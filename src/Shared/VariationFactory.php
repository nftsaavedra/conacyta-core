<?php

declare(strict_types=1);

namespace ConacytaCore\Shared;

final class VariationFactory
{
    /**
     * Crea una variación de core/query para un CPT.
     *
     * @param string $name        Namespace de la variación (ej. 'conacyta/ponentes-grid').
     * @param string $title       Título visible en el inserter.
     * @param string $description Descripción.
     * @param string $icon        Icono dashicon.
     * @param string $postType    Slug del CPT.
     * @param int    $perPage     Posts por página.
     * @param string $order       ASC o DESC.
     * @param string $orderBy     Campo de orden.
     */
    public static function make(
        string $name,
        string $title,
        string $description,
        string $icon,
        string $postType,
        int $perPage = 10,
        string $order = 'ASC',
        string $orderBy = 'date'
    ): array {
        return [[
            'name'        => $name,
            'title'       => $title,
            'description' => $description,
            'icon'        => $icon,
            'isActive'    => ['namespace'],
            'attributes'  => [
                'namespace' => $name,
                'query'     => [
                    'postType' => $postType,
                    'perPage'  => $perPage,
                    'orderBy'  => $orderBy,
                    'order'    => $order,
                ],
            ],
            'scope'       => ['inserter'],
        ]];
    }
}
