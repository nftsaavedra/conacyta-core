<?php

declare(strict_types=1);

namespace ConacytaCore\Chatbot;

final class DeepSeekClient extends AbstractAiClient
{
    private string $endpoint;

    public function __construct(?string $sessionId = null)
    {
        parent::__construct($sessionId);
        $this->endpoint = (string) get_option('conacyta_core_deepseek_endpoint', 'https://api.deepseek.com');
    }

    protected function resolveApiKey(): string
    {
        return (string) get_option('conacyta_core_deepseek_api_key', '');
    }

    protected function resolveModel(): string
    {
        return (string) get_option('conacyta_core_deepseek_model', 'deepseek-v4-flash');
    }

    protected function getLogPrefix(): string
    {
        return '[Conacyta DeepSeek]';
    }

    protected function buildApiUrl(): string
    {
        return trailingslashit($this->endpoint) . 'chat/completions';
    }

    protected function buildRequestHeaders(): array
    {
        return [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $this->apiKey,
        ];
    }

    protected function extractTextFromResponse(array $data): string
    {
        $choices = $data['choices'] ?? [];
        $firstChoice = $choices[0] ?? [];
        $msg = $firstChoice['message'] ?? [];
        return $msg['content'] ?? '';
    }

    public function sendMessage(string $message, array $history = []): array
    {
        if ('' === $this->apiKey) {
            error_log('[Conacyta DeepSeek] API key no configurada.');
            return ['error' => 'Servicio no disponible en este momento.'];
        }

        $messages = [['role' => 'system', 'content' => $this->systemPrompt]];

        foreach ($history as $entry) {
            $role = ($entry['role'] ?? 'user') === 'bot' ? 'assistant' : 'user';
            $messages[] = ['role' => $role, 'content' => $entry['text'] ?? ''];
        }

        $messages[] = ['role' => 'user', 'content' => $message];

        if (count($messages) > self::MAX_HISTORY + 1) {
            $systemMsg = $messages[0];
            $messages = array_merge([$systemMsg], array_slice($messages, -(self::MAX_HISTORY)));
        }

        $body = [
            'model'       => $this->model,
            'messages'    => $messages,
            'tools'       => $this->getTools(),
            'temperature' => 0.4,
            'max_tokens'  => 1024,
        ];

        $data = $this->apiCall($body);
        if (isset($data['error'])) {
            return $data;
        }

        $choice = $data['choices'][0] ?? [];
        $msg    = $choice['message'] ?? [];
        $finish = $choice['finish_reason'] ?? 'stop';

        $content = $msg['content'] ?? '';

        // Detect DeepSeek V4 XML tool calls in content (finish_reason = stop)
        $xmlCalls = $this->extractXmlToolCalls($content);
        if ($xmlCalls !== null) {
            $messages[] = ['role' => 'assistant', 'content' => $content];
            foreach ($xmlCalls as $call) {
                $args = $call['args'] ?? [];
                $result = $this->executeFunction($call['name'], $args);
                $messages[] = ['role' => 'tool', 'tool_call_id' => $call['id'] ?? uniqid('xml_', true), 'content' => $result];
            }
            $data = $this->continueWithTools($messages);
            if (isset($data['error'])) {
                return $data;
            }
        }

        // Standard OpenAI-compatible tool_calls
        if ($finish === 'tool_calls' && isset($msg['tool_calls'])) {
            $toolCalls = $msg['tool_calls'];
            $messages[] = $msg;

            foreach ($toolCalls as $tc) {
                $funcName = $tc['function']['name'] ?? '';
                $args = json_decode($tc['function']['arguments'] ?? '{}', true) ?: [];
                $result = $this->executeFunction($funcName, $args);
                $messages[] = [
                    'role'         => 'tool',
                    'tool_call_id' => $tc['id'] ?? '',
                    'content'      => $result,
                ];
            }

            $data = $this->continueWithTools($messages);
            if (isset($data['error'])) {
                return $data;
            }
        }

        $text = $this->extractTextFromResponse($data);
        $suggestions = $this->extractSuggestions($text);

        $this->saveToHistory($message, $text);

        return ['reply' => $text, 'suggestions' => $suggestions];
    }

