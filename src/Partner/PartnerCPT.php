<?php

declare(strict_types=1);

namespace ConacytaCore\Partner;

use ConacytaCore\Shared\MetaRegistrar;

final class PartnerCPT
{
    public function register(): void
    {
        register_post_type('partner', [
            'labels' => [
                'name'               => __('Partners', 'conacyta'),
                'singular_name'      => __('Partner', 'conacyta'),
                'menu_name'          => __('Partners', 'conacyta'),
                'add_new'            => __('Añadir Partner', 'conacyta'),
                'add_new_item'       => __('Añadir Nuevo Partner', 'conacyta'),
                'edit_item'          => __('Editar Partner', 'conacyta'),
                'view_item'          => __('Ver Partner', 'conacyta'),
                'new_item'           => __('Nuevo Partner', 'conacyta'),
                'search_items'       => __('Buscar Partners', 'conacyta'),
                'not_found'          => __('No se encontraron partners.', 'conacyta'),
                'not_found_in_trash' => __('No hay partners en la papelera.', 'conacyta'),
                'all_items'          => __('Partners', 'conacyta'),
            ],
            'public'          => true,
            'has_archive'     => true,
            'show_in_rest'    => true,
            'supports'        => ['title', 'editor', 'thumbnail'],
            'show_in_menu'       => 'conacyta-menu',
            'rewrite'         => ['slug' => 'partners'],
            'capability_type' => 'post',
            'show_in_admin_bar' => true,
            'template'        => [
                ['core/paragraph', ['placeholder' => __('Descripción del partner...', 'conacyta')]],
            ],
        ]);

        MetaRegistrar::forPostType('partner', [
            'conacyta_core_partner_tipo' => 'string',
            'conacyta_core_partner_url'  => 'url',
        ]);
    }
}
