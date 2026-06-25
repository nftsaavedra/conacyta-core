<?php

declare(strict_types=1);

namespace ConacytaCore\Portada;

use ConacytaCore\Shared\MetaRegistrar;

final class PortadaCPT
{
    public function register(): void
    {
        register_post_type('portada', [
            'labels' => [
                'name'               => __('Portadas', 'conacyta'),
                'singular_name'      => __('Portada', 'conacyta'),
                'menu_name'          => __('Portadas', 'conacyta'),
                'add_new'            => __('Añadir Portada', 'conacyta'),
                'add_new_item'       => __('Añadir Nueva Portada', 'conacyta'),
                'edit_item'          => __('Editar Portada', 'conacyta'),
                'view_item'          => __('Ver Portada', 'conacyta'),
                'new_item'           => __('Nueva Portada', 'conacyta'),
                'search_items'       => __('Buscar Portadas', 'conacyta'),
                'not_found'          => __('No se encontraron portadas.', 'conacyta'),
                'not_found_in_trash' => __('No hay portadas en la papelera.', 'conacyta'),
                'all_items'          => __('Portadas', 'conacyta'),
            ],
            'public'             => true,
            'has_archive'        => false,
            'show_in_rest'       => true,
            'show_in_menu'       => 'conacyta-menu',
            'supports'           => ['title', 'editor', 'thumbnail'],
            'capability_type'    => 'post',
            'show_in_admin_bar'  => true,
            'rewrite'            => ['slug' => 'portadas'],
            'template'           => [
                ['core/paragraph', ['placeholder' => __('Contenido adicional opcional para el hero...', 'conacyta')]],
            ],
        ]);

        MetaRegistrar::forPostType('portada', [
            'conacyta_core_portada_principal'  => 'boolean',
            'conacyta_core_portada_tagline'    => 'string',
            'conacyta_core_portada_cta_texto'  => 'string',
            'conacyta_core_portada_cta_url'    => 'url',
            'conacyta_core_portada_cta2_texto' => 'string',
            'conacyta_core_portada_cta2_url'   => 'url',
        ]);

        add_action('save_post_portada', [$this, 'enforceSinglePrincipal'], 10, 3);
    }

    public function enforceSinglePrincipal(int $post_id, \WP_Post $post, bool $update): void
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        $is_principal = get_post_meta($post_id, 'conacyta_core_portada_principal', true);

        if ('1' !== $is_principal && true !== $is_principal) {
            return;
        }

        $others = get_posts([
            'post_type'      => 'portada',
            'post__not_in'   => [$post_id],
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_key'       => 'conacyta_core_portada_principal',
            'meta_value'     => '1',
        ]);

        foreach ($others as $other_id) {
            delete_post_meta($other_id, 'conacyta_core_portada_principal');
        }
    }
}
