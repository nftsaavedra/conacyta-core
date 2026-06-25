<?php

declare(strict_types=1);

namespace ConacytaCore\Agenda;

final class AuditorioTaxonomy
{
    public const TAXONOMY = 'conacyta_auditorio';

    public function register(): void
    {
        register_taxonomy(self::TAXONOMY, ['agenda_item'], [
            'labels' => [
                'name'                       => __('Auditorios', 'conacyta'),
                'singular_name'              => __('Auditorio', 'conacyta'),
                'menu_name'                  => __('Auditorios', 'conacyta'),
                'all_items'                  => __('Todos los Auditorios', 'conacyta'),
                'edit_item'                  => __('Editar Auditorio', 'conacyta'),
                'view_item'                  => __('Ver Auditorio', 'conacyta'),
                'update_item'                => __('Actualizar Auditorio', 'conacyta'),
                'add_new_item'               => __('Anadir Nuevo Auditorio', 'conacyta'),
                'new_item_name'              => __('Nombre del Nuevo Auditorio', 'conacyta'),
                'search_items'               => __('Buscar Auditorios', 'conacyta'),
                'popular_items'              => __('Auditorios Populares', 'conacyta'),
                'separate_items_with_commas' => __('Separar auditorios con comas', 'conacyta'),
                'add_or_remove_items'        => __('Anadir o quitar auditorios', 'conacyta'),
                'choose_from_most_used'      => __('Elegir de los mas usados', 'conacyta'),
                'not_found'                  => __('No se encontraron auditorios.', 'conacyta'),
                'back_to_items'              => __('Volver a Auditorios', 'conacyta'),
            ],
            'hierarchical'      => true,
            'public'            => true,
            'show_in_menu'      => 'conacyta-menu',
            'show_in_rest'      => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => false,
            'rewrite'           => [
                'slug'         => 'auditorio',
                'with_front'   => false,
                'hierarchical' => true,
            ],
            'capabilities' => [
                'manage_terms' => 'manage_categories',
                'edit_terms'   => 'manage_categories',
                'delete_terms' => 'manage_categories',
                'assign_terms' => 'edit_posts',
            ],
        ]);
    }

    /**
     * Terminos de referencia (no se insertan automaticamente).
     *
     * @return array<string, string>
     */
    public static function defaultTerms(): array
    {
        return [
            'auditorio-central'  => 'Auditorio Central',
            'sala-1'             => 'Sala 1',
            'sala-2'             => 'Sala 2',
            'sala-3'             => 'Sala 3',
            'aula-magna'         => 'Aula Magna',
            'laboratorio'        => 'Laboratorio',
            'auditorio-externo'  => 'Auditorio Externo',
            'patio-central'      => 'Patio Central',
        ];
    }
}