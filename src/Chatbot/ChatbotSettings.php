<?php

declare(strict_types=1);

namespace ConacytaCore\Chatbot;

final class ChatbotSettings
{
    public function register(): void
    {
        register_setting("conacyta_chatbot", "conacyta_core_gemini_model", [
            "type"              => "string",
            "default"           => "gemini-3.1-flash-lite",
            "sanitize_callback" => [\ConacytaCore\Shared\Sanitizer::class, "text"],
        ]);

        register_setting("conacyta_chatbot", "conacyta_core_ai_provider", [
            "type"              => "string",
            "default"           => "gemini",
            "sanitize_callback" => [\ConacytaCore\Shared\Sanitizer::class, "text"],
        ]);

        register_setting("conacyta_chatbot", "conacyta_core_deepseek_api_key", [
            "type"              => "string",
            "default"           => "",
            "sanitize_callback" => static function ($value): string {
                $clean = sanitize_text_field((string) $value);
                return trim($clean);
            },
        ]);

        register_setting("conacyta_chatbot", "conacyta_core_deepseek_endpoint", [
            "type"              => "string",
            "default"           => "https://api.deepseek.com",
            "sanitize_callback" => [\ConacytaCore\Shared\Sanitizer::class, "url"],
        ]);

        register_setting("conacyta_chatbot", "conacyta_core_deepseek_model", [
            "type"              => "string",
            "default"           => "deepseek-v4-flash",
            "sanitize_callback" => [\ConacytaCore\Shared\Sanitizer::class, "text"],
        ]);

        register_setting("conacyta_chatbot", "conacyta_core_chat_rate_limit", [
            "type"              => "integer",
            "default"           => 60,
            "sanitize_callback" => [\ConacytaCore\Shared\Sanitizer::class, "integer"],
        ]);

        register_setting("conacyta_chatbot", "conacyta_core_system_prompt", [
            "type"              => "string",
            "default"           => $this->getDefaultSystemPrompt(),
            "sanitize_callback" => [\ConacytaCore\Shared\Sanitizer::class, "html"],
        ]);

        add_settings_section(
            "conacyta_chatbot_section",
            __("Configuración del Chatbot", "conacyta"),
            "__return_empty_string",
            "conacyta_chatbot"
        );

        add_settings_field(
            "conacyta_core_gemini_model",
            __("Modelo Gemini", "conacyta"),
            [$this, "renderModelField"],
            "conacyta_chatbot",
            "conacyta_chatbot_section"
        );

        add_settings_field(
            "conacyta_core_chat_rate_limit",
            __("Límite de solicitudes (por minuto/IP)", "conacyta"),
            [$this, "renderRateLimitField"],
            "conacyta_chatbot",
            "conacyta_chatbot_section"
        );

        add_settings_field(
            "conacyta_core_system_prompt",
            __("System Prompt", "conacyta"),
            [$this, "renderSystemPromptField"],
            "conacyta_chatbot",
            "conacyta_chatbot_section"
        );

        add_settings_field(
            "conacyta_core_gemini_api_key_notice",
            __("API Key de Gemini", "conacyta"),
            [$this, "renderApiKeyNotice"],
            "conacyta_chatbot",
            "conacyta_chatbot_section"
        );

        add_settings_field(
            "conacyta_core_ai_provider",
            __("Proveedor de IA", "conacyta"),
            [$this, "renderProviderField"],
            "conacyta_chatbot",
            "conacyta_chatbot_section"
        );

        add_settings_field(
            "conacyta_core_deepseek_api_key",
            __("DeepSeek API Key", "conacyta"),
            [$this, "renderDeepSeekApiKeyField"],
            "conacyta_chatbot",
            "conacyta_chatbot_section"
        );

        add_settings_field(
            "conacyta_core_deepseek_endpoint",
            __("DeepSeek Endpoint", "conacyta"),
            [$this, "renderDeepSeekEndpointField"],
            "conacyta_chatbot",
            "conacyta_chatbot_section"
        );

        add_settings_field(
            "conacyta_core_deepseek_model",
            __("Modelo DeepSeek", "conacyta"),
            [$this, "renderDeepSeekModelField"],
            "conacyta_chatbot",
            "conacyta_chatbot_section"
        );

        add_settings_section(
            "conacyta_chatbot_ui_section",
            __("Textos de la Interfaz del Chatbot", "conacyta"),
            "__return_empty_string",
            "conacyta_chatbot"
        );

        $uiFields = [
            "conacyta_core_chatbot_welcome"     => __("Mensaje de bienvenida del chatbot", "conacyta"),
            "conacyta_core_chatbot_placeholder" => __("Placeholder del input de chat", "conacyta"),
            "conacyta_core_chatbot_badge"       => __("Texto del badge superior", "conacyta"),
            "conacyta_core_chatbot_footer_left" => __("Texto footer izquierdo", "conacyta"),
            "conacyta_core_chatbot_footer_right" => __("Texto footer derecho", "conacyta"),
        ];

        foreach ($uiFields as $key => $label) {
            register_setting("conacyta_chatbot", $key, [
                "type"              => "string",
                "default"           => "",
                "sanitize_callback" => [\ConacytaCore\Shared\Sanitizer::class, "text"],
            ]);

            add_settings_field(
                $key,
                $label,
                [$this, "renderTextField"],
                "conacyta_chatbot",
                "conacyta_chatbot_ui_section",
                ["key" => $key]
            );
        }
    }

