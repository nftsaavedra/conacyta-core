<?php

declare(strict_types=1);

namespace ConacytaCore\Tarifa;

use ConacytaCore\Shared\MetaRegistrar;

final class TarifaCPT
{
    public function register(): void
    {
        register_post_type('tarifa', [
            'labels' => [
                'name'               => __('Tarifas', 'conacyta'),
                'singular_name'      => __('Tarifa', 'conacyta'),
                'menu_name'          => __('Tarifas', 'conacyta'),
                'add_new'            => __('Añadir Tarifa', 'conacyta'),
                'add_new_item'       => __('Añadir Nueva Tarifa', 'conacyta'),
                'edit_item'          => __('Editar Tarifa', 'conacyta'),
                'view_item'          => __('Ver Tarifa', 'conacyta'),
                'new_item'           => __('Nueva Tarifa', 'conacyta'),
                'search_items'       => __('Buscar Tarifas', 'conacyta'),
                'not_found'          => __('No se encontraron tarifas.', 'conacyta'),
                'not_found_in_trash' => __('No hay tarifas en la papelera.', 'conacyta'),
                'all_items'          => __('Tarifas', 'conacyta'),
            ],
            'public'          => true,
            'has_archive'     => true,
            'show_in_rest'    => true,
            'supports'        => ['title', 'editor'],
            'show_in_menu'       => 'conacyta-menu',
            'rewrite'         => ['slug' => 'tarifas'],
            'taxonomies'      => ['beneficio_tarifa'],
            'capability_type' => 'post',
            'show_in_admin_bar' => true,
        ]);

        MetaRegistrar::forPostType('tarifa', [
            'conacyta_core_tarifa_precio'         => 'number',
            'conacyta_core_tarifa_moneda'         => 'string',
            'conacyta_core_tarifa_destacada'      => 'boolean',
            'conacyta_core_tarifa_etiqueta'       => 'string',
            'conacyta_core_tarifa_boton_texto'    => 'string',
            'conacyta_core_tarifa_url_inscripcion' => 'url',
        ]);
    }
}
