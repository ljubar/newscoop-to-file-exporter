<?php

declare(strict_types=1);

namespace App\Importer;

use AHS\Content\ContentInterface;
use AHS\LoggerAwareInterface;

interface ImporterInterface extends LoggerAwareInterface
{
    public function import(string $domain, int $articleNumber, bool $forceImageDownload = false): ContentInterface;
}
