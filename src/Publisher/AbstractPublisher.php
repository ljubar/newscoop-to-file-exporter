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

use App\LoggerTrait;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractPublisher
{
    use LoggerTrait;

    /**
     * @param string $fileName
     * @param string $path
     * @param string $content
     */
    protected function saveContentToFile(string $fileName, string $path, string $content): void
    {
        $filesystem = new Filesystem();
        $filesystem->mkdir($path);
        file_put_contents($path.'/'.$fileName, $content);
    }
}