    private function continueWithTools(array $messages): array
    {
        $maxTurns = 3;
        for ($turn = 0; $turn < $maxTurns; $turn++) {
            $body = [
                'model'       => $this->model,
                'messages'    => $messages,
                'temperature' => 0.4,
                'max_tokens'  => 1024,
            ];

            $data = $this->apiCall($body);
            if (isset($data['error'])) {
                return $data;
            }

            $choice = $data['choices'][0] ?? [];
            $msg    = $choice['message'] ?? [];
            $finish = $choice['finish_reason'] ?? 'stop';
            $content = $msg['content'] ?? '';

            $xmlCalls = $this->extractXmlToolCalls($content);
            if ($xmlCalls !== null) {
                $messages[] = ['role' => 'assistant', 'content' => $content];
                foreach ($xmlCalls as $call) {
                    $args = $call['args'] ?? [];
                    $result = $this->executeFunction($call['name'], $args);
                    $messages[] = ['role' => 'tool', 'tool_call_id' => $call['id'] ?? uniqid('xml_', true), 'content' => $result];
                }
                continue;
            }

            if ($finish === 'tool_calls' && isset($msg['tool_calls'])) {
                $messages[] = $msg;
                foreach ($msg['tool_calls'] as $tc) {
                    $funcName = $tc['function']['name'] ?? '';
                    $args = json_decode($tc['function']['arguments'] ?? '{}', true) ?: [];
                    $result = $this->executeFunction($funcName, $args);
                    $messages[] = [
                        'role'         => 'tool',
                        'tool_call_id' => $tc['id'] ?? '',
                        'content'      => $result,
                    ];
                }
                continue;
            }

            return $data;
        }

        return ['error' => 'Servicio no disponible.'];
    }

    private function extractXmlToolCalls(string $content): ?array
    {
        if (!preg_match_all('/<调用\s+name="([^"]+)">(.*?)<\/调用>/s', $content, $matches, PREG_SET_ORDER)) {
            return null;
        }
        $calls = [];
        foreach ($matches as $m) {
            $name = $m[1];
            $inner = $m[2];
            $args = [];
            if (preg_match_all('/<参数\s+name="([^"]+)">(.*?)<\/参数>/s', $inner, $pm, PREG_SET_ORDER)) {
                foreach ($pm as $p) {
                    $args[$p[1]] = $p[2];
                }
            }
            $calls[] = ['name' => $name, 'args' => $args, 'id' => uniqid('xml_', true)];
        }
        return $calls;
    }

    private function getTools(): array
    {
        return [
            ['type' => 'function', 'function' => ['name' => 'get_evento',              'description' => 'Obtener datos del evento: nombre, fechas, sede, ciudad, organizador, URL de inscripción.',              'parameters' => ['type' => 'object', 'properties' => new \stdClass()]]],
            ['type' => 'function', 'function' => ['name' => 'get_tarifas',             'description' => 'Obtener tarifas de inscripción: precios, moneda, beneficios incluidos.',                             'parameters' => ['type' => 'object', 'properties' => new \stdClass()]]],
            ['type' => 'function', 'function' => ['name' => 'get_ponentes',            'description' => 'Obtener lista de ponentes magistrales: nombre, institución, país.',                                  'parameters' => ['type' => 'object', 'properties' => new \stdClass()]]],
            ['type' => 'function', 'function' => ['name' => 'get_agenda',              'description' => 'Obtener agenda del congreso: sesiones por día, horarios, tipo de sesión.',                           'parameters' => ['type' => 'object', 'properties' => new \stdClass()]]],
            ['type' => 'function', 'function' => ['name' => 'get_comite',              'description' => 'Obtener comité organizador: nombres y roles.',                                                       'parameters' => ['type' => 'object', 'properties' => new \stdClass()]]],
            ['type' => 'function', 'function' => ['name' => 'get_cronograma',          'description' => 'Obtener cronograma de convocatoria.',                                                                'parameters' => ['type' => 'object', 'properties' => new \stdClass()]]],
            ['type' => 'function', 'function' => ['name' => 'get_actividades',         'description' => 'Obtener actividades paralelas.',                                                                      'parameters' => ['type' => 'object', 'properties' => new \stdClass()]]],
            ['type' => 'function', 'function' => ['name' => 'get_partners',            'description' => 'Obtener socios estratégicos y colaboradores.',                                                        'parameters' => ['type' => 'object', 'properties' => new \stdClass()]]],
            ['type' => 'function', 'function' => ['name' => 'get_conocimiento_general', 'description' => 'Conocimiento general: Sullana, Piura, transporte, zonas de hospedaje, clima. NO datos del congreso.', 'parameters' => ['type' => 'object', 'properties' => new \stdClass()]]],
            ['type' => 'function', 'function' => [
                'name' => 'buscar_informacion', 'description' => 'Buscar en el sitio web: posts, páginas, anuncios.',
                'parameters' => [
                    'type' => 'object', 'properties' => [
                        'consulta' => ['type' => 'string', 'description' => 'Palabras clave para buscar.'],
                    ],
                ],
            ]],
        ];
    }

