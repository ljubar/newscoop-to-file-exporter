<?php
/*
 * Copyright (C) Paweł Mikołajczuk Creative Apps - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Paweł Mikołajczuk <pawel@mikolajczuk.in>, 2017.
 */

declare(strict_types=1);

namespace App\Entity;

interface ImageInterface
{
    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @param int $id
     */
    public function setId(int $id): void;

    /**
     * @return string
     */
    public function getLocation(): string;

    /**
     * @param string $location
     */
    public function setLocation(string $location): void;

    /**
     * @return string
     */
    public function getBasename(): string;

    /**
     * @param string $basename
     */
    public function setBasename(string $basename): void;

    /**
     * @return string
     */
    public function getThumbnailPath(): ?string;

    /**
     * @param string $thumbnailPath
     */
    public function setThumbnailPath(string $thumbnailPath): void;

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @param string $description
     */
    public function setDescription(string $description): void;

    /**
     * @return string
     */
    public function getWidth(): ?string;

    /**
     * @param string $width
     */
    public function setWidth(string $width): void;

    /**
     * @return string
     */
    public function getHeight(): ?string;

    /**
     * @param string $height
     */
    public function setHeight(string $height): void;

    /**
     * @return string
     */
    public function getPhotographer(): ?string;

    /**
     * @param string $photographer
     */
    public function setPhotographer(string $photographer): void;

    /**
     * @return string
     */
    public function getPhotographerUrl(): ?string;

    /**
     * @param string $photographerUrl
     */
    public function setPhotographerUrl(string $photographerUrl): void;

    /**
     * @return string
     */
    public function getPlace(): string;

    /**
     * @param string $place
     */
    public function setPlace(string $place): void;

    /**
     * @return string
     */
    public function getDomain(): string;

    /**
     * @param string $domain
     */
    public function setDomain(string $domain): void;
}
