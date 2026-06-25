<?php

declare(strict_types=1);

namespace ConacytaCore\Settings;

final class EventoSettings
{
    public function register(): void
    {
        $this->registerSettings();
        $this->addFields();
        $this->addHooks();
    }

    private function registerSettings(): void
    {
        $groupMap = [
            'identidad'  => ['conacyta_evento_edicion', 'conacyta_evento_acronimo', 'conacyta_evento_anio', 'conacyta_evento_fecha_inicio', 'conacyta_evento_fecha_fin', 'conacyta_evento_url_inscripcion', 'conacyta_evento_url_envio_resumen', 'conacyta_core_cleanup_on_uninstall'],
            'sede'       => ['conacyta_evento_sede', 'conacyta_evento_ciudad', 'conacyta_evento_organizador', 'conacyta_evento_facultad'],
            'countdown'  => ['conacyta_evento_countdown_titulo', 'conacyta_evento_countdown_fecha_objetivo', 'conacyta_evento_countdown_fase1_badge', 'conacyta_evento_countdown_fase1_mensaje', 'conacyta_evento_countdown_fase2_badge', 'conacyta_evento_countdown_fase2_mensaje', 'conacyta_evento_countdown_cta_texto', 'conacyta_evento_countdown_cta_url'],
            'sobre'      => ['conacyta_evento_sobre_titulo', 'conacyta_evento_sobre_descripcion', 'conacyta_evento_sobre_imagen_1', 'conacyta_evento_sobre_imagen_1_alt', 'conacyta_evento_sobre_imagen_2', 'conacyta_evento_sobre_imagen_2_alt', 'conacyta_evento_sobre_imagen_3', 'conacyta_evento_sobre_imagen_3_alt'],
            'secciones'  => ['conacyta_evento_seccion_ponentes', 'conacyta_evento_seccion_actividades', 'conacyta_evento_seccion_agenda', 'conacyta_evento_seccion_tarifas', 'conacyta_evento_seccion_comite', 'conacyta_evento_seccion_partners', 'conacyta_evento_seccion_ejes'],
        ];

        $keyToGroup = [];
        foreach ($groupMap as $group => $keys) {
            foreach ($keys as $k) {
                $keyToGroup[$k] = $group;
            }
        }

        $settings = [
            "conacyta_evento_edicion"         => ["type" => "integer", "default" => 17],
            "conacyta_evento_acronimo"        => ["type" => "string",  "default" => "CONACYTA"],
            "conacyta_evento_anio"            => ["type" => "integer", "default" => (int) date("Y")],
            "conacyta_evento_fecha_inicio"    => ["type" => "string",  "default" => "2026-10-12", "validate" => "date"],
            "conacyta_evento_fecha_fin"       => ["type" => "string",  "default" => "2026-10-16", "validate" => "date"],
            "conacyta_evento_url_inscripcion" => ["type" => "string",  "default" => "/inscripcion/"],
            "conacyta_evento_url_envio_resumen" => ["type" => "string", "default" => "/enviar-resumen/", "validate" => "url"],

            "conacyta_evento_sede"            => ["type" => "string",  "default" => "Universidad Nacional de Frontera"],
            "conacyta_evento_ciudad"          => ["type" => "string",  "default" => "Sullana, Perú"],
            "conacyta_evento_organizador"     => ["type" => "string",  "default" => "Universidad Nacional de Frontera"],
            "conacyta_evento_facultad"        => ["type" => "string",  "default" => "Facultad de Ingeniería de Industrias Alimentarias y Biotecnología"],

            "conacyta_evento_countdown_titulo"       => ["type" => "string", "default" => "PREPARATE PARA EL CONGRESO"],
            "conacyta_evento_countdown_fecha_objetivo"  => ["type" => "string", "default" => "2026-10-12T08:00:00-05:00", "validate" => "datetime"],
            "conacyta_evento_countdown_fase1_badge"   => ["type" => "string", "default" => "Convocatoria Próxima"],
            "conacyta_evento_countdown_fase1_mensaje" => ["type" => "string", "default" => "Mantente atento a las fechas de envío de resúmenes."],
            "conacyta_evento_countdown_fase2_badge"   => ["type" => "string", "default" => "Envío de Resúmenes"],
            "conacyta_evento_countdown_fase2_mensaje" => ["type" => "string", "default" => "Envía tu investigación antes del cierre de convocatoria."],
            "conacyta_evento_countdown_cta_texto"     => ["type" => "string", "default" => "INSCRIBIRME AL EVENTO"],
            "conacyta_evento_countdown_cta_url"       => ["type" => "string", "default" => "/inscripcion/"],
            "conacyta_evento_sobre_titulo"      => ["type" => "string", "default" => "SOBRE LA ORGANIZACIÓN"],
            "conacyta_evento_sobre_descripcion" => ["type" => "string", "default" => "El XVII CONACYTA 2026 es organizado por la Universidad Nacional de Frontera..."],
            "conacyta_evento_sobre_imagen_1"    => ["type" => "string", "default" => ""],
            "conacyta_evento_sobre_imagen_1_alt" => ["type" => "string", "default" => ""],
            "conacyta_evento_sobre_imagen_2"    => ["type" => "string", "default" => ""],
            "conacyta_evento_sobre_imagen_2_alt" => ["type" => "string", "default" => ""],
            "conacyta_evento_sobre_imagen_3"    => ["type" => "string", "default" => ""],
            "conacyta_evento_sobre_imagen_3_alt" => ["type" => "string", "default" => ""],
            "conacyta_evento_seccion_ponentes"   => ["type" => "string", "default" => "Ponentes Magistrales"],
            "conacyta_evento_seccion_actividades" => ["type" => "string", "default" => "Actividades Paralelas"],
            "conacyta_evento_seccion_agenda"     => ["type" => "string", "default" => "Programa Oficial del Congreso"],
            "conacyta_evento_seccion_tarifas"    => ["type" => "string", "default" => "Inversion y Tarifas"],
            "conacyta_evento_seccion_comite"     => ["type" => "string", "default" => "Comite Organizador"],
            "conacyta_evento_seccion_partners"   => ["type" => "string", "default" => "Socios Estrategicos"],
            "conacyta_evento_seccion_ejes"       => ["type" => "string", "default" => "Ejes Temáticos del Congreso"],
            "conacyta_core_cleanup_on_uninstall" => ["type" => "boolean", "default" => false],

        ];

        foreach ($settings as $key => $config) {
            $sanitizer = match (true) {
                $config["type"] === "integer"                   => [\ConacytaCore\Shared\Sanitizer::class, "integer"],
                $config["type"] === "boolean"                   => [\ConacytaCore\Shared\Sanitizer::class, "boolean"],
                ($config["validate"] ?? "") === "date"          => static function ($value): string {
                    $d = \DateTime::createFromFormat("Y-m-d", (string) $value);
                    return $d ? $d->format("Y-m-d") : "";
                },
                ($config["validate"] ?? "") === "datetime"     => static function ($value): string {
                    try {
                        $d = new \DateTimeImmutable((string) $value);
                        return $d->format('Y-m-d\TH:i:sP');
                    } catch (\Exception) {
                        return "";
                    }
                },
                default => [\ConacytaCore\Shared\Sanitizer::class, "text"],
            };

            $group = $keyToGroup[$key] ?? 'identidad';
            register_setting("conacyta_evento_{$group}", $key, [
                "type"              => $config["type"],
                "default"           => $config["default"],
                "sanitize_callback" => $sanitizer,
            ]);
        }
    }

