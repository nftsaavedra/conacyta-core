<?php

declare(strict_types=1);

namespace ConacytaCore\Ponente;

use ConacytaCore\Shared\MetaRegistrar;

final class PonenteCPT
{
    public function register(): void
    {
        register_post_type('ponente', [
            'labels' => [
                'name'               => __('Ponentes', 'conacyta'),
                'singular_name'      => __('Ponente', 'conacyta'),
                'menu_name'          => __('Ponentes', 'conacyta'),
                'add_new'            => __('Añadir Ponente', 'conacyta'),
                'add_new_item'       => __('Añadir Nuevo Ponente', 'conacyta'),
                'edit_item'          => __('Editar Ponente', 'conacyta'),
                'new_item'           => __('Nuevo Ponente', 'conacyta'),
                'view_item'          => __('Ver Ponente', 'conacyta'),
                'search_items'       => __('Buscar Ponentes', 'conacyta'),
                'not_found'          => __('No se encontraron ponentes.', 'conacyta'),
                'not_found_in_trash' => __('No hay ponentes en la papelera.', 'conacyta'),
                'all_items'          => __('Ponentes', 'conacyta'),
            ],
            'public'             => true,
            'has_archive'        => true,
            'show_in_rest'       => true,
            'show_in_menu'       => 'conacyta-menu',
            'supports'           => ['title', 'editor', 'thumbnail', 'excerpt'],
            'rewrite'            => ['slug' => 'ponentes'],
            'taxonomies'         => ['area_tematica'],
            'show_in_admin_bar'  => true,
            'capability_type'    => 'post',
        ]);

        MetaRegistrar::forPostType('ponente', [
            'conacyta_core_ponente_titulo'      => 'string',
            'conacyta_core_ponente_institucion' => 'string',
            'conacyta_core_ponente_pais'        => 'string',
            'conacyta_core_ponente_bandera_id'  => 'integer',
        ]);
    }
}
