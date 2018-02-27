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

namespace App\Importer;

use App\Entity\Article;

interface ImporterInterface
{
    /**
     * @param string $domain
     * @param int    $articleNumber
     * @param bool   $forceImageDownload
     *
     * @return Article
     */
    public function import(string $domain, int $articleNumber, bool $forceImageDownload = false): Article;
}
