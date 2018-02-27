<?php
/*
 * Copyright (C) Paweł Mikołajczuk Creative Apps - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Paweł Mikołajczuk <pawel@mikolajczuk.in>, 2017.
 */
declare(strict_types=1);

namespace App\Factory;

use App\Entity\ArticleInterface;
use AHS\Ninjs\Superdesk\Item;

interface FactoryInterface
{
    /**
     * @param ArticleInterface $article
     *
     * @return Item
     */
    public function create(ArticleInterface $article): Item;

    /**
     * @param ArticleInterface $article
     *
     * @return Item
     */
    public function createArticle(ArticleInterface $article): Item;

    /**
     * @param ArticleInterface $article
     *
     * @return Item|null
     */
    public function createMedia(ArticleInterface $article): ?Item;

    /**
     * @return array
     */
    public function getRenditionNames(): array;

    /**
     * @param ArticleInterface $article
     *
     * @return string
     */
    public function getDescription(ArticleInterface $article): string;
}
