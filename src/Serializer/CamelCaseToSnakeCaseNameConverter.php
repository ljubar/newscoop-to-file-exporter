<?php

declare(strict_types=1);

/*
 * This file is part of the NewscoopExporter application.
 *
 * Copyright 2018 Sourcefabric z.ú. and contributors.
 *
 * For the full copyright and license information, please see the
 * AUTHORS and LICENSE files distributed with this source code.
 *
 * @copyright 2018 Sourcefabric z.ú
 * @license http://www.superdesk.org/license
 */

namespace App\Serializer;

use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter as SymfonyCamelCaseToSnakeCaseNameConverter;

class CamelCaseToSnakeCaseNameConverter extends SymfonyCamelCaseToSnakeCaseNameConverter
{
    /**
     * {@inheritdoc}
     */
    public function normalize($propertyName)
    {
        if ('baseImage' === $propertyName) {
            return $propertyName;
        }

        return parent::normalize($propertyName);
    }
}
