<?php

declare(strict_types=1);

namespace ConacytaCore\Shared;

final class MetaRegistrar
{
    /**
     * Registra meta keys via register_post_meta() + register_rest_field()
     * con type coercion en get_callback y update_callback.
     *
     * @param string        $postType        Slug del CPT.
     * @param array         $metaMap         [ 'conacyta_core_xxx' => 'boolean|number|integer|string|url', ... ]
     * @param callable|null $extraValidation Callback opcional (int $postId, string $key, mixed $value): mixed — retorna el valor sanitizado extra.
     */
    public static function forPostType(string $postType, array $metaMap, ?callable $extraValidation = null): void
    {
        foreach ($metaMap as $key => $type) {
            $wpType = match ($type) {
                'boolean' => 'boolean',
                'number', 'integer' => 'number',
                default => 'string',
            };

            $sanitizer = match ($type) {
                'boolean' => [Sanitizer::class, 'boolean'],
                'number'  => [Sanitizer::class, 'float'],
                'integer' => [Sanitizer::class, 'integer'],
                'url'     => [Sanitizer::class, 'url'],
                default   => [Sanitizer::class, 'text'],
            };

            register_post_meta($postType, $key, [
                'type'              => $wpType,
                'single'            => true,
                'show_in_rest'      => true,
                'sanitize_callback' => $sanitizer,
                'auth_callback'     => static function (): bool {
                    return current_user_can('edit_posts');
                },
            ]);
        }

        register_rest_field($postType, 'meta', [
            'get_callback' => static function ($post) use ($metaMap): array {
                $result = [];
                foreach ($metaMap as $key => $type) {
                    $raw = get_post_meta($post['id'], $key, true);
                    $result[$key] = match ($type) {
                        'boolean' => (bool) $raw,
                        'number'  => (float) $raw,
                        'integer' => (int) $raw,
                        default   => (string) $raw,
                    };
                }
                return $result;
            },
            'update_callback' => static function ($values, $post) use ($metaMap, $extraValidation): void {
                foreach ($values as $key => $value) {
                    $type = $metaMap[$key] ?? null;
                    if ($type === null) {
                        continue;
                    }
                    $sanitized = match ($type) {
                        'boolean' => is_bool($value)
                            ? $value
                            : in_array($value, ['1', 1, 'true', true], true),
                        'number'  => ($value === null || $value === '') ? 0.0 : (float) $value,
                        'integer' => ($value === null || $value === '') ? 0 : (int) $value,
                        default   => (string) ($value ?? ''),
                    };
                    if ($extraValidation !== null) {
                        $sanitized = $extraValidation($post->ID, $key, $sanitized);
                    }
                    update_post_meta($post->ID, $key, $sanitized);
                }
            },
            'schema' => [
                'type' => 'object',
                'properties' => (static function () use ($metaMap): array {
                    $props = [];
                    foreach ($metaMap as $key => $type) {
                        $propType = match ($type) {
                            'boolean' => 'boolean',
                            'number'  => 'number',
                            'integer' => 'integer',
                            default   => 'string',
                        };
                        $props[$key] = ['type' => $propType];
                    }
                    return $props;
                })(),
            ],
        ]);
    }
}
