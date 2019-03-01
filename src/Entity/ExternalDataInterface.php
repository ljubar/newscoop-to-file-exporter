<?php
/*
 * Copyright (C) Paweł Mikołajczuk Creative Apps - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Paweł Mikołajczuk <pawel@mikolajczuk.in>, 2017.
 */

declare(strict_types=1);

namespace App\Entity;

use AHS\Content\ContentInterface;

interface ExternalDataInterface extends ContentInterface
{
    public function getExternalData(): array;

    public function setExternalData(array $externalData): void;

    public function getPublisherUrl(): string;

    public function setPublisherUrl(string $publisherUrl): void;

    public function getPublisherSecret(): ?string;

    public function setPublisherSecret(?string $publisherSecret): void;
}