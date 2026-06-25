<?php

declare(strict_types=1);

namespace ConacytaCore\AreaTematica;

final class AreaTematicaTaxonomy
{
    public function register(): void
    {
        register_taxonomy('area_tematica', 'ponente', [
            'labels' => [
                'name'              => __('Áreas Temáticas', 'conacyta'),
                'singular_name'     => __('Área Temática', 'conacyta'),
                'menu_name'         => __('Áreas Temáticas', 'conacyta'),
                'search_items'      => __('Buscar Áreas Temáticas', 'conacyta'),
                'all_items'         => __('Todas las Áreas Temáticas', 'conacyta'),
                'edit_item'         => __('Editar Área Temática', 'conacyta'),
                'update_item'       => __('Actualizar Área Temática', 'conacyta'),
                'add_new_item'      => __('Añadir Nueva Área Temática', 'conacyta'),
                'new_item_name'     => __('Nombre de Nueva Área Temática', 'conacyta'),
            ],
            'hierarchical'      => true,
            'public'            => true,
            'show_in_menu'      => 'conacyta-menu',
            'show_in_rest'      => true,
            'show_admin_column' => true,
            'rewrite'           => ['slug' => 'ejes-tematicos'],
            'capabilities'      => [
                'manage_terms' => 'manage_categories',
                'edit_terms'   => 'manage_categories',
                'delete_terms' => 'manage_categories',
                'assign_terms' => 'edit_posts',
            ],
        ]);
    }
}