    private function addFields(): void
    {
        $subtabs = [
            'identidad' => __('Identidad del Evento', 'conacyta'),
            'sede'      => __('Sede y Organizador', 'conacyta'),
            'countdown' => __('Cuenta Regresiva', 'conacyta'),
            'sobre'     => __('Sobre la Organizacion', 'conacyta'),
            'secciones' => __('Titulos de Secciones', 'conacyta'),
        ];

        foreach ($subtabs as $slug => $title) {
            $page = 'conacyta_evento_' . $slug;
            add_settings_section($page, $title, '__return_empty_string', $page);
        }

        $fields = [
            'conacyta_evento_edicion'         => ['tab' => 'identidad', 'label' => __('Número de edición', 'conacyta'), 'type' => 'number', 'min' => '1'],
            'conacyta_evento_acronimo'        => ['tab' => 'identidad', 'label' => __('Acrónimo', 'conacyta'), 'help' => 'Ej: CONACYTA'],
            'conacyta_evento_fecha_inicio'    => ['tab' => 'identidad', 'label' => __('Fecha de inicio', 'conacyta'), 'type' => 'date', 'default' => '2026-10-12'],
            'conacyta_evento_fecha_fin'       => ['tab' => 'identidad', 'label' => __('Fecha de fin', 'conacyta'), 'type' => 'date', 'default' => '2026-10-16'],
            'conacyta_evento_url_inscripcion' => ['tab' => 'identidad', 'label' => __('URL de inscripción', 'conacyta'), 'type' => 'url'],
            'conacyta_evento_url_envio_resumen' => ['tab' => 'identidad', 'label' => __('URL de envío de resúmenes', 'conacyta'), 'type' => 'url'],
            'conacyta_evento_anio'            => ['tab' => 'identidad', 'label' => __('Año (auto)', 'conacyta'), 'readonly' => true],
            'conacyta_core_cleanup_on_uninstall' => ['tab' => 'identidad', 'label' => __('Limpiar datos al desinstalar', 'conacyta'), 'type' => 'checkbox', 'help' => __('Elimina todas las opciones al desinstalar.', 'conacyta')],

            'conacyta_evento_sede'            => ['tab' => 'sede', 'label' => __('Sede', 'conacyta')],
            'conacyta_evento_ciudad'          => ['tab' => 'sede', 'label' => __('Ciudad', 'conacyta')],
            'conacyta_evento_organizador'     => ['tab' => 'sede', 'label' => __('Organizador', 'conacyta')],
            'conacyta_evento_facultad'        => ['tab' => 'sede', 'label' => __('Facultad', 'conacyta')],

            'conacyta_evento_countdown_titulo'       => ['tab' => 'countdown', 'label' => __('Título', 'conacyta')],
            'conacyta_evento_countdown_fecha_objetivo'  => ['tab' => 'countdown', 'label' => __('Fecha objetivo del countdown', 'conacyta'), 'type' => 'datetime', 'default' => '2026-10-12T08:00:00-05:00'],
            'conacyta_evento_countdown_fase1_badge'   => ['tab' => 'countdown', 'label' => __('Badge Fase 1', 'conacyta')],
            'conacyta_evento_countdown_fase1_mensaje' => ['tab' => 'countdown', 'label' => __('Mensaje Fase 1', 'conacyta')],
            'conacyta_evento_countdown_fase2_badge'   => ['tab' => 'countdown', 'label' => __('Badge Fase 2', 'conacyta')],
            'conacyta_evento_countdown_fase2_mensaje' => ['tab' => 'countdown', 'label' => __('Mensaje Fase 2', 'conacyta')],
            'conacyta_evento_countdown_cta_texto'     => ['tab' => 'countdown', 'label' => __('Texto CTA', 'conacyta')],
            'conacyta_evento_countdown_cta_url'       => ['tab' => 'countdown', 'label' => __('URL del CTA', 'conacyta'), 'type' => 'url'],

            'conacyta_evento_sobre_titulo'      => ['tab' => 'sobre', 'label' => __('Título', 'conacyta')],
            'conacyta_evento_sobre_descripcion' => ['tab' => 'sobre', 'label' => __('Descripción', 'conacyta')],
            'conacyta_evento_sobre_imagen_1'    => ['tab' => 'sobre', 'label' => __('Imagen 1', 'conacyta'), 'media' => true],
            'conacyta_evento_sobre_imagen_1_alt' => ['tab' => 'sobre', 'label' => __('Alt imagen 1', 'conacyta')],
            'conacyta_evento_sobre_imagen_2'    => ['tab' => 'sobre', 'label' => __('Imagen 2', 'conacyta'), 'media' => true],
            'conacyta_evento_sobre_imagen_2_alt' => ['tab' => 'sobre', 'label' => __('Alt imagen 2', 'conacyta')],
            'conacyta_evento_sobre_imagen_3'    => ['tab' => 'sobre', 'label' => __('Imagen 3', 'conacyta'), 'media' => true],
            'conacyta_evento_sobre_imagen_3_alt' => ['tab' => 'sobre', 'label' => __('Alt imagen 3', 'conacyta')],

            'conacyta_evento_seccion_ponentes'   => ['tab' => 'secciones', 'label' => __('Ponentes', 'conacyta')],
            'conacyta_evento_seccion_actividades' => ['tab' => 'secciones', 'label' => __('Actividades', 'conacyta')],
            'conacyta_evento_seccion_agenda'     => ['tab' => 'secciones', 'label' => __('Programa', 'conacyta')],
            'conacyta_evento_seccion_tarifas'    => ['tab' => 'secciones', 'label' => __('Tarifas', 'conacyta')],
            'conacyta_evento_seccion_comite'     => ['tab' => 'secciones', 'label' => __('Comite', 'conacyta')],
            'conacyta_evento_seccion_partners'   => ['tab' => 'secciones', 'label' => __('Partners', 'conacyta')],
            'conacyta_evento_seccion_ejes'       => ['tab' => 'secciones', 'label' => __('Ejes Temáticos', 'conacyta')],
        ];

        foreach ($fields as $key => $config) {
            $page = 'conacyta_evento_' . $config['tab'];
            $section = $page;
            add_settings_field(
                $key,
                $config['label'],
                [$this, 'renderTextField'],
                $page,
                $section,
                ['key' => $key, 'type' => $config['type'] ?? 'text', 'default' => $config['default'] ?? '', 'textarea' => in_array($key, ['conacyta_evento_sobre_descripcion'], true), 'readonly' => $config['readonly'] ?? false, 'media' => $config['media'] ?? false, 'min' => $config['min'] ?? null, 'help' => $config['help'] ?? '']
            );
        }
    }

