<?php

declare(strict_types=1);

namespace ConacytaCore\Agenda;

use WP_Error;
use WP_Query;
use WP_REST_Request;
use WP_REST_Response;

final class AgendaRestController
{
    private const CACHE_TTL = 300;

    public function register(): void
    {
        register_rest_route('conacyta/v1', '/agenda', [
            'methods'             => 'GET',
            'callback'            => [$this, 'handle'],
            'permission_callback' => '__return_true', // Datos publicos de agenda — sin restriccion de acceso
            'args'                => [
                'dia'         => [
                    'type'              => 'integer',
                    'minimum'           => 1,
                    'sanitize_callback' => 'absint',
                ],
                'auditorio'   => [
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_title',
                ],
                'tipo'        => [
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_title',
                ],
                'ponente_id'  => [
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                ],
                'per_page'    => [
                    'type'              => 'integer',
                    'minimum'           => 1,
                    'maximum'           => 100,
                    'default'           => 50,
                    'sanitize_callback' => 'absint',
                    'validate_callback' => static function ($value): bool {
                        return $value >= 1 && $value <= 100;
                    },
                ],
                'page'        => [
                    'type'              => 'integer',
                    'minimum'           => 1,
                    'default'           => 1,
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);
    }

    /**
     * GET /wp-json/conacyta/v1/agenda
     */
    public function handle(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $params = $request->get_params();
        ksort($params);
        $cacheKey = 'conacyta_agenda_rest_' . md5(wp_json_encode($params));
        $cached = get_transient($cacheKey);

        if (is_array($cached)) {
            return new WP_REST_Response($cached, 200);
        }

        $dia        = $request->get_param('dia');
        $auditorio  = $request->get_param('auditorio');
        $tipo       = $request->get_param('tipo');
        $ponenteId  = $request->get_param('ponente_id');
        $perPage    = $request->get_param('per_page');
        $page       = $request->get_param('page');

        $args = [
            'post_type'      => 'agenda_item',
            'post_status'    => 'publish',
            'posts_per_page' => (int) $perPage,
            'paged'          => (int) $page,
            'orderby'        => ['meta_value' => 'ASC'],
            'meta_key'       => 'conacyta_core_agenda_hora_inicio',
            'order'          => 'ASC',
            'meta_query'     => [],
            'tax_query'      => [],
        ];

        if (null !== $dia && $dia) {
            $args['meta_query'][] = [
                'key'   => 'conacyta_core_agenda_dia',
                'value' => (int) $dia,
                'type'  => 'NUMERIC',
            ];
        }

        if (null !== $ponenteId && $ponenteId) {
            $args['meta_query'][] = [
                'key'     => 'conacyta_core_agenda_ponente_id',
                'value'   => (int) $ponenteId,
                'type'    => 'NUMERIC',
                'compare' => '=',
            ];
        }

        if (null !== $auditorio && '' !== $auditorio) {
            $term = get_term_by('slug', $auditorio, AuditorioTaxonomy::TAXONOMY);
            if ($term instanceof \WP_Term) {
                $args['tax_query'][] = [
                    'taxonomy' => AuditorioTaxonomy::TAXONOMY,
                    'field'    => 'term_id',
                    'terms'    => $term->term_id,
                ];
            }
        }

        if (null !== $tipo && '' !== $tipo) {
            $term = get_term_by('slug', $tipo, AgendaTipoTaxonomy::TAXONOMY);
            if ($term instanceof \WP_Term) {
                $args['tax_query'][] = [
                    'taxonomy' => AgendaTipoTaxonomy::TAXONOMY,
                    'field'    => 'term_id',
                    'terms'    => $term->term_id,
                ];
            }
        }

        if (count($args['tax_query']) > 1) {
            $args['tax_query']['relation'] = 'AND';
        }

        $query = new WP_Query($args);
        $items = [];

        foreach ($query->posts as $post) {
            $auditorios = wp_get_object_terms($post->ID, AuditorioTaxonomy::TAXONOMY, ['fields' => 'slugs']);
            $tipos      = wp_get_object_terms($post->ID, AgendaTipoTaxonomy::TAXONOMY, ['fields' => 'slugs']);

            $ponenteIdVal = (int) get_post_meta($post->ID, 'conacyta_core_agenda_ponente_id', true);
            $ponente      = null;
            if ($ponenteIdVal > 0) {
                $ponentePost = get_post($ponenteIdVal);
                if ($ponentePost && 'ponente' === $ponentePost->post_type) {
                    $banderaId = (int) get_post_meta($ponentePost->ID, 'conacyta_core_ponente_bandera_id', true);
                    $bandera   = null;
                    if ($banderaId > 0) {
                        $imgUrl = wp_get_attachment_image_url($banderaId, 'thumbnail');
                        if ($imgUrl) {
                            $bandera = ['id' => $banderaId, 'url' => $imgUrl];
                        }
                    }

                    $ponente = [
                        'id'          => $ponentePost->ID,
                        'nombre'      => $ponentePost->post_title,
                        'titulo'      => get_post_meta($ponentePost->ID, 'conacyta_core_ponente_titulo', true),
                        'institucion' => get_post_meta($ponentePost->ID, 'conacyta_core_ponente_institucion', true),
                        'pais'        => get_post_meta($ponentePost->ID, 'conacyta_core_ponente_pais', true),
                        'bandera'     => $bandera,
                        'enlace'      => get_permalink($ponentePost->ID),
                    ];
                }
            }

            $items[] = [
                'id'               => $post->ID,
                'titulo'           => $post->post_title,
                'extracto'         => get_the_excerpt($post),
                'enlace'           => get_permalink($post),
                'dia'              => (int) get_post_meta($post->ID, 'conacyta_core_agenda_dia', true),
                'hora_inicio'      => get_post_meta($post->ID, 'conacyta_core_agenda_hora_inicio', true),
                'hora_fin'         => get_post_meta($post->ID, 'conacyta_core_agenda_hora_fin', true),
                'auditorios'       => is_array($auditorios) ? $auditorios : [],
                'tipos'            => is_array($tipos) ? $tipos : [],
                'ponente'          => $ponente,
                'color_dot'        => get_post_meta($post->ID, 'conacyta_core_agenda_color_dot', true),
                'duracion_minutos' => (int) get_post_meta($post->ID, 'conacyta_core_agenda_duracion_minutos', true),
                'imagen'           => get_the_post_thumbnail_url($post->ID, 'medium') ?: null,
            ];
        }

        wp_reset_postdata();

        $result = [
            'items'       => $items,
            'total'       => $query->found_posts,
            'total_pages' => $query->max_num_pages,
            'page'        => (int) $page,
            'per_page'    => (int) $perPage,
        ];

        set_transient($cacheKey, $result, self::CACHE_TTL);

        $response = new WP_REST_Response($result, 200);
        $response->header('X-WP-Total', (string) $query->found_posts);
        $response->header('X-WP-TotalPages', (string) $query->max_num_pages);

        return $response;
    }
}