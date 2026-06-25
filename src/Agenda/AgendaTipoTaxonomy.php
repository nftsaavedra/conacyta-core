<?php

declare(strict_types=1);

namespace ConacytaCore\Agenda;

final class AgendaTipoTaxonomy
{
    public const TAXONOMY = 'conacyta_agenda_tipo';

    public function register(): void
    {
        register_taxonomy(self::TAXONOMY, ['agenda_item'], [
            'labels' => [
                'name'                       => __('Tipos de Sesión', 'conacyta'),
                'singular_name'              => __('Tipo de Sesión', 'conacyta'),
                'menu_name'                  => __('Tipos de Sesión', 'conacyta'),
                'all_items'                  => __('Todos los Tipos', 'conacyta'),
                'edit_item'                  => __('Editar Tipo', 'conacyta'),
                'view_item'                  => __('Ver Tipo', 'conacyta'),
                'update_item'                => __('Actualizar Tipo', 'conacyta'),
                'add_new_item'               => __('Anadir Nuevo Tipo', 'conacyta'),
                'new_item_name'              => __('Nombre del Nuevo Tipo', 'conacyta'),
                'search_items'               => __('Buscar Tipos', 'conacyta'),
                'popular_items'              => __('Tipos Populares', 'conacyta'),
                'separate_items_with_commas' => __('Separar tipos con comas', 'conacyta'),
                'add_or_remove_items'        => __('Anadir o quitar tipos', 'conacyta'),
                'choose_from_most_used'      => __('Elegir de los mas usados', 'conacyta'),
                'not_found'                  => __('No se encontraron tipos.', 'conacyta'),
                'back_to_items'              => __('Volver a Tipos', 'conacyta'),
            ],
            'hierarchical'      => false,
            'public'            => true,
            'show_in_menu'      => 'conacyta-menu',
            'show_in_rest'      => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => false,
            'rewrite'           => [
                'slug'       => 'tipo-sesion',
                'with_front' => false,
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
            'magistral-internacional' => 'Magistral Internacional',
            'magistral-nacional'      => 'Magistral Nacional',
            'simultanea'              => 'Simultanea',
            'conferencia'             => 'Conferencia',
            'curso'                   => 'Curso',
            'taller'                  => 'Taller',
            'mesa-redonda'            => 'Mesa Redonda',
            'poster'                  => 'Poster / Exposicion',
            'ceremonia'               => 'Ceremonia',
            'receso'                  => 'Receso',
            'registro'                => 'Registro / Inscripción',
            'clausura'                => 'Clausura',
        ];
    }
}