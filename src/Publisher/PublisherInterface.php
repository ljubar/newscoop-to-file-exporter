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

use App\Entity\Article;

interface PublisherInterface
{
    /**
     * @param Article $article
     * @param bool    $printRenderedTemplate
     */
    public function publish(Article $article, $printRenderedTemplate = false): void;
}
