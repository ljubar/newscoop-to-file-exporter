<?php
/*
 * Copyright (C) Paweł Mikołajczuk Creative Apps - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Paweł Mikołajczuk <pawel@mikolajczuk.in>, 2017.
 */
declare(strict_types=1);

namespace App\Entity;

interface ArticleInterface
{
    /**
     * @return mixed
     */
    public function getId();

    /**
     * @param mixed $id
     */
    public function setId($id);

    /**
     * @return mixed
     */
    public function getNumber();

    /**
     * @param mixed $number
     */
    public function setNumber($number);

    /**
     * @return mixed
     */
    public function getCreatedAt();

    /**
     * @param mixed $createdAt
     */
    public function setCreatedAt($createdAt);

    /**
     * @return mixed
     */
    public function getPublishedAt();

    /**
     * @param mixed $publishedAt
     */
    public function setPublishedAt($publishedAt);

    /**
     * @return mixed
     */
    public function getAuthors();

    /**
     * @param mixed $authors
     */
    public function setAuthors(array $authors);

    /**
     * @return mixed
     */
    public function getKeywords();

    /**
     * @param mixed $keywords
     */
    public function setKeywords($keywords);

    /**
     * @return mixed
     */
    public function getTitle();

    /**
     * @param mixed $title
     */
    public function setTitle($title);

    /**
     * @return mixed
     */
    public function getWebcode();

    /**
     * @param mixed $webcode
     */
    public function setWebcode($webcode);

    /**
     * @param array $fields
     */
    public function setFields(array $fields);

    /**
     * @return array
     */
    public function getFields();

    /**
     * @return mixed
     */
    public function getUrl();

    /**
     * @param mixed $url
     */
    public function setUrl($url);

    /**
     * @return mixed
     */
    public function getRenditions();

    /**
     * @param mixed $renditions
     */
    public function setRenditions($renditions);

    /**
     * @param string $caption
     *
     * @return Rendition|null
     */
    public function getRendition(string $caption): ?Rendition;

    /**
     * @return mixed
     */
    public function getLanguage();

    /**
     * @param mixed $language
     */
    public function setLanguage($language);

    /**
     * @return mixed
     */
    public function getIssue();

    /**
     * @param mixed $issue
     */
    public function setIssue($issue);

    /**
     * @return mixed
     */
    public function getSection();

    /**
     * @param mixed $section
     */
    public function setSection($section);

    /**
     * @return mixed
     */
    public function getBody();

    /**
     * @param mixed $body
     */
    public function setBody($body);

    /**
     * @return string
     */
    public function getType(): ?string;

    /**
     * @param string $type
     */
    public function setType(string $type): void;
}
