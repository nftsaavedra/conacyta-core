<?php

declare(strict_types=1);

namespace ConacytaCore\Agenda;

use WP_Error;
use WP_Post;
use WP_Query;

final class AgendaValidator
{
    public const TIME_REGEX = '/^([01]\d|2[0-3]):[0-5]\d$/';

    /**
     * Valida formato HH:MM 24h.
     */
    public static function isValidTime(string $time): bool
    {
        return (bool) preg_match(self::TIME_REGEX, $time);
    }

    /**
     * Valida que hora_fin > hora_inicio en minutos desde 00:00.
     */
    public static function isValidRange(string $inicio, string $fin): bool
    {
        if (!self::isValidTime($inicio) || !self::isValidTime($fin)) {
            return false;
        }

        $startMinutes = self::timeToMinutes($inicio);
        $endMinutes   = self::timeToMinutes($fin);

        return $endMinutes > $startMinutes;
    }

    /**
     * Calcula duracion en minutos (asume rango valido).
     */
    public static function duration(string $inicio, string $fin): int
    {
        if (!self::isValidRange($inicio, $fin)) {
            return 0;
        }

        return self::timeToMinutes($fin) - self::timeToMinutes($inicio);
    }

    /**
     * Detecta solapamiento con otras sesiones en mismo día + auditorio.
     *
     * @param int[] $auditorioTermIds
     * @return WP_Error|null Null si OK, WP_Error si hay conflicto.
     */
    public static function detectOverlap(
        int $postId,
        int $dia,
        array $auditorioTermIds,
        string $inicio,
        string $fin,
    ): ?WP_Error {
        if (empty($auditorioTermIds) || !self::isValidRange($inicio, $fin)) {
            return null;
        }

        $newStart = self::timeToMinutes($inicio);
        $newEnd   = self::timeToMinutes($fin);

        $args = [
            'post_type'      => 'agenda_item',
            'post_status'    => ['publish', 'future', 'pending', 'draft'],
            'posts_per_page' => 100,
            'post__not_in'   => [$postId],
            'meta_query'     => [
                [
                    'key'   => 'conacyta_core_agenda_dia',
                    'value' => $dia,
                    'type'  => 'NUMERIC',
                ],
            ],
            'tax_query'      => [
                [
                    'taxonomy' => AuditorioTaxonomy::TAXONOMY,
                    'field'    => 'term_id',
                    'terms'    => $auditorioTermIds,
                ],
            ],
        ];

        $query = new WP_Query($args);

        foreach ($query->posts as $other) {
            if (!($other instanceof WP_Post)) {
                continue;
            }

            $otherStart = (string) get_post_meta($other->ID, 'conacyta_core_agenda_hora_inicio', true);
            $otherEnd   = (string) get_post_meta($other->ID, 'conacyta_core_agenda_hora_fin', true);

            if (!self::isValidRange($otherStart, $otherEnd)) {
                continue;
            }

            $otherStartMin = self::timeToMinutes($otherStart);
            $otherEndMin   = self::timeToMinutes($otherEnd);

            if ($newStart < $otherEndMin && $otherStartMin < $newEnd) {
                return new WP_Error(
                    'agenda_overlap',
                    sprintf(
                        /* translators: %s: post title of the conflicting session */
                        __('Conflicto de horario con la sesión: %s', 'conacyta'),
                        $other->post_title
                    ),
                    ['status' => 400]
                );
            }
        }

        return null;
    }

    /**
     * Valida y normaliza los datos recibidos del editor.
     *
     * @param array<string, mixed> $data Datos crudos.
     * @return array{ok: bool, errors: WP_Error, normalized: array<string, mixed>}
     */
    public static function validateAndNormalize(array $data): array
    {
        $errors = new WP_Error();

        $dia = isset($data['conacyta_core_agenda_dia']) ? (int) $data['conacyta_core_agenda_dia'] : 0;
        $inicio = isset($data['conacyta_core_agenda_hora_inicio']) ? (string) $data['conacyta_core_agenda_hora_inicio'] : '';
        $fin    = isset($data['conacyta_core_agenda_hora_fin']) ? (string) $data['conacyta_core_agenda_hora_fin'] : '';

        $normalized = [
            'conacyta_core_agenda_dia'         => max(1, $dia),
            'conacyta_core_agenda_hora_inicio' => $inicio,
            'conacyta_core_agenda_hora_fin'    => $fin,
        ];

        if ($dia < 1) {
            $errors->add('agenda_dia_invalid', __('El día de la sesión debe ser >= 1.', 'conacyta'));
        }

        if ('' !== $inicio && !self::isValidTime($inicio)) {
            $errors->add(
                'agenda_hora_inicio_invalid',
                __('La hora de inicio debe tener formato HH:MM (24h).', 'conacyta')
            );
        }

        if ('' !== $fin && !self::isValidTime($fin)) {
            $errors->add(
                'agenda_hora_fin_invalid',
                __('La hora de fin debe tener formato HH:MM (24h).', 'conacyta')
            );
        }

        if (
            '' !== $inicio
            && '' !== $fin
            && self::isValidTime($inicio)
            && self::isValidTime($fin)
            && !self::isValidRange($inicio, $fin)
        ) {
            $errors->add(
                'agenda_hora_rango_invalido',
                __('La hora de fin debe ser estrictamente posterior a la hora de inicio.', 'conacyta')
            );
        }

        $hasErrors = $errors->has_errors();

        return [
            'ok'         => !$hasErrors,
            'errors'     => $errors,
            'normalized' => $normalized,
        ];
    }

    private static function timeToMinutes(string $time): int
    {
        [$h, $m] = array_map('intval', explode(':', $time));

        return ($h * 60) + $m;
    }
}