    public function renderModelField(): void
    {
        $value = get_option("conacyta_core_gemini_model", "gemini-3.1-flash-lite");
        echo "<select name=\"conacyta_core_gemini_model\">";
        echo "<option value=\"gemini-3.1-flash-lite\" " . selected($value, "gemini-3.1-flash-lite", false) . ">" . esc_html__("Gemini 3.1 Flash-Lite", "conacyta") . "</option>";
        echo "<option value=\"gemini-3.5-flash\" " . selected($value, "gemini-3.5-flash", false) . ">" . esc_html__("Gemini 3.5 Flash (Preview)", "conacyta") . "</option>";
        echo "<option value=\"gemini-3.1-pro\" " . selected($value, "gemini-3.1-pro", false) . ">" . esc_html__("Gemini 3.1 Pro", "conacyta") . "</option>";
        echo "</select>";
    }

    public function renderRateLimitField(): void
    {
        $value = (int) get_option("conacyta_core_chat_rate_limit", 60);
        printf(
            "<input type=\"number\" name=\"conacyta_core_chat_rate_limit\" value=\"%s\" min=\"1\" max=\"60\" step=\"1\" />",
            esc_attr((string) $value)
        );
    }

    public function renderSystemPromptField(): void
    {
        $value = get_option("conacyta_core_system_prompt", $this->getDefaultSystemPrompt());
        printf(
            "<textarea name=\"conacyta_core_system_prompt\" rows=\"6\" cols=\"60\">%s</textarea>",
            esc_textarea($value)
        );
    }

    public function renderApiKeyNotice(): void
    {
        echo "<p class=\"description\">";
        echo esc_html__("La API key de Gemini se gestiona desde Settings > Connectors (Google es auto-descubierto en WP 7.0).", "conacyta");
        echo " " . esc_html__("El plugin obtiene la key automaticamente del conector registrado.", "conacyta");
        echo "</p>";
    }

    public function renderProviderField(): void
    {
        $value = get_option("conacyta_core_ai_provider", "gemini");
        echo "<select name=\"conacyta_core_ai_provider\">";
        echo "<option value=\"gemini\" " . selected($value, "gemini", false) . ">" . esc_html__("Gemini (Google)", "conacyta") . "</option>";
        echo "<option value=\"deepseek\" " . selected($value, "deepseek", false) . ">" . esc_html__("DeepSeek", "conacyta") . "</option>";
        echo "</select>";
        echo "<p class=\"description\">" . esc_html__("Selecciona el proveedor de IA. Gemini usa Connectors API de WP 7.0. DeepSeek requiere API key propia.", "conacyta") . "</p>";
    }

