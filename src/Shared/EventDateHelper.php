<?php

declare(strict_types=1);

namespace ConacytaCore\Shared;

final class EventDateHelper
{
    /**
     * Calcula la cantidad total de dias del evento (inclusivo).
     * Usa format('%a') para evitar problemas de DST con diff()->days.
     */
    public static function getTotalDias(): int
    {
        $inicio = get_option('conacyta_evento_fecha_inicio', '2026-10-12');
        $fin    = get_option('conacyta_evento_fecha_fin', '2026-10-16');

        try {
            $start = new \DateTime($inicio);
            $end   = new \DateTime($fin);
        } catch (\Exception) {
            return 5;
        }

        return max(1, (int) $start->diff($end)->format('%a') + 1);
    }
}
