<?php

declare(strict_types=1);

namespace ConacytaCore\Agenda;

use ConacytaCore\Shared\MetaRegistrar;

final class AgendaCPT
{
    public function register(): void
    {
        register_post_type('agenda_item', [
            'labels' => [
                'name'               => __('Agenda', 'conacyta'),
                'singular_name'      => __('Sesión de Agenda', 'conacyta'),
                'menu_name'          => __('Agenda', 'conacyta'),
                'add_new'            => __('Añadir Sesión', 'conacyta'),
                'add_new_item'       => __('Añadir Nueva Sesión', 'conacyta'),
                'edit_item'          => __('Editar Sesión', 'conacyta'),
                'new_item'           => __('Nueva Sesión', 'conacyta'),
                'view_item'          => __('Ver Sesión', 'conacyta'),
                'view_items'         => __('Ver Sesiones', 'conacyta'),
                'search_items'       => __('Buscar Sesiones', 'conacyta'),
                'not_found'          => __('No se encontraron sesiones.', 'conacyta'),
                'not_found_in_trash' => __('No hay sesiones en la papelera.', 'conacyta'),
                'all_items'          => __('Todas las Sesiones', 'conacyta'),
                'archives'           => __('Archivo de Agenda', 'conacyta'),
                'attributes'         => __('Atributos de la sesión', 'conacyta'),
            ],
            'public'             => true,
            'has_archive'        => true,
            'show_in_rest'       => true,
            'supports'           => ['title', 'editor', 'thumbnail', 'excerpt', 'revisions'],
            'show_in_menu'       => 'conacyta-menu',
            'rewrite'            => ['slug' => 'agenda', 'with_front' => false],
            'taxonomies'         => ['conacyta_auditorio', 'conacyta_agenda_tipo'],
            'capability_type'    => 'post',
            'show_in_admin_bar'  => true,
        ]);

        MetaRegistrar::forPostType('agenda_item', [
            'conacyta_core_agenda_dia'              => 'integer',
            'conacyta_core_agenda_hora_inicio'      => 'string',
            'conacyta_core_agenda_hora_fin'         => 'string',
            'conacyta_core_agenda_ponente_id'       => 'integer',
            'conacyta_core_agenda_color_dot'        => 'string',
            'conacyta_core_agenda_duracion_minutos' => 'integer',
            'conacyta_core_agenda_orden'            => 'integer',
        ], static function (int $postId, string $key, mixed $value): mixed {
            if ($key === 'conacyta_core_agenda_dia' && (int) $value < 1) {
                return 1;
            }
            return $value;
        });
    }
}
