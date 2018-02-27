<?php
/*
 * Copyright (C) Paweł Mikołajczuk Creative Apps - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Paweł Mikołajczuk <pawel@mikolajczuk.in>, 2017.
 */
declare(strict_types=1);

namespace App\Factory;

use AHS\Ninjs\Schema\Associations;
use AHS\Ninjs\Schema\Renditions;
use App\Entity\ArticleInterface;
use AHS\Ninjs\Superdesk\Author;
use AHS\Ninjs\Superdesk\Item;
use AHS\Ninjs\Superdesk\Rendition;
use Behat\Transliterator\Transliterator;
use Hoa\Mime\Mime;

class NinjsFactory implements FactoryInterface
{
    /**
     * @var string
     */
    protected $publicDirPath;

    /**
     * NinjsFactory constructor.
     *
     * @param string $publicDirPath
     */
    public function __construct(string $publicDirPath)
    {
        $this->publicDirPath = $publicDirPath;
    }

    /**
     * @param ArticleInterface $article
     *
     * @return Item
     */
    public function create(ArticleInterface $article): Item
    {
        $item = $this->createArticle($article);
        $featureMedia = $this->createMedia($article);
        if (null !== $featureMedia) {
            $associations = new Associations();
            $associations->add('featuremedia', $this->createMedia($article));
            $item->setAssociations($associations);
        }

        return $item;
    }

    /**
     * @param ArticleInterface $article
     *
     * @return Item
     */
    public function createArticle(ArticleInterface $article): Item
    {
        $item = new Item((string) $article->getUrl());
        $item->setDescriptionHtml($this->getDescription($article));
        $item->setDescriptionText(strip_tags($this->getDescription($article)));
        $item->setBodyHtml($article->getBody());
        $item->setBodyText(strip_tags($article->getBody()));
        $item->setVersion('1');
        $item->setHeadline($article->getTitle());
        $item->setSlugline(Transliterator::urlize($article->getTitle()));
        $item->setUrgency(5);
        $item->setPriority(5);
        $item->setPubstatus('usable');
        $item->setLanguage('pt');

        $this->setAuthor($article, $item);
        $this->setCategory($article, $item);

        return $item;
    }

    /**
     * @param ArticleInterface $article
     *
     * @return Item|null
     */
    public function createMedia(ArticleInterface $article): ?Item
    {
        $rendition = null;
        foreach ($this->getRenditionNames() as $renditionName) {
            if (0 === count($article->getRenditions())) {
                break;
            }

            if (null !== $rendition = $article->getRendition($renditionName)) {
                break;
            }
        }

        if (null === $rendition) {
            return null;
        }

        $imagePath = $this->publicDirPath.$rendition->getLink();
        $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
        $imageFileName = pathinfo($imagePath, PATHINFO_BASENAME);
        list($width, $height) = getimagesize($imagePath);
        $externalUrl = $rendition->getDetails()['original']['external_src'];

        $imageItem = new Item($externalUrl);
        $imageItem->setType('picture');
        $imageItem->setHeadline($article->getTitle());
        $imageItem->setVersion('1');
        $this->setAuthor($article, $imageItem);
        $imageItem->setUrgency(5);
        $imageItem->setPriority(5);
        $imageItem->setLanguage($article->getLanguage());
        $imageItem->setUsageterms('indefinite-usage');
        $imageItem->setPubstatus('usable');

        $renditions = new Renditions();
        $originalRendition = new Rendition('http://'.$externalUrl);
        $originalRendition->setMimetype(Mime::getMimeFromExtension($extension));
        $originalRendition->setWidth($width);
        $originalRendition->setHeight($height);
        $originalRendition->setMedia($imageFileName);
        $renditions->add('original', $originalRendition);

        $imageItem->setRenditions($renditions);

        return $imageItem;
    }

    /**
     * @return array
     */
    public function getRenditionNames(): array
    {
        return ['article_small_image'];
    }

    /**
     * @param ArticleInterface $article
     *
     * @return string
     */
    public function getDescription(ArticleInterface $article): string
    {
        if (array_key_exists('deck', $article->getFields())) {
            return $article->getFields()['deck'];
        }

        return '';
    }

    /**
     * @param ArticleInterface $article
     * @param Item             $item
     */
    protected function setCategory(ArticleInterface $article, Item $item)
    {
        // Change it to service
        $item->setLocated($article->getSection()['title']);
    }

    /**
     * @param ArticleInterface $article
     * @param Item             $item
     */
    protected function setAuthor(ArticleInterface $article, Item $item)
    {
        $articleAuthors = $article->getAuthors();

        if (null === $articleAuthors) {
            $item->setByline('editoria');

            return;
        }

        $byline = [];
        foreach ($articleAuthors as $articleAuthor) {
            $author = new Author();
            $author->setName($articleAuthor['name']);
            $byline[] = $articleAuthor['name'];
            $item->addAuthor($author);
        }
        $item->setByline(implode(', ', $byline));
    }
}
