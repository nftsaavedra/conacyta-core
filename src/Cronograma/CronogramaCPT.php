<?php

declare(strict_types=1);

namespace ConacytaCore\Cronograma;

use ConacytaCore\Shared\MetaRegistrar;

final class CronogramaCPT
{
    public function register(): void
    {
        register_post_type('cronograma_fase', [
            'labels' => [
                'name'               => __('Convocatoria', 'conacyta'),
                'singular_name'      => __('Fase Convocatoria', 'conacyta'),
                'menu_name'          => __('Convocatoria', 'conacyta'),
                'add_new'            => __('Añadir Fase', 'conacyta'),
                'add_new_item'       => __('Añadir Nueva Fase', 'conacyta'),
                'edit_item'          => __('Editar Fase', 'conacyta'),
                'view_item'          => __('Ver Fase', 'conacyta'),
                'new_item'           => __('Nueva Fase', 'conacyta'),
                'search_items'       => __('Buscar Fases', 'conacyta'),
                'not_found'          => __('No se encontraron fases.', 'conacyta'),
                'not_found_in_trash' => __('No hay fases en la papelera.', 'conacyta'),
                'all_items'          => __('Convocatoria', 'conacyta'),
            ],
            'public'             => true,
            'has_archive'        => true,
            'show_in_rest'       => true,
            'show_in_menu'       => 'conacyta-menu',
            'supports'           => ['title', 'editor'],
            'rewrite'            => ['slug' => 'cronograma'],
            'show_in_admin_bar'  => true,
            'capability_type'    => 'post',
        ]);

        MetaRegistrar::forPostType('cronograma_fase', [
            'conacyta_core_fase_fecha_inicio' => 'string',
            'conacyta_core_fase_fecha_fin'    => 'string',
            'conacyta_core_fase_destacada'    => 'boolean',
        ]);
    }
}