    public function renderTextField(array $args): void
    {
        $key         = $args["key"];
        $raw_value   = get_option($key, null);
        $value       = (null !== $raw_value && false !== $raw_value) ? (string) $raw_value : (string) ($args["default"] ?? "");
        $is_textarea = $args["textarea"] ?? false;
        $is_readonly = $args["readonly"] ?? false;
        $is_media    = $args["media"] ?? false;

        if (($args["type"] ?? "text") === "date") {
            printf(
                '<input type="date" name="%s" value="%s" data-original="%s" class="regular-text" style="max-width:14em" />',
                esc_attr($key),
            esc_attr($value),
            esc_attr($value)
            );
            if (($args["help"] ?? "") !== "") {
                echo "<p class=\"description\">" . esc_html($args["help"]) . "</p>";
            }
            return;
        }

        if (($args["type"] ?? "text") === "datetime") {
            try {
                $dt = new \DateTimeImmutable($value);
                $display = $dt->format('Y-m-d\TH:i');
            } catch (\Exception) {
                $display = '';
            }
            printf(
                '<input type="datetime-local" name="%s" value="%s" data-original="%s" class="regular-text" />',
                esc_attr($key),
                esc_attr($display),
                esc_attr($display)
            );
            if (($args["help"] ?? "") !== "") {
                echo "<p class=\"description\">" . esc_html($args["help"]) . "</p>";
            }
            return;
        }

        if (($args["type"] ?? "text") === "number") {
            $min = $args["min"] ?? "1";
            printf(
                "<input type=\"number\" name=\"" . esc_attr($key) . "\" value=\"" . esc_attr((string) $value) . "\" min=\"" . esc_attr((string) $min) . "\" class=\"small-text\" />"
            );
            if (($args["help"] ?? "") !== "") {
                echo "<p class=\"description\">" . esc_html($args["help"]) . "</p>";
            }
            return;
        }

        if (($args["type"] ?? "text") === "url") {
            printf(
                "<input type=\"url\" name=\"" . esc_attr($key) . "\" value=\"" . esc_attr((string) $value) . "\" class=\"regular-text\" />"
            );
            if (($args["help"] ?? "") !== "") {
                echo "<p class=\"description\">" . esc_html($args["help"]) . "</p>";
            }
            return;
        }

        if ($is_readonly) {
            $value = $key === "conacyta_evento_anio"
                ? (function () {
                    $fecha = get_option("conacyta_evento_fecha_inicio", "2026-10-12");
                    $ts = is_string($fecha) && '' !== $fecha ? strtotime($fecha) : false;
                    return date("Y", $ts ?: strtotime("2026-10-12"));
                })()
                : $value;
            printf(
                "<input type=\"text\" name=\"%s\" value=\"%s\" class=\"regular-text\" readonly style=\"background:#f3f4f6;color:#6b7280\" />",
                esc_attr($key),
                esc_attr((string) $value)
            );
            echo "<p class=\"description\">" . esc_html__("Calculado automaticamente desde la fecha de inicio.", "conacyta") . "</p>";
            return;
        }

        if (($args["type"] ?? "text") === "checkbox") {
            printf(
                '<input type="hidden" name="%s" value="0" />',
                esc_attr($key)
            );
            printf(
                "<label><input type=\"checkbox\" name=\"%s\" value=\"1\" %s /> %s</label>",
                esc_attr($key),
                checked($value, true, false),
                esc_html($args["help"] ?? "")
            );
            return;
        }

        if ($is_media) {
            $media_id = absint($value);
            $preview = "";
            if ($media_id > 0) {
                $img = wp_get_attachment_image_url($media_id, "medium");
                if ($img) {
                    $preview = sprintf(
                        "<img src=\"%s\" style=\"max-width:200px;display:block;margin:8px 0;border-radius:8px\" alt=\"\" />",
                        esc_url($img)
                    );
                }
            }
            printf(
                "<div class=\"conacyta-media-field\" data-key=\"%s\" data-value=\"%d\">
                    <button type=\"button\" class=\"button conacyta-media-select\">%s</button>
                    <button type=\"button\" class=\"button conacyta-media-remove\" style=\"%s\">%s</button>
                    <input type=\"hidden\" name=\"%s\" value=\"%d\" />
                    <div class=\"conacyta-media-preview\">%s</div>
                </div>",
                esc_attr($key),
                $media_id,
                esc_html__("Seleccionar imagen", "conacyta"),
                $media_id > 0 ? "" : "display:none",
                esc_html__("Eliminar", "conacyta"),
                esc_attr($key),
                $media_id,
                $preview
            );
            if (($args["help"] ?? "") !== "") {
                echo "<p class=\"description\">" . esc_html($args["help"]) . "</p>";
            }
            return;
        }

        if ($is_textarea) {
            printf(
                "<textarea name=\"" . esc_attr($key) . "\" rows=\"4\" cols=\"50\">" . esc_textarea((string) $value) . "</textarea>"
            );
        } else {
            printf(
                "<input type=\"text\" name=\"" . esc_attr($key) . "\" value=\"" . esc_attr((string) $value) . "\" class=\"regular-text\" />"
            );
        }

        if (($args["help"] ?? "") !== "") {
            echo "<p class=\"description\">" . esc_html($args["help"]) . "</p>";
        }
    }