    public function renderDeepSeekApiKeyField(): void
    {
        $value = get_option("conacyta_core_deepseek_api_key", "");
        $display = $value !== "" ? '********' . substr($value, -4) : '';
        printf(
            "<input type=\"password\" name=\"conacyta_core_deepseek_api_key\" value=\"%s\" class=\"regular-text\" autocomplete=\"new-password\" placeholder=\"sk-...\" />",
            esc_attr($value)
        );
        if ($display !== '') {
            echo '<p class="description">' . esc_html(sprintf(__('Key guardada: %s', 'conacyta'), $display)) . '</p>';
        }
        echo "<p class=\"description\">" . esc_html__("Obten tu API key en platform.deepseek.com. Recomendado: define CONACYTA_DEEPSEEK_API_KEY en wp-config.php.", "conacyta") . "</p>";
    }

    public function renderDeepSeekEndpointField(): void
    {
        $value = get_option("conacyta_core_deepseek_endpoint", "https://api.deepseek.com");
        printf(
            "<input type=\"url\" name=\"conacyta_core_deepseek_endpoint\" value=\"%s\" class=\"regular-text\" />",
            esc_attr((string) $value)
        );
        echo "<p class=\"description\">" . esc_html__("URL base de la API. Por defecto: https://api.deepseek.com", "conacyta") . "</p>";
    }

    public function renderDeepSeekModelField(): void
    {
        $value = get_option("conacyta_core_deepseek_model", "deepseek-v4-flash");
        echo "<select name=\"conacyta_core_deepseek_model\">";
        echo "<option value=\"deepseek-v4-flash\" " . selected($value, "deepseek-v4-flash", false) . ">" . esc_html__("DeepSeek V4 Flash", "conacyta") . "</option>";
        echo "<option value=\"deepseek-v4-pro\" " . selected($value, "deepseek-v4-pro", false) . ">" . esc_html__("DeepSeek V4 Pro", "conacyta") . "</option>";
        echo "</select>";
        echo "<p class=\"description\">" . esc_html__("V4 Flash: rapido, tool calls. V4 Pro: mas capacidad, tool calls.", "conacyta") . "</p>";
    }

    public function renderTextField(array $args): void
    {
        $key = $args["key"];
        $value = get_option($key, "");
        printf(
            "<input type=\"text\" name=\"%s\" value=\"%s\" class=\"regular-text\" />",
            esc_attr($key),
            esc_attr((string) $value)
        );
    }

    private function getDefaultSystemPrompt(): string
    {
        return "Eres el asistente virtual oficial del XVII CONACYTA 2026, organizado por la Universidad Nacional de Frontera en Sullana, Perú.\n\n"
            . "PERSONALIDAD: Profesional, conciso, servicial. Respuestas de 2 a 4 oraciones.\n\n"
            . "HERRAMIENTAS: Tienes acceso a tools (function calling) para obtener datos reales del congreso. USA LAS HERRAMIENTAS para responder con precisión.\n"
            . "- get_evento: fechas, sede, URL inscripción\n"
            . "- get_tarifas: precios y beneficios de inscripción\n"
            . "- get_ponentes: ponentes magistrales\n"
            . "- get_agenda: sesiones por día y hora\n"
            . "- get_comite: comité organizador\n"
            . "- get_cronograma: fases de convocatoria\n"
            . "- get_actividades: actividades paralelas\n"
            . "- get_partners: socios estratégicos\n"
            . "- buscar_informacion: buscar en el sitio web del congreso\n"
            . "- get_conocimiento_general: entorno de Sullana y transporte\n\n"
            . "SUGERENCIAS: OBLIGATORIO. Al final de CADA respuesta, DEBES incluir exactamente 2 preguntas sugeridas en este formato:\n"
            . "---SUGERENCIAS---\n"
            . "Pregunta sugerida 1\n"
            . "Pregunta sugerida 2\n\n"
            . "REGLAS:\n"
            . "1. USA LAS HERRAMIENTAS. No inventes datos. Si una herramienta no devuelve un dato, di 'no tengo esa información'.\n"
            . "2. NUNCA inventes fechas, días de la semana, horas ni números. Usa SOLO los valores exactos que las herramientas devuelven.\n"
            . "3. NUNCA nombres hoteles, restaurantes ni negocios. Solo zonas.\n"
            . "4. Si das info de transporte, hospedaje o clima, incluye SIEMPRE: 'Esta información es referencial. Verifícala directamente.'\n"
            . "5. Precios en S/ (soles peruanos). Siempre responde en español.";
    }
}