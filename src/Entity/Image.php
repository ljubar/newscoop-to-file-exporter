<?php
/*
 * Copyright (C) Paweł Mikołajczuk Creative Apps - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Paweł Mikołajczuk <pawel@mikolajczuk.in>, 2017.
 */
declare(strict_types=1);

namespace App\Entity;

/**
 * Class Image.
 */
class Image extends Content implements ImageInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $location;

    /**
     * @var string
     */
    protected $basename;

    /**
     * @var string
     */
    protected $thumbnailPath;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $width;

    /**
     * @var string
     */
    protected $height;

    /**
     * @var string
     */
    protected $photographer;

    /**
     * @var string
     */
    protected $photographerUrl;

    /**
     * @var string
     */
    protected $place;

    /**
     * @var string
     */
    private $domain;

    /**
     * {@inheritdoc}
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    /**
     * {@inheritdoc}
     */
    public function getBasename(): string
    {
        return $this->basename;
    }

    /**
     * {@inheritdoc}
     */
    public function setBasename(string $basename): void
    {
        $this->basename = $basename;
    }

    /**
     * {@inheritdoc}
     */
    public function getThumbnailPath(): ?string
    {
        return $this->thumbnailPath;
    }

    /**
     * {@inheritdoc}
     */
    public function setThumbnailPath(string $thumbnailPath): void
    {
        $this->thumbnailPath = $thumbnailPath;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidth(): ?string
    {
        return $this->width;
    }

    /**
     * {@inheritdoc}
     */
    public function setWidth(string $width): void
    {
        $this->width = $width;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeight(): ?string
    {
        return $this->height;
    }

    /**
     * {@inheritdoc}
     */
    public function setHeight(string $height): void
    {
        $this->height = $height;
    }

    /**
     * {@inheritdoc}
     */
    public function getPhotographer(): ?string
    {
        return $this->photographer;
    }

    /**
     * {@inheritdoc}
     */
    public function setPhotographer(string $photographer): void
    {
        $this->photographer = $photographer;
    }

    /**
     * {@inheritdoc}
     */
    public function getPhotographerUrl(): ?string
    {
        return $this->photographerUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function setPhotographerUrl(string $photographerUrl): void
    {
        $this->photographerUrl = $photographerUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getPlace(): string
    {
        return $this->place;
    }

    /**
     * {@inheritdoc}
     */
    public function setPlace(string $place): void
    {
        $this->place = $place;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     */
    public function setDomain(string $domain): void
    {
        $this->domain = $domain;
    }

    /**
     * {@inheritdoc}
     */
    public function getOutputFileLocation(): string
    {
        return str_replace('https://', '', $this->domain).'/images/';
    }

    /**
     * {@inheritdoc}
     */
    public function getOutputFileName(): string
    {
        return $this->getId().'.json';
    }
}
