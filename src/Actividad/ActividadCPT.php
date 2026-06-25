<?php

declare(strict_types=1);

namespace ConacytaCore\Actividad;

use ConacytaCore\Shared\MetaRegistrar;

final class ActividadCPT
{
    public function register(): void
    {
        register_post_type('actividad', [
            'labels' => [
                'name'               => __('Actividades', 'conacyta'),
                'singular_name'      => __('Actividad', 'conacyta'),
                'menu_name'          => __('Actividades', 'conacyta'),
                'add_new'            => __('Añadir Actividad', 'conacyta'),
                'add_new_item'       => __('Añadir Nueva Actividad', 'conacyta'),
                'edit_item'          => __('Editar Actividad', 'conacyta'),
                'view_item'          => __('Ver Actividad', 'conacyta'),
                'new_item'           => __('Nueva Actividad', 'conacyta'),
                'search_items'       => __('Buscar Actividades', 'conacyta'),
                'not_found'          => __('No se encontraron actividades.', 'conacyta'),
                'not_found_in_trash' => __('No hay actividades en la papelera.', 'conacyta'),
                'all_items'          => __('Actividades', 'conacyta'),
            ],
            'public'          => true,
            'has_archive'     => true,
            'show_in_rest'    => true,
            'supports'        => ['title', 'editor', 'thumbnail'],
            'show_in_menu'       => 'conacyta-menu',
            'rewrite'         => ['slug' => 'actividades-conacyta'],
            'capability_type' => 'post',
            'show_in_admin_bar' => true,
        ]);

        MetaRegistrar::forPostType('actividad', [
            'conacyta_core_actividad_icono'          => 'string',
            'conacyta_core_actividad_color_tailwind' => 'string',
        ]);
    }
}
