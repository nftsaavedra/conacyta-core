<?php

declare(strict_types=1);

namespace ConacytaCore\Agenda;

use ConacytaCore\Shared\EventDateHelper;

final class AgendaVariation
{
    public const NS_COMPLETA = 'conacyta/agenda-completa';
    public const NS_DIA_PREFIX = 'conacyta/agenda-dia-';

    public function get(): array
    {
        $variations   = [];
        $variations[] = $this->buildVariacionCompleta();
        $variations   = array_merge($variations, $this->buildVariacionesPorDia());

        return $variations;
    }

    private function buildVariacionCompleta(): array
    {
        return [
            'name'        => self::NS_COMPLETA,
            'title'       => __('Agenda completa', 'conacyta'),
            'description' => __('Muestra todas las sesiones de la agenda ordenadas por día y hora.', 'conacyta'),
            'icon'        => 'list-view',
            'isActive'    => ['namespace'],
            'attributes'  => [
                'namespace' => self::NS_COMPLETA,
                'query'     => [
                    'postType'  => 'agenda_item',
                    'perPage'   => 100,
                    'orderBy'   => 'meta_value_num',
                    'metaKey'   => 'conacyta_core_agenda_dia',
                    'order'     => 'ASC',
                ],
            ],
            'scope'       => ['inserter'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildVariacionesPorDia(): array
    {
        $variations = [];

        $totalDias = EventDateHelper::getTotalDias();

        for ($dia = 1; $dia <= $totalDias; $dia++) {
            $variations[] = [
                'name'        => self::NS_DIA_PREFIX . $dia,
                'title'       => sprintf(__('Agenda - Día %d', 'conacyta'), $dia),
                'description' => sprintf(__('Muestra las sesiones del día %d del evento.', 'conacyta'), $dia),
                'icon'        => 'clock',
                'isActive'    => ['namespace'],
                'attributes'  => [
                    'namespace' => self::NS_DIA_PREFIX . $dia,
                    'query'     => [
                        'postType'  => 'agenda_item',
                        'perPage'   => 50,
                        'orderBy'   => ['meta_value' => 'ASC'],
                        'metaKey'   => 'conacyta_core_agenda_hora_inicio',
                        'order'     => 'ASC',
                    ],
                ],
                'scope'       => ['inserter'],
            ];
        }

        return $variations;
    }
}