    private function addHooks(): void
    {
        add_action("update_option_conacyta_evento_fecha_inicio", [$this, "autoAjustarFechaFin"], 10, 2);
        add_action("update_option_conacyta_evento_fecha_fin", [$this, "autoAjustarFechaInicio"], 10, 2);
    }

    public function autoAjustarFechaFin(mixed $old, mixed $new): void
    {
        $fin = get_option("conacyta_evento_fecha_fin", "");
        if (strtotime((string) $new) > strtotime((string) $fin)) {
            try {
                $old_dt   = new \DateTime((string) $old);
                $fin_dt   = new \DateTime((string) $fin);
                $duracion = max(0, (int) $old_dt->diff($fin_dt)->format('%a'));
                $nuevo_fin = (new \DateTime((string) $new))->modify("+{$duracion} days")->format('Y-m-d');
            } catch (\Exception) {
                $nuevo_fin = (string) $new;
            }
            remove_action("update_option_conacyta_evento_fecha_fin", [$this, "autoAjustarFechaInicio"], 10);
            update_option("conacyta_evento_fecha_fin", $nuevo_fin);
            add_action("update_option_conacyta_evento_fecha_fin", [$this, "autoAjustarFechaInicio"], 10, 2);
            add_settings_error(
                "conacyta_evento",
                "fecha_ajustada",
                __("La fecha de fin fue ajustada automaticamente para mantener la duracion del evento.", "conacyta"),
                "warning"
            );
        }
    }

    public function autoAjustarFechaInicio(mixed $old, mixed $new): void
    {
        $inicio = get_option("conacyta_evento_fecha_inicio", "");
        if (strtotime((string) $new) < strtotime((string) $inicio)) {
            try {
                $old_dt     = new \DateTime((string) $old);
                $inicio_dt  = new \DateTime((string) $inicio);
                $duracion   = max(0, (int) $old_dt->diff($inicio_dt)->format('%a'));
                $nuevo_inicio = (new \DateTime((string) $new))->modify("-{$duracion} days")->format('Y-m-d');
            } catch (\Exception) {
                $nuevo_inicio = (string) $new;
            }
            remove_action("update_option_conacyta_evento_fecha_inicio", [$this, "autoAjustarFechaFin"], 10);
            update_option("conacyta_evento_fecha_inicio", $nuevo_inicio);
            add_action("update_option_conacyta_evento_fecha_inicio", [$this, "autoAjustarFechaFin"], 10, 2);
            add_settings_error(
                "conacyta_evento",
                "fecha_ajustada",
                __("La fecha de inicio fue ajustada automaticamente para mantener la duracion del evento.", "conacyta"),
                "warning"
            );
        }
    }
}