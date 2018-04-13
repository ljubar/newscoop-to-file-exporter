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

namespace App\Publisher;

use App\Entity\ContentInterface;

interface PublisherInterface
{
    /**
     * @param ContentInterface $content
     * @param bool             $printRenderedTemplate
     */
    public function publish(ContentInterface $content, $printRenderedTemplate = false): void;
}
