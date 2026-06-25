<?php

declare(strict_types=1);

namespace ConacytaCore\Abilities;

final class AbilityRegistrar
{
    public function registerCategories(): void
    {
        if (!function_exists("wp_register_ability_category")) {
            return;
        }

        wp_register_ability_category("conacyta", [
            "label"       => "CONACYTA",
            "description" => "Abilities para el XVII CONACYTA 2026",
        ]);
    }

    public function register(): void
    {
        $this->registerSiteInfo();
        $this->registerPonentes();
        $this->registerAgenda();
    }

    private function registerSiteInfo(): void
    {
        if (!function_exists("wp_register_ability")) {
            return;
        }

        wp_register_ability("conacyta/get-site-info", [
            "label"       => "Información del Evento",
            "description" => "Obtiene la información del XVII CONACYTA 2026: nombre, fechas, sede, organizador.",
            "category"    => "conacyta",
            "input_schema"  => [
                "type"       => "object",
                "properties" => [],
            ],
            "output_schema" => [
                "type"       => "object",
                "properties" => [
                    "evento"     => ["type" => "string"],
                    "edicion"    => ["type" => "integer"],
                    "acronimo"   => ["type" => "string"],
                    "anio"       => ["type" => "integer"],
                    "inicio"     => ["type" => "string", "format" => "date"],
                    "fin"        => ["type" => "string", "format" => "date"],
                    "sede"       => ["type" => "string"],
                    "ciudad"     => ["type" => "string"],
                    "organizador" => ["type" => "string"],
                    "facultad"   => ["type" => "string"],
                    "url_inscripcion" => ["type" => "string", "format" => "uri"],
                ],
            ],
            "permission_callback" => "__return_true",
            "execute_callback"    => function (array $input): array {
                return [
                    "evento"     => get_option("conacyta_evento_acronimo", "CONACYTA")
                                    . " " . get_option("conacyta_evento_edicion", 17),
                    "edicion"    => (int) get_option("conacyta_evento_edicion", 17),
                    "acronimo"   => get_option("conacyta_evento_acronimo", "CONACYTA"),
                    "anio"       => (int) get_option("conacyta_evento_anio", (int) date("Y")),
                    "inicio"     => get_option("conacyta_evento_fecha_inicio", "2026-10-12"),
                    "fin"        => get_option("conacyta_evento_fecha_fin", "2026-10-16"),
                    "sede"       => get_option("conacyta_evento_sede", "Universidad Nacional de Frontera"),
                    "ciudad"     => get_option("conacyta_evento_ciudad", "Sullana, Perú"),
                    "organizador" => get_option("conacyta_evento_organizador", "Universidad Nacional de Frontera"),
                    "facultad"   => get_option("conacyta_evento_facultad", "Facultad de Ingeniería de Industrias Alimentarias y Biotecnología"),
                    "url_inscripcion" => get_option("conacyta_evento_url_inscripcion", "/inscripcion/"),
                ];
            },
            "meta" => [
                "mcp" => [
                    "public"      => true,
                    "type"        => "tool",
                    "description" => "Información del evento XVII CONACYTA 2026: nombre, fechas, sede, organizador",
                ],
            ],
        ]);
    }

    private function registerPonentes(): void
    {
        if (!function_exists("wp_register_ability")) {
            return;
        }

        wp_register_ability("conacyta/list-ponentes", [
            "label"       => "Listar Ponentes",
            "description" => "Obtiene la lista de ponentes magistrales del XVII CONACYTA 2026.",
            "category"    => "conacyta",
            "input_schema"  => [
                "type"       => "object",
                "properties" => [],
            ],
            "output_schema" => [
                "type"  => "array",
                "items" => [
                    "type"       => "object",
                    "properties" => [
                        "id"          => ["type" => "integer"],
                        "nombre"      => ["type" => "string"],
                        "titulo"      => ["type" => "string"],
                        "institucion" => ["type" => "string"],
                        "pais"        => ["type" => "string"],
                        "enlace"      => ["type" => "string", "format" => "uri"],
                    ],
                ],
            ],
            "permission_callback" => "__return_true",
            "execute_callback"    => function (array $input): array {
                $query = new \WP_Query([
                    "post_type"      => "ponente",
                    "post_status"    => "publish",
                    "posts_per_page" => 50,
                    "orderby"        => "title",
                    "order"          => "ASC",
                ]);

                $items = [];
                foreach ($query->posts as $post) {
                    $items[] = [
                        "id"          => $post->ID,
                        "nombre"      => $post->post_title,
                        "titulo"      => get_post_meta($post->ID, "conacyta_core_ponente_titulo", true),
                        "institucion" => get_post_meta($post->ID, "conacyta_core_ponente_institucion", true),
                        "pais"        => get_post_meta($post->ID, "conacyta_core_ponente_pais", true),
                        "enlace"      => get_permalink($post->ID),
                    ];
                }

                \wp_reset_postdata();

                return $items;
            },
            "meta" => [
                "mcp" => [
                    "public"      => true,
                    "type"        => "tool",
                    "description" => "Lista de ponentes magistrales del XVII CONACYTA 2026",
                ],
            ],
        ]);
    }