    private function executeFunction(string $name, array $args = []): string
    {
        $consulta = $args['consulta'] ?? '';

        return match ($name) {
            'get_evento'              => $this->queryEvento(),
            'get_tarifas'             => $this->queryCPT('tarifa'),
            'get_ponentes'            => $this->queryCPT('ponente'),
            'get_agenda'              => $this->queryCPT('agenda_item'),
            'get_comite'              => $this->queryCPT('comite_member'),
            'get_cronograma'          => $this->queryCPT('cronograma_fase'),
            'get_actividades'         => $this->queryCPT('actividad'),
            'get_partners'            => $this->queryCPT('partner'),
            'buscar_informacion'      => $this->searchWordPress((string) $consulta),
            'get_conocimiento_general' => 'Usa tu conocimiento general sobre Sullana, Piura, transporte y zonas de hospedaje. NO nombres hoteles ni negocios específicos, solo zonas. Incluye: "Esta información es referencial. Verifícala directamente."',
            default                   => '',
        };
    }

    private function queryEvento(): string
    {
        $lines = [];
        $lines[] = '=== DATOS DEL EVENTO ===';
        $lines[] = 'Nombre: XVII CONACYTA 2026';
        $lines[] = 'Fechas: ' . get_option('conacyta_evento_fecha_inicio', '2026-10-12') . ' al ' . get_option('conacyta_evento_fecha_fin', '2026-10-16');
        $lines[] = 'Sede: ' . get_option('conacyta_evento_sede', 'Universidad Nacional de Frontera');
        $lines[] = 'Ciudad: ' . get_option('conacyta_evento_ciudad', 'Sullana, Perú');
        $lines[] = 'Organizador: ' . get_option('conacyta_evento_organizador', 'UNF');
        $lines[] = 'URL inscripción: ' . get_option('conacyta_evento_url_inscripcion', '/inscripcion/');
        return implode("\n", $lines);
    }

    private function queryCPT(string $postType): string
    {
        $metaKeys = $this->getMetaKeysForCPT($postType);
        $args = [
            'post_type'      => $postType,
            'posts_per_page' => $postType === 'agenda_item' ? 20 : 15,
            'post_status'    => 'publish',
            'orderby'        => $postType === 'agenda_item' ? 'meta_value_num' : 'title',
            'order'          => 'ASC',
        ];
        if ($postType === 'agenda_item') {
            $args['meta_key'] = 'conacyta_core_agenda_dia';
        }

        $posts = get_posts($args);
        $lines = [];
        $lines[] = '=== ' . strtoupper(str_replace('_', ' ', $postType)) . ' ===';

        foreach ($posts as $p) {
            $line = $p->post_title;
            foreach ($metaKeys as $mk => $ml) {
                $val = get_post_meta($p->ID, $mk, true);
                if ($val !== '' && $val !== null && $val !== false) {
                    $line .= ' | ' . $ml . ': ' . $val;
                }
            }
            $content = wp_strip_all_tags($p->post_content);
            if ($content !== '') {
                $line .= ' | ' . mb_substr($content, 0, 120);
            }
            $lines[] = '- ' . $line;
        }

        return implode("\n", $lines);
    }

    private function getMetaKeysForCPT(string $postType): array
    {
        return match ($postType) {
            'tarifa'        => ['conacyta_core_tarifa_precio' => 'Precio', 'conacyta_core_tarifa_moneda' => 'Moneda'],
            'ponente'       => ['conacyta_core_ponente_institucion' => 'Institucion', 'conacyta_core_ponente_pais' => 'Pais'],
            'agenda_item'   => ['conacyta_core_agenda_dia' => 'Dia', 'conacyta_core_agenda_hora_inicio' => 'Hora'],
            'comite_member' => ['conacyta_core_comite_rol' => 'Rol'],
            'cronograma_fase' => ['conacyta_core_fase_fecha_inicio' => 'Inicio', 'conacyta_core_fase_fecha_fin' => 'Fin'],
            'partner'       => ['conacyta_core_partner_tipo' => 'Tipo', 'conacyta_core_partner_url' => 'URL'],
            default         => [],
        };
    }

    private function searchWordPress(string $consulta): string
    {
        $query = trim($consulta);
        if ($query === '') {
            return 'No se especificó término de búsqueda.';
        }

        $results = get_posts([
            'post_type'      => ['post', 'page'],
            'post_status'    => 'publish',
            'posts_per_page' => 5,
            's'              => $query,
            'orderby'        => 'relevance',
        ]);

        if (empty($results)) {
            return 'No se encontraron resultados para: ' . $query;
        }

        $lines = [];
        $lines[] = '=== RESULTADOS DE BUSQUEDA: ' . $query . ' ===';
        foreach ($results as $r) {
            $excerpt = wp_strip_all_tags($r->post_excerpt ?: $r->post_content);
            $lines[] = '- ' . $r->post_title . ' | ' . mb_substr($excerpt, 0, 150) . ' | URL: ' . get_permalink($r);
        }

        return implode("\n", $lines);
    }
}
