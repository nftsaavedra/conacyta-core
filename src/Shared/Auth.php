<?php

declare(strict_types=1);

namespace ConacytaCore\Shared;

final class Auth
{
    public static function publicChatAccess(): bool|\WP_Error
    {
        return self::publicAccess('chat');
    }

    public static function publicContactAccess(): bool|\WP_Error
    {
        return self::publicAccess('contacto');
    }

    private static function publicAccess(string $endpoint): bool|\WP_Error
    {
        $nonce = wp_unslash($_SERVER['HTTP_X_WP_NONCE'] ?? '');

        if (!wp_verify_nonce((string) $nonce, 'wp_rest')) {
            return new \WP_Error(
                'rest_forbidden',
                __('Nonce inválido o expirado.', 'conacyta'),
                ['status' => 403]
            );
        }

        $rate_limit = (int) get_option('conacyta_core_chat_rate_limit', 60);
        $ip = self::getClientIp();
        $transient_key = 'conacyta_core_rate_' . $endpoint . '_' . md5($ip);
        $count = (int) get_transient($transient_key);

        if ($count >= $rate_limit) {
            return new \WP_Error(
                'rate_limit_exceeded',
                __('Límite de solicitudes excedido. Intenta de nuevo en un minuto.', 'conacyta'),
                ['status' => 429]
            );
        }

        set_transient($transient_key, $count + 1, 60);

        return true;
    }

    public static function adminAccess(): bool
    {
        return current_user_can("manage_options");
    }

    private static function getClientIp(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR',
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = explode(',', (string) $_SERVER[$header])[0];
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                    return $ip;
                }
            }
        }

        return '127.0.0.1';
    }
}
