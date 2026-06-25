<?php

declare(strict_types=1);

namespace ConacytaCore\Chatbot;

abstract class AbstractAiClient
{
    protected const HISTORY_TTL = 1800;
    protected const MAX_HISTORY = 20;
    protected const MAX_RETRIES = 2;
    protected const RETRY_DELAY_MS = 1000;

    protected string $apiKey;
    protected string $model;
    protected string $systemPrompt;
    protected ?string $sessionId;

    public function __construct(?string $sessionId = null)
    {
        $this->apiKey       = $this->resolveApiKey();
        $this->model        = $this->resolveModel();
        $this->systemPrompt = (string) get_option('conacyta_core_system_prompt', 'Eres el asistente virtual oficial del XVII CONACYTA 2026. Usa solo datos de herramientas, no inventes fechas ni numeros. Incluye OBLIGATORIAMENTE 2 sugerencias al final en formato: ---SUGERENCIAS---');
        $this->sessionId    = $sessionId;
    }

    abstract public function sendMessage(string $message, array $history = []): array;

    abstract protected function resolveApiKey(): string;

    abstract protected function resolveModel(): string;

    abstract protected function getLogPrefix(): string;

    abstract protected function buildApiUrl(): string;

    abstract protected function buildRequestHeaders(): array;

    abstract protected function extractTextFromResponse(array $data): string;

    protected function apiCall(array $body, int $attempt = 0): array
    {
        $response = wp_remote_post($this->buildApiUrl(), [
            'headers' => $this->buildRequestHeaders(),
            'body'    => wp_json_encode($body),
            'timeout' => 45,
        ]);

        if (is_wp_error($response)) {
            $error_msg = $response->get_error_message();
            if ($attempt < self::MAX_RETRIES && $this->isRetryable($error_msg)) {
                usleep(self::RETRY_DELAY_MS * 1000 * ($attempt + 1));
                return $this->apiCall($body, $attempt + 1);
            }
            error_log($this->getLogPrefix() . ' wp_remote_post error: ' . $error_msg);
            return ['error' => 'Servicio no disponible.'];
        }

        $status = wp_remote_retrieve_response_code($response);
        if ($status >= 500 && $attempt < self::MAX_RETRIES) {
            usleep(self::RETRY_DELAY_MS * 1000 * ($attempt + 1));
            return $this->apiCall($body, $attempt + 1);
        }

        $raw_body = wp_remote_retrieve_body($response);
        $data = json_decode($raw_body, true);

        if (!is_array($data)) {
            error_log($this->getLogPrefix() . ' Respuesta inesperada: ' . substr($raw_body, 0, 200));
            return ['error' => 'Servicio no disponible.'];
        }

        if (isset($data['error'])) {
            error_log($this->getLogPrefix() . ' Error de API: ' . ($data['error']['message'] ?? 'desconocido'));
            return ['error' => 'Servicio no disponible.'];
        }

        return $data;
    }

    protected function isRetryable(string $error): bool
    {
        $retryable = ['timed out', 'timeout', 'connection refused', 'Connection refused', 'curl error', 'cURL error', 'name lookup timed out', "couldn't connect"];
        foreach ($retryable as $pattern) {
            if (stripos($error, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }

    protected function saveToHistory(string $userMsg, string $botReply): void
    {
        if ($this->sessionId === null) {
            return;
        }
        $key = 'conacyta_chat_hist_' . $this->sessionId;
        $history = get_transient($key) ?: [];
        if (!is_array($history)) {
            $history = [];
        }
        $history[] = ['role' => 'user', 'text' => $userMsg];
        if ($botReply !== '') {
            $history[] = ['role' => 'bot', 'text' => $botReply];
        }
        if (count($history) > self::MAX_HISTORY) {
            $history = array_slice($history, -self::MAX_HISTORY);
        }
        set_transient($key, $history, self::HISTORY_TTL);
    }

    public function getHistory(): array
    {
        if ($this->sessionId === null) {
            return [];
        }
        return get_transient('conacyta_chat_hist_' . $this->sessionId) ?: [];
    }

    protected function extractSuggestions(string &$text): array
    {
        $suggestions = [];
        if (preg_match('/---SUGERENCIAS---\s*\n(.*)/s', $text, $m)) {
            $text = trim(preg_replace('/\s*---SUGERENCIAS---.*$/s', '', $text));
            $lines = array_map('trim', explode("\n", trim($m[1])));
            $suggestions = array_values(array_slice(array_filter($lines, fn ($l) => $l !== ''), 0, 5));
        }
        return $suggestions;
    }
}
