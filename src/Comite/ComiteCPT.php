<?php

declare(strict_types=1);

namespace ConacytaCore\Comite;

use ConacytaCore\Shared\MetaRegistrar;

final class ComiteCPT
{
    public function register(): void
    {
        register_post_type('comite_member', [
            'labels' => [
                'name'               => __('Comité Organizador', 'conacyta'),
                'singular_name'      => __('Miembro del Comité', 'conacyta'),
                'menu_name'          => __('Comité', 'conacyta'),
                'add_new'            => __('Añadir Miembro', 'conacyta'),
                'add_new_item'       => __('Añadir Nuevo Miembro', 'conacyta'),
                'edit_item'          => __('Editar Miembro', 'conacyta'),
                'view_item'          => __('Ver Miembro', 'conacyta'),
                'new_item'           => __('Nuevo Miembro', 'conacyta'),
                'search_items'       => __('Buscar Miembros', 'conacyta'),
                'not_found'          => __('No se encontraron miembros.', 'conacyta'),
                'not_found_in_trash' => __('No hay miembros en la papelera.', 'conacyta'),
                'all_items'          => __('Comite', 'conacyta'),
            ],
            'public'          => true,
            'has_archive'     => true,
            'show_in_rest'    => true,
            'supports'        => ['title', 'editor', 'thumbnail'],
            'show_in_menu'       => 'conacyta-menu',
            'rewrite'         => ['slug' => 'comite'],
            'capability_type' => 'post',
            'show_in_admin_bar' => true,
            'template'        => [
                ['core/paragraph', ['placeholder' => __('Biografia del miembro...', 'conacyta')]],
            ],
        ]);

        MetaRegistrar::forPostType('comite_member', [
            'conacyta_core_comite_rol' => 'string',
        ]);
    }
}
