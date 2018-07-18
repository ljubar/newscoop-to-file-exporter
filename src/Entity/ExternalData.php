<?php
/*
 * Copyright (C) Paweł Mikołajczuk Creative Apps - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Paweł Mikołajczuk <pawel@mikolajczuk.in>, 2017.
 */

namespace App\Entity;

class ExternalData extends Content implements ExternalDataInterface
{
    protected $externalData = [];

    /**
     * @var string
     */
    protected $publisherUrl;

    /**
     * @var null|string
     */
    protected $publisherSecret;

    public function __construct(string $publisherUrl, array $externalData)
    {
        $this->publisherUrl = $publisherUrl;
        $this->externalData = $externalData;
    }

    public function getExternalData(): array
    {
        return $this->externalData;
    }

    public function setExternalData(array $externalData): void
    {
        $this->externalData = $externalData;
    }

    public function getPublisherUrl(): string
    {
        return $this->publisherUrl;
    }

    public function setPublisherUrl(string $publisherUrl): void
    {
        $this->publisherUrl = $publisherUrl;
    }

    public function getIdentifier(): int
    {
        return $this->externalData['number'];
    }

    public function getPublisherSecret(): ?string
    {
        return $this->publisherSecret;
    }

    public function setPublisherSecret(?string $publisherSecret): void
    {
        $this->publisherSecret = $publisherSecret;
    }

    /**
     * {@inheritdoc}
     */
    public function getOutputFileLocation(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getOutputFileName(): string
    {
        return $this->externalData['number'].'.json';
    }
}