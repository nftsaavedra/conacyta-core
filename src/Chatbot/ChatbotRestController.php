<?php

declare(strict_types=1);

namespace ConacytaCore\Chatbot;

use ConacytaCore\Shared\Auth;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

final class ChatbotRestController
{
    private const MAX_MESSAGE_CHARS = 500;
    private const MAX_SESSION_CHARS = 64;

    public function register(): void
    {
        register_rest_route('conacyta/v1', '/chat', [
            'methods'             => 'POST',
            'callback'            => [$this, 'handle'],
            'permission_callback' => [Auth::class, 'publicChatAccess'],
            'args'                => [
                'message' => [
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => [\ConacytaCore\Shared\Sanitizer::class, 'html'],
                    'validate_callback' => function ($value): bool {
                        $trimmed = trim((string) $value);
                        return '' !== $trimmed && mb_strlen($trimmed) <= self::MAX_MESSAGE_CHARS;
                    },
                ],
                'session_id' => [
                    'required'          => false,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_key',
                    'validate_callback' => function ($value): bool {
                        return strlen((string) $value) <= self::MAX_SESSION_CHARS;
                    },
                ],
            ],
        ]);

        register_rest_route('conacyta/v1', '/chat/history', [
            'methods'             => 'GET',
            'callback'            => [$this, 'handleHistory'],
            'permission_callback' => [Auth::class, 'publicChatAccess'],
            'args'                => [
                'session_id' => [
                    'required'          => false,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_key',
                    'validate_callback' => function ($value): bool {
                        return strlen((string) $value) <= self::MAX_SESSION_CHARS;
                    },
                ],
            ],
        ]);
    }

    public function handle(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $message   = $request->get_param('message');
        $sessionId = $request->get_param('session_id') ?: null;

        $trimmed = trim((string) $message);
        if ('' === $trimmed) {
            return new WP_Error(
                'empty_message',
                __('El mensaje no puede estar vacio.', 'conacyta'),
                ['status' => 400]
            );
        }

        if (mb_strlen($trimmed) > self::MAX_MESSAGE_CHARS) {
            return new WP_Error(
                'message_too_long',
                sprintf(
                    __('El mensaje no puede exceder %d caracteres.', 'conacyta'),
                    self::MAX_MESSAGE_CHARS
                ),
                ['status' => 400]
            );
        }

        if ($sessionId === null) {
            $sessionId = wp_generate_uuid4();
        }

        try {
            $provider = get_option('conacyta_core_ai_provider', 'gemini');

            if ($provider === 'deepseek') {
                $client = new DeepSeekClient($sessionId);
            } else {
                $client = new GeminiClient($sessionId);
            }

            $history = $client->getHistory();
            $result  = $client->sendMessage($trimmed, $history);

            if (isset($result['error'])) {
                $provider = get_option('conacyta_core_ai_provider', 'gemini');
                return new WP_Error(
                    $provider === 'deepseek' ? 'ai_service_error' : 'gemini_error',
                    $result['error'],
                    ['status' => 500]
                );
            }

            return new WP_REST_Response([
                'reply'       => $result['reply'] ?? '',
                'session_id'  => $sessionId,
                'suggestions' => $result['suggestions'] ?? [],
            ], 200);
        } catch (\Throwable $e) {
            error_log('[Conacyta Chat] Error interno: ' . $e->getMessage());
            return new WP_Error(
                'chat_error',
                __('Error interno al procesar la consulta.', 'conacyta'),
                ['status' => 500]
            );
        }
    }

    public function handleHistory(WP_REST_Request $request): WP_REST_Response
    {
        nocache_headers();

        $sessionId = $request->get_param('session_id') ?: null;
        if ($sessionId === null) {
            $sessionId = wp_generate_uuid4();
        }

        try {
            $provider  = get_option('conacyta_core_ai_provider', 'gemini');
            $client    = ($provider === 'deepseek')
                ? new DeepSeekClient($sessionId)
                : new GeminiClient($sessionId);

            $stored   = $client->getHistory();
            $messages = [];
            foreach ($stored as $entry) {
                $role = isset($entry['role']) && $entry['role'] === 'user' ? 'user' : 'bot';
                $messages[] = [
                    'role' => $role,
                    'text' => (string) ($entry['text'] ?? ''),
                ];
            }

            return new WP_REST_Response([
                'session_id' => $sessionId,
                'messages'   => $messages,
                'total'      => count($messages),
            ], 200);
        } catch (\Throwable $e) {
            error_log('[Conacyta Chat History] Error: ' . $e->getMessage());
            return new WP_REST_Response([
                'session_id' => $sessionId,
                'messages'   => [],
                'total'      => 0,
            ], 200);
        }
    }
}
