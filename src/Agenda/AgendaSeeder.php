<?php

declare(strict_types=1);

namespace ConacytaCore\Agenda;

final class AgendaSeeder
{
    public static function seed(): void
    {
        if (!taxonomy_exists(AuditorioTaxonomy::TAXONOMY)) {
            register_taxonomy(AuditorioTaxonomy::TAXONOMY, ['agenda_item'], ['hierarchical' => true]);
        }

        if (!taxonomy_exists(AgendaTipoTaxonomy::TAXONOMY)) {
            register_taxonomy(AgendaTipoTaxonomy::TAXONOMY, ['agenda_item'], ['hierarchical' => false]);
        }

        self::seedTerms(AuditorioTaxonomy::TAXONOMY, AuditorioTaxonomy::defaultTerms());
        self::seedTerms(AgendaTipoTaxonomy::TAXONOMY, AgendaTipoTaxonomy::defaultTerms());
    }

    /**
     * @param array<string, string> $terms slug => name
     */
    private static function seedTerms(string $taxonomy, array $terms): void
    {
        foreach ($terms as $slug => $name) {
            if (!term_exists($slug, $taxonomy)) {
                wp_insert_term($name, $taxonomy, ['slug' => $slug]);
            }
        }
    }
}