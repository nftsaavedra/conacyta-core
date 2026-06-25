<?php

declare(strict_types=1);

namespace ConacytaCore\Portada;

final class PortadaQueryFilter
{
    public function register(): void
    {
        add_filter('query_loop_block_query_vars', [$this, 'filter'], 10, 2);
    }

    public function filter(array $query, \WP_Block $block): array
    {
        $namespace = $block->parsed_block['attrs']['namespace'] ?? '';
        if ('conacyta/portada-hero' !== $namespace) { return $query; }
        if (!isset($query['meta_query'])) { $query['meta_query'] = []; }
        $query['meta_query'][] = [ 'key' => 'conacyta_core_portada_principal',             'value'   => '1', 'compare' => '=' ];
        return $query;
    }
}