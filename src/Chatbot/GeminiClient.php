<?php

declare(strict_types=1);

namespace ConacytaCore\Chatbot;

final class GeminiClient extends AbstractAiClient
{
    private const API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent';

    protected function resolveApiKey(): string
    {
        $key = get_option('connectors_ai_google_api_key', '');
        if (empty($key) && defined('CONNECTORS_AI_GOOGLE_API_KEY')) {
            $key = CONNECTORS_AI_GOOGLE_API_KEY;
        }
        return $key;
    }

    protected function resolveModel(): string
    {
        return (string) get_option('conacyta_core_gemini_model', 'gemini-3.1-flash-lite');
    }

    protected function getLogPrefix(): string
    {
        return '[Conacyta Gemini]';
    }

    // ADVERTENCIA: La API key viaja como query parameter (Google requiere este mecanismo).
    // Verificar que el servidor NO loguee query strings en access_log ni WP_DEBUG_LOG.
    protected function buildApiUrl(): string
    {
        return add_query_arg('key', $this->apiKey, sprintf(self::API_URL, $this->model));
    }

    protected function buildRequestHeaders(): array
    {
        return ['Content-Type' => 'application/json'];
    }

    protected function extractTextFromResponse(array $data): string
    {
        $candidates = $data['candidates'] ?? [];
        $firstCandidate = $candidates[0] ?? [];
        $content = $firstCandidate['content'] ?? [];
        $parts = $content['parts'] ?? [];
        $firstPart = $parts[0] ?? [];
        return $firstPart['text'] ?? '';
    }

    public function sendMessage(string $message, array $history = []): array
    {
        if ('' === $this->apiKey) {
            error_log('[Conacyta Gemini] API key no configurada.');
            return ['error' => 'Servicio no disponible en este momento.'];
        }

        $systemInstruction = $this->systemPrompt . "\n\n---\n\n" . $this->buildContextPrompt();

        $body = [
            'system_instruction' => [
                'parts' => [['text' => $systemInstruction]],
            ],
            'contents' => $this->buildContents($history, $message),
            'generationConfig' => [
                'temperature'     => 0.4,
                'topP'            => 0.8,
                'topK'            => 40,
                'maxOutputTokens' => 1024,
            ],
            'safetySettings' => [
                ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
            ],
        ];

        $data = $this->apiCall($body);
        if (isset($data['error'])) {
            return $data;
        }

        $text = $this->extractTextFromResponse($data);
        $suggestions = $this->extractSuggestions($text);

        $this->saveToHistory($message, $text);

        return ['reply' => $text, 'suggestions' => $suggestions];
    }

    private function buildContents(array $history, string $message): array
    {
        $contents = [];
        foreach ($history as $entry) {
            $role = $entry['role'] ?? 'user';
            $text = $entry['text'] ?? '';
            $geminiRole = $role === 'bot' ? 'model' : 'user';
            $contents[] = ['role' => $geminiRole, 'parts' => [['text' => $text]]];
        }
        $contents[] = ['role' => 'user', 'parts' => [['text' => $message]]];
        if (count($contents) > self::MAX_HISTORY) {
            $contents = array_slice($contents, -self::MAX_HISTORY);
        }
        return $contents;
    }

    private function buildContextPrompt(): string
    {
        $cacheKey = 'conacyta_context_prompt';
        $cached = get_transient($cacheKey);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $parts = [];
        $inicio = get_option('conacyta_evento_fecha_inicio', '2026-10-12');
        $fin    = get_option('conacyta_evento_fecha_fin', '2026-10-16');

        $parts[] = '=== DATOS DEL EVENTO ===';
        $parts[] = '- Nombre: XVII CONACYTA 2026';
        $parts[] = '- Fechas: ' . $inicio . ' al ' . $fin;
        $parts[] = '- Sede: ' . get_option('conacyta_evento_sede', 'Universidad Nacional de Frontera');
        $parts[] = '- Ciudad: ' . get_option('conacyta_evento_ciudad', 'Sullana, Perú');
        $parts[] = '- URL inscripción: ' . get_option('conacyta_evento_url_inscripcion', '/inscripcion/');

        $this->appendCPT($parts, 'ponente', 'PONENTES MAGISTRALES', 15, ['conacyta_core_ponente_institucion' => 'Inst', 'conacyta_core_ponente_pais' => 'Pais']);
        $this->appendCPT($parts, 'tarifa', 'TARIFAS DE INSCRIPCION', 10, ['conacyta_core_tarifa_precio' => 'Precio', 'conacyta_core_tarifa_moneda' => 'Moneda']);
        $this->appendCPT($parts, 'comite_member', 'COMITE ORGANIZADOR', 10, ['conacyta_core_comite_rol' => 'Rol']);
        $this->appendCPT($parts, 'cronograma_fase', 'CRONOGRAMA DE CONVOCATORIA', 10, ['conacyta_core_fase_fecha_inicio' => 'Inicio', 'conacyta_core_fase_fecha_fin' => 'Fin']);
        $this->appendCPT($parts, 'actividad', 'ACTIVIDADES PARALELAS', 10, []);
        $this->appendCPT($parts, 'partner', 'SOCIOS ESTRATEGICOS', 10, ['conacyta_core_partner_tipo' => 'Tipo']);
        $this->appendCPT($parts, 'agenda_item', 'AGENDA DEL CONGRESO', 20, ['conacyta_core_agenda_dia' => 'Dia', 'conacyta_core_agenda_hora_inicio' => 'Hora'], 'meta_value_num', 'conacyta_core_agenda_dia');

        $result = implode("\n", $parts);
        set_transient($cacheKey, $result, 300);

        return $result;
    }

    private function appendCPT(array &$parts, string $postType, string $label, int $limit, array $metaKeys, string $orderby = 'title', string $metaKey = ''): void
    {
        $args = ['post_type' => $postType, 'posts_per_page' => $limit, 'post_status' => 'publish', 'orderby' => $orderby, 'order' => 'ASC'];
        if ($metaKey !== '') {
            $args['meta_key'] = $metaKey;
        }
        $posts = get_posts($args);
        if (empty($posts)) {
            return;
        }
        $parts[] = '';
        $parts[] = '=== ' . $label . ' ===';
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
                $line .= ' | ' . mb_substr($content, 0, 100);
            }
            $parts[] = '- ' . $line;
        }
    }
}