    private function registerAgenda(): void
    {
        if (!function_exists("wp_register_ability")) {
            return;
        }

        wp_register_ability("conacyta/get-agenda", [
            "label"       => "Consultar Agenda",
            "description" => "Obtiene las sesiones de la agenda del XVII CONACYTA 2026 filtradas por día del evento.",
            "category"    => "conacyta",
            "input_schema"  => [
                "type"       => "object",
                "properties" => [
                    "dia" => [
                        "type"    => "integer",
                        "minimum" => 1,
                        "default" => 1,
                    ],
                    "auditorio" => [
                        "type" => "string",
                    ],
                    "tipo" => [
                        "type" => "string",
                    ],
                ],
            ],
            "output_schema" => [
                "type"  => "array",
                "items" => [
                    "type"       => "object",
                    "properties" => [
                        "id"          => ["type" => "integer"],
                        "titulo"      => ["type" => "string"],
                        "hora_inicio" => ["type" => "string"],
                        "hora_fin"    => ["type" => "string"],
                        "auditorios"  => ["type" => "array", "items" => ["type" => "string"]],
                        "tipos"       => ["type" => "array", "items" => ["type" => "string"]],
                        "color_dot"   => ["type" => "string"],
                        "ponente_id"  => ["type" => "integer"],
                        "ponente"     => ["type" => "string"],
                        "destacada"   => ["type" => "boolean"],
                    ],
                ],
            ],
            "permission_callback" => "__return_true",
            "execute_callback"    => function (array $input): array {
                $dia       = $input["dia"] ?? 1;
                $auditorio = $input["auditorio"] ?? null;
                $tipo      = $input["tipo"] ?? null;

                $args = [
                    "post_type"      => "agenda_item",
                    "post_status"    => "publish",
                    "posts_per_page" => 100,
                    "orderby"        => "meta_value",
                    "meta_key"       => "conacyta_core_agenda_hora_inicio",
                    "order"          => "ASC",
                    "meta_query"     => [
                        [
                            "key"   => "conacyta_core_agenda_dia",
                            "value" => $dia,
                            "type"  => "NUMERIC",
                        ],
                    ],
                    "tax_query" => [],
                ];

                if ($auditorio) {
                    $term = get_term_by("slug", sanitize_title($auditorio), \ConacytaCore\Agenda\AuditorioTaxonomy::TAXONOMY);
                    if ($term instanceof \WP_Term) {
                        $args["tax_query"][] = [
                            "taxonomy" => \ConacytaCore\Agenda\AuditorioTaxonomy::TAXONOMY,
                            "field"    => "term_id",
                            "terms"    => $term->term_id,
                        ];
                    }
                }

                if ($tipo) {
                    $term = get_term_by("slug", sanitize_title($tipo), \ConacytaCore\Agenda\AgendaTipoTaxonomy::TAXONOMY);
                    if ($term instanceof \WP_Term) {
                        $args["tax_query"][] = [
                            "taxonomy" => \ConacytaCore\Agenda\AgendaTipoTaxonomy::TAXONOMY,
                            "field"    => "term_id",
                            "terms"    => $term->term_id,
                        ];
                    }
                }

                $query = new \WP_Query($args);

                $items = [];
                foreach ($query->posts as $post) {
                    $ponente_id = (int) get_post_meta($post->ID, "conacyta_core_agenda_ponente_id", true);
                    $ponente    = "";
                    if ($ponente_id > 0) {
                        $ponente_post = get_post($ponente_id);
                        if ($ponente_post && $ponente_post->post_type === "ponente") {
                            $ponente = $ponente_post->post_title;
                        }
                    }

                    $auditorios = wp_get_object_terms($post->ID, \ConacytaCore\Agenda\AuditorioTaxonomy::TAXONOMY, ["fields" => "slugs"]);
                    $tipos      = wp_get_object_terms($post->ID, \ConacytaCore\Agenda\AgendaTipoTaxonomy::TAXONOMY, ["fields" => "slugs"]);

                    $items[] = [
                        "id"          => $post->ID,
                        "titulo"      => $post->post_title,
                        "hora_inicio" => get_post_meta($post->ID, "conacyta_core_agenda_hora_inicio", true),
                        "hora_fin"    => get_post_meta($post->ID, "conacyta_core_agenda_hora_fin", true),
                        "auditorios"  => is_array($auditorios) ? $auditorios : [],
                        "tipos"       => is_array($tipos) ? $tipos : [],
                        "color_dot"   => get_post_meta($post->ID, "conacyta_core_agenda_color_dot", true),
                        "ponente_id"  => $ponente_id,
                        "ponente"     => $ponente,
                    ];
                }

                \wp_reset_postdata();

                return $items;
            },
            "meta" => [
                "mcp" => [
                    "public"      => true,
                    "type"        => "tool",
                    "description" => "Agenda del XVII CONACYTA 2026 filtrada por dia, auditorio y tipo.",
                ],
            ],
        ]);
    }
}