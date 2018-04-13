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

use AHS\Ninjs\Serializer\Normalizer\IgnoreNullValuesNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use App\Serializer\SerializerInterface as NinjsSerializerInterface;

class NinjsSerializer implements NinjsSerializerInterface
{
    /**
     * {@inheritdoc}
     */
    public function serialize($data, $format, array $context = [])
    {
        return $this->getSerializer()->serialize($data, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function deserialize($data, $type, $format, array $context = [])
    {
        return $this->getSerializer()->deserialize($data, $type, $format, $context);
    }

    /**
     * @return SerializerInterface
     */
    protected function getSerializer(): SerializerInterface
    {
        $normalizer = new IgnoreNullValuesNormalizer(null, new CamelCaseToSnakeCaseNameConverter());
        $normalizer->setIgnoredAttributes(['items']);

        return new Serializer([$normalizer], [new JsonEncoder()]);
    }
}
