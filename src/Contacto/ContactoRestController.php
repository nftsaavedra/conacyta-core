<?php

declare(strict_types=1);

namespace ConacytaCore\Contacto;

use ConacytaCore\Shared\Auth;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

final class ContactoRestController
{
    public function register(): void
    {
        register_rest_route('conacyta/v1', '/contacto', [
            'methods'             => 'POST',
            'callback'            => [$this, 'handle'],
            'permission_callback' => [Auth::class, 'publicContactAccess'],
            'args'                => [
                'nombre'  => [
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => static function ($value): bool {
                        return '' !== trim((string) $value);
                    },
                ],
                'email'   => [
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_email',
                    'validate_callback' => static function ($value): bool {
                        return is_email((string) $value);
                    },
                ],
                'mensaje' => [
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_textarea_field',
                    'validate_callback' => static function ($value): bool {
                        return '' !== trim((string) $value) && mb_strlen((string) $value) <= 5000;
                    },
                ],
            ],
        ]);
    }

    public function handle(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $nombre  = $request->get_param('nombre');
        $email   = $request->get_param('email');
        $mensaje = $request->get_param('mensaje');

        $destinatario = get_option('conacyta_core_contacto_email', 'conacyta2026@unf.edu.pe');

        $asunto = sprintf('[CONACYTA 2026] Consulta de %s', $nombre);

        $cuerpo = sprintf(
            "Nombre: %s\nEmail: %s\n\nMensaje:\n%s",
            $nombre,
            $email,
            $mensaje
        );

        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'Reply-To: ' . $email,
        ];

        error_log(sprintf(
            '[Conacyta Contacto] Mensaje recibido: %d caracteres, email: %s',
            mb_strlen($mensaje),
            substr(hash('sha256', $email), 0, 8) . '...'
        ));

        $enviado = wp_mail($destinatario, $asunto, $cuerpo, $headers);

        if (!$enviado) {
            return new WP_Error(
                'email_error',
                __('No se pudo enviar el mensaje. Intenta de nuevo.', 'conacyta'),
                ['status' => 500]
            );
        }

        return new WP_REST_Response([
            'message' => __('Mensaje enviado correctamente. Nos pondremos en contacto pronto.', 'conacyta'),
        ], 200);
    }
}
