<?php

declare(strict_types=1);

namespace ConacytaCore\AreaTematica;

final class AreaTematicaQueryFilter
{
    public function register(): void
    {
        add_filter('query_loop_block_query_vars', [$this, 'filter'], 10, 2);
    }

    public function filter(array $query, \WP_Block $block): array
    {
        $namespace = $block->parsed_block['attrs']['namespace'] ?? '';

        if ('conacyta/ponentes-grid' !== $namespace) {
            return $query;
        }

        if (is_tax('area_tematica')) {
            $term = get_queried_object();

            if ($term instanceof \WP_Term && 'area_tematica' === $term->taxonomy) {
                if (!isset($query['tax_query'])) {
                    $query['tax_query'] = [];
                }

                $query['tax_query'][] = [
                    'taxonomy' => 'area_tematica',
                    'field'    => 'term_id',
                    'terms'    => $term->term_id,
                ];
            }
        }

        return $query;
    }
}
