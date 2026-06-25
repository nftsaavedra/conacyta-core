<?php

declare(strict_types=1);

namespace ConacytaCore\Agenda;

use ConacytaCore\Agenda\AuditorioTaxonomy;
use ConacytaCore\Agenda\AgendaTipoTaxonomy;
use ConacytaCore\Shared\EventDateHelper;
use WP_Block;
use WP_Query;

final class AgendaQueryFilter
{
    public function register(): void
    {
        add_filter('query_loop_block_query_vars', [$this, 'filter'], 10, 2);
    }

    /**
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    public function filter(array $query, WP_Block $block): array
    {
        $namespace = $block->parsed_block['attrs']['namespace'] ?? '';

        $dia = $this->extractDia($namespace);

        if (null !== $dia) {
            $query = $this->addDiaFilter($query, $dia);
        }

        $auditorio = $this->extractAuditorioFromQuery($query);
        if (null !== $auditorio) {
            $query = $this->addAuditorioFilter($query, $auditorio);
        }

        $tipo = $this->extractTipoFromQuery($query);
        if (null !== $tipo) {
            $query = $this->addTipoFilter($query, $tipo);
        }

        $ponente = $this->extractPonenteFromQuery($query);
        if (null !== $ponente) {
            $query = $this->addPonenteFilter($query, $ponente);
        }

        return $query;
    }

    /**
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    private function addDiaFilter(array $query, int $dia): array
    {
        $maxDia = EventDateHelper::getTotalDias();
        if ($dia < 1 || $dia > $maxDia) {
            $dia = max(1, min($dia, $maxDia));
        }

        $query['meta_query'] = $query['meta_query'] ?? [];
        $query['meta_query'][] = [
            'key'     => 'conacyta_core_agenda_dia',
            'value'   => $dia,
            'type'    => 'NUMERIC',
            'compare' => '=',
        ];

        return $query;
    }

    /**
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    private function addAuditorioFilter(array $query, string $auditorio): array
    {
        $term = get_term_by('slug', sanitize_title($auditorio), AuditorioTaxonomy::TAXONOMY);
        if (!$term instanceof \WP_Term) {
            $term = get_term_by('name', $auditorio, AuditorioTaxonomy::TAXONOMY);
        }

        if (!$term instanceof \WP_Term) {
            return $query;
        }

        $query['tax_query'] = $query['tax_query'] ?? [];
        $query['tax_query'][] = [
            'taxonomy' => AuditorioTaxonomy::TAXONOMY,
            'field'    => 'term_id',
            'terms'    => $term->term_id,
        ];

        return $query;
    }

    /**
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    private function addTipoFilter(array $query, string $tipo): array
    {
        $term = get_term_by('slug', sanitize_title($tipo), AgendaTipoTaxonomy::TAXONOMY);
        if (!$term instanceof \WP_Term) {
            $term = get_term_by('name', $tipo, AgendaTipoTaxonomy::TAXONOMY);
        }

        if (!$term instanceof \WP_Term) {
            return $query;
        }

        $query['tax_query'] = $query['tax_query'] ?? [];
        $query['tax_query'][] = [
            'taxonomy' => AgendaTipoTaxonomy::TAXONOMY,
            'field'    => 'term_id',
            'terms'    => $term->term_id,
        ];

        return $query;
    }

    /**
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    private function addPonenteFilter(array $query, int $ponenteId): array
    {
        $query['meta_query'] = $query['meta_query'] ?? [];
        $query['meta_query'][] = [
            'key'     => 'conacyta_core_agenda_ponente_id',
            'value'   => $ponenteId,
            'type'    => 'NUMERIC',
            'compare' => '=',
        ];

        return $query;
    }

    private function extractDia(string $namespace): ?int
    {
        $prefix = AgendaVariation::NS_DIA_PREFIX;
        if (strpos($namespace, $prefix) !== 0) {
            return null;
        }

        $suffix = substr($namespace, strlen($prefix));
        if (preg_match('/^(\d+)$/', $suffix, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * @param array<string, mixed> $query
     */
    private function extractAuditorioFromQuery(array $query): ?string
    {
        $value = $this->extractCustom($query, 'auditorio');
        if (!is_string($value) || '' === $value) {
            return null;
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $query
     */
    private function extractTipoFromQuery(array $query): ?string
    {
        $value = $this->extractCustom($query, 'tipo');
        if (!is_string($value) || '' === $value) {
            return null;
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $query
     */
    private function extractPonenteFromQuery(array $query): ?int
    {
        $value = $this->extractCustom($query, 'ponente_id');
        if (null === $value) {
            return null;
        }

        return (int) $value;
    }

    /**
     * Extrae un parametro custom: el theme puede setear query['conacyta_agenda_auditorio'] por variation.
     *
     * @param array<string, mixed> $query
     */
    private function extractCustom(array $query, string $key): mixed
    {
        $candidates = [
            'conacyta_agenda_' . $key,
            'agenda_' . $key,
            $key,
        ];

        foreach ($candidates as $candidate) {
            if (isset($query[$candidate])) {
                return $query[$candidate];
            }
        }

        return null;
    }
}