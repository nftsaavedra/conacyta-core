<?php

declare(strict_types=1);

namespace ConacytaCore\Agenda;

final class AgendaMigrator
{
    private const FLAG_OPTION = 'conacyta_core_agenda_migrated_v2';

    public function register(): void
    {
        add_action('init', [$this, 'maybeMigrate'], 20);
    }

    public function maybeMigrate(): void
    {
        if (get_option(self::FLAG_OPTION, false)) {
            return;
        }

        if (!post_type_exists('agenda_item')
            || !taxonomy_exists(AuditorioTaxonomy::TAXONOMY)
            || !taxonomy_exists(AgendaTipoTaxonomy::TAXONOMY)
        ) {
            return;
        }

        $this->migrateAuditorio();
        $this->migrateTipo();

        update_option(self::FLAG_OPTION, true);
    }

    private function migrateAuditorio(): void
    {
        $posts = get_posts([
            'post_type'      => 'agenda_item',
            'post_status'    => ['publish', 'draft', 'pending'],
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => [
                [
                    'key'     => 'conacyta_core_agenda_auditorio',
                    'value'   => '',
                    'compare' => '!=',
                ],
            ],
        ]);

        foreach ($posts as $postId) {
            $value = (string) get_post_meta($postId, 'conacyta_core_agenda_auditorio', true);
            if ('' === $value) {
                continue;
            }

            $slug = sanitize_title($value);
            if (!term_exists($slug, AuditorioTaxonomy::TAXONOMY)) {
                wp_insert_term($value, AuditorioTaxonomy::TAXONOMY, ['slug' => $slug]);
            }

            $term = get_term_by('slug', $slug, AuditorioTaxonomy::TAXONOMY);
            if ($term instanceof \WP_Term) {
                wp_set_object_terms($postId, $term->term_id, AuditorioTaxonomy::TAXONOMY, true);
            }
        }
    }

    private function migrateTipo(): void
    {
        $posts = get_posts([
            'post_type'      => 'agenda_item',
            'post_status'    => ['publish', 'draft', 'pending'],
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => [
                [
                    'key'     => 'conacyta_core_agenda_tipo',
                    'value'   => '',
                    'compare' => '!=',
                ],
            ],
        ]);

        foreach ($posts as $postId) {
            $value = (string) get_post_meta($postId, 'conacyta_core_agenda_tipo', true);
            if ('' === $value) {
                continue;
            }

            $slug = sanitize_title($value);
            if (!term_exists($slug, AgendaTipoTaxonomy::TAXONOMY)) {
                wp_insert_term($value, AgendaTipoTaxonomy::TAXONOMY, ['slug' => $slug]);
            }

            $term = get_term_by('slug', $slug, AgendaTipoTaxonomy::TAXONOMY);
            if ($term instanceof \WP_Term) {
                wp_set_object_terms($postId, $term->term_id, AgendaTipoTaxonomy::TAXONOMY, true);
            }
        }
    }
}