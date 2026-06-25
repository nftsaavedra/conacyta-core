<?php

declare(strict_types=1);

namespace ConacytaCore\Core;

final class Activator
{
    public static function activate(): void
    {
        Plugin::getInstance()->registerPostTypesAndTaxonomies();
        flush_rewrite_rules();
    }
}
