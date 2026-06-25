<?php

declare(strict_types=1);

namespace ConacytaCore\Contacto;

final class ContactoSettings
{
    public function register(): void
    {
        $fields = [
            "conacyta_core_contacto_email"     => ["type" => "string", "default" => "conacyta2026@unf.edu.pe", "label" => __("Email de contacto", "conacyta"), "sanitizer" => "email"],
            "conacyta_core_contacto_whatsapp"  => ["type" => "string", "default" => "+51963123456",       "label" => __("WhatsApp", "conacyta"), "sanitizer" => "text"],
            "conacyta_core_facebook_url"       => ["type" => "string", "default" => "",                    "label" => __("URL de Facebook", "conacyta"), "sanitizer" => "url"],
            "conacyta_core_instagram_url"      => ["type" => "string", "default" => "",                    "label" => __("URL de Instagram", "conacyta"), "sanitizer" => "url"],
            "conacyta_core_linkedin_url"       => ["type" => "string", "default" => "",                    "label" => __("URL de LinkedIn", "conacyta"), "sanitizer" => "url"],
        ];

        foreach ($fields as $key => $config) {
            $sanitizer_cb = match ($config["sanitizer"]) {
                "email" => [\ConacytaCore\Shared\Sanitizer::class, "email"],
                "url"   => [\ConacytaCore\Shared\Sanitizer::class, "url"],
                default => [\ConacytaCore\Shared\Sanitizer::class, "text"],
            };

            register_setting("conacyta_contacto", $key, [
                "type"              => $config["type"],
                "default"           => $config["default"],
                "sanitize_callback" => $sanitizer_cb,
            ]);
        }

        add_settings_section(
            "conacyta_contacto_section",
            __("Información de Contacto", "conacyta"),
            "__return_empty_string",
            "conacyta_contacto"
        );

        foreach ($fields as $key => $config) {
            add_settings_field(
                $key,
                $config["label"],
                [$this, "renderTextField"],
                "conacyta_contacto",
                "conacyta_contacto_section",
                ["key" => $key, "type" => $key === "conacyta_core_contacto_email" ? "email" : ("conacyta_core_contacto_whatsapp" === $key ? "text" : "url")]
            );
        }
    }

    public function renderTextField(array $args): void
    {
        $key = $args["key"];
        $value = get_option($key, "");
        $type = $args["type"] ?? "text";
        printf(
            "<input type=\"" . esc_attr($type) . "\" name=\"" . esc_attr($key) . "\" value=\"" . esc_attr((string) $value) . "\" class=\"regular-text\" />"
        );
    }
}