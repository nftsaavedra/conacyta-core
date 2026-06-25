<?php

declare(strict_types=1);

namespace ConacytaCore\Tarifa;

final class BeneficioTarifaTaxonomy
{
    public const TAXONOMY = 'beneficio_tarifa';

    public function register(): void
    {
        register_taxonomy(self::TAXONOMY, ['tarifa'], [
            'labels' => [
                'name'                       => __('Beneficios', 'conacyta'),
                'singular_name'              => __('Beneficio', 'conacyta'),
                'menu_name'                  => __('Beneficios', 'conacyta'),
                'all_items'                  => __('Todos los Beneficios', 'conacyta'),
                'edit_item'                  => __('Editar Beneficio', 'conacyta'),
                'view_item'                  => __('Ver Beneficio', 'conacyta'),
                'update_item'                => __('Actualizar Beneficio', 'conacyta'),
                'add_new_item'               => __('Anadir Nuevo Beneficio', 'conacyta'),
                'new_item_name'              => __('Nombre del Nuevo Beneficio', 'conacyta'),
                'search_items'               => __('Buscar Beneficios', 'conacyta'),
                'popular_items'              => __('Beneficios Populares', 'conacyta'),
                'separate_items_with_commas' => __('Separar beneficios con comas', 'conacyta'),
                'add_or_remove_items'        => __('Anadir o quitar beneficios', 'conacyta'),
                'choose_from_most_used'      => __('Elegir de los mas usados', 'conacyta'),
                'not_found'                  => __('No se encontraron beneficios.', 'conacyta'),
                'back_to_items'              => __('Volver a Beneficios', 'conacyta'),
            ],
            'hierarchical'      => false,
            'public'            => true,
            'show_in_menu'      => 'conacyta-menu',
            'show_in_rest'      => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => false,
            'rewrite'           => [
                'slug'       => 'beneficio-tarifa',
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
            'acceso-ponencias'          => 'Acceso a ponencias magistrales',
            'certificado-asistencia'    => 'Certificado de asistencia',
            'publicacion-indexada'      => 'Publicación en revista indexada',
            'kit-congreso'              => 'Kit del congreso',
            'coffee-breaks'             => 'Coffee breaks',
            'feria-gastronomica'        => 'Feria gastronómica',
            'material-didactico'        => 'Material didáctico',
            'certificado-curso'         => 'Certificado del curso',
            'exposicion-investigacion'  => 'Exposición de investigación',
            'derecho-publicacion'       => 'Derecho a publicación',
            'certificacion-especial'    => 'Certificación especial',
            'acceso-completo'           => 'Acceso completo al evento',
            'ceremonias-centrales'      => 'Ceremonias centrales',
            'curso-especializacion'     => 'Curso de especializacion',
        ];
    }
}
