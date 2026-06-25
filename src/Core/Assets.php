<?php

declare(strict_types=1);

namespace ConacytaCore\Core;

final class Assets
{
    public function enqueue(): void
    {
        wp_register_script('conacyta-core-frontend', false, [], CONACYTA_CORE_VERSION, true);
        wp_enqueue_script('conacyta-core-frontend');

        wp_localize_script('conacyta-core-frontend', 'conacytaData', [
            'restUrl'          => esc_url_raw(rest_url('conacyta/v1')),
            'nonce'            => wp_create_nonce('wp_rest'),
            'chatEndpoint'     => esc_url_raw(rest_url('conacyta/v1/chat')),
            'eventoFechaInicio' => get_option('conacyta_evento_fecha_inicio', '2026-10-12'),
            'eventoFechaFin'   => get_option('conacyta_evento_fecha_fin', '2026-10-16'),
        ]);
    }
}

