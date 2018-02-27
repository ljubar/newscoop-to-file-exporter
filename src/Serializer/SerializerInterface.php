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

use Symfony\Component\Serializer\SerializerInterface as BaseSerializerInterface;

interface SerializerInterface extends BaseSerializerInterface
{
    /**
     * @param mixed  $data
     * @param string $format
     * @param array  $context
     *
     * @return string
     */
    public function serialize($data, $format, array $context = array());

    /**
     * @param mixed  $data
     * @param string $type
     * @param string $format
     * @param array  $context
     *
     * @return object
     */
    public function deserialize($data, $type, $format, array $context = array());
}
