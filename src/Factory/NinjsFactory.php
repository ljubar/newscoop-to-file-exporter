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
use AHS\Ninjs\Superdesk\Service;
use App\Entity\ArticleInterface;
use AHS\Ninjs\Superdesk\Author;
use AHS\Ninjs\Superdesk\Item;
use AHS\Ninjs\Superdesk\Rendition;
use App\Entity\ContentInterface;
use App\Entity\ImageInterface;
use Behat\Transliterator\Transliterator;
use Doctrine\ORM\EntityManagerInterface;
use Hoa\Mime\Mime;
use App\Entity\Rendition as ArticleRendition;

class NinjsFactory implements FactoryInterface
{
    /**
     * @var string
     */
    protected $publicDirPath;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * NinjsFactory constructor.
     *
     * @param string                 $publicDirPath
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(string $publicDirPath, EntityManagerInterface $entityManager)
    {
        $this->publicDirPath = $publicDirPath;
        $this->entityManager = $entityManager;
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
        $item->setVersioncreated($article->getPublishedAt());
        $item->setUrgency(5);
        $item->setPriority(5);
        $item->setPubstatus('usable');
        $item->setLanguage('pt');

        $this->setAuthor($article, $item);
        $this->setCategory($article, $item);
        $this->setExtra($article, $item);
        $this->removeStylingFromBody($article);

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
        $caption = $rendition->getDetails()['caption'];
        if ('' === $caption) {
            $caption = $this->getDescription($article);
        }
        $imageItem->setDescriptionHtml($caption);
        $imageItem->setDescriptionText(strip_tags($caption));
        $imageItem->setVersion('1');
        $this->setAuthor($article, $imageItem);
        $imageItem->setUrgency(5);
        $imageItem->setPriority(5);
        $imageItem->setLanguage($article->getLanguage());
        $imageItem->setUsageterms('indefinite-usage');
        $imageItem->setPubstatus('usable');
        $imageItem->setVersioncreated($article->getPublishedAt());

        $renditions = new Renditions();
        $originalRendition = new Rendition($externalUrl);
        $originalRendition->setMimetype(Mime::getMimeFromExtension($extension));
        $originalRendition->setWidth($width);
        $originalRendition->setHeight($height);
        $originalRendition->setMedia($imageFileName);
        $renditions->add('original', $originalRendition);
        $renditions->add('baseImage', $originalRendition);

        $imageItem->setRenditions($renditions);

        return $imageItem;
    }

    public function createImageItem(ImageInterface $image)
    {
        $imageItem = new Item($image->getDomain().'/images/'.$image->getBasename());
        $extension = pathinfo($imageItem->getGuid(), PATHINFO_EXTENSION);
        $mimeType = Mime::getMimeFromExtension($extension);
        if (null === $mimeType || null === $image->getWidth() || null === $image->getHeight()) {
            return;
        }
        $imageItem->setType('picture');
        $imageItem->setHeadline($image->getDescription() ? strip_tags($image->getDescription()) : 'Image #'.$image->getId());
        $imageItem->setDescriptionHtml($image->getDescription());
        $imageItem->setDescriptionText(strip_tags($image->getDescription()));
        $imageItem->setVersion('1');
        if ($image->getPhotographer()) {
            $author = new Author();
            $author->setName($image->getPhotographer());
            $author->setRole('photographer');
            $imageItem->setByline($author->getName());
            $imageItem->addAuthor($author);
        }
        $imageItem->setUrgency(5);
        $imageItem->setPriority(5);
        $imageItem->setLanguage('en');
        $imageItem->setUsageterms('indefinite-usage');
        $imageItem->setPubstatus('usable');
        $imageItem->setMimeType($mimeType);
        $imageItem->setVersioncreated(new \DateTime());

        $renditions = new Renditions();
        $originalRendition = new Rendition($imageItem->getGuid());
        $originalRendition->setMimetype($mimeType);
        $originalRendition->setWidth((int) $image->getWidth());
        $originalRendition->setHeight((int) $image->getHeight());
        $originalRendition->setMedia($image->getBasename());
        $renditions->add('original', $originalRendition);
        $renditions->add('baseImage', $originalRendition);
        $imageItem->setRenditions($renditions);

        return $imageItem;
    }

    /**
     * {@inheritdoc}
     */
    public function getRenditionNames(): array
    {
        return ['article_small_image'];
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(ArticleInterface $article): string
    {
        if (array_key_exists('deck', $article->getFields())) {
            return $article->getFields()['deck'];
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function setCategory(ArticleInterface $article, Item $item): void
    {
        $issueName = $article->getIssue()['title'];
        $sectionName = $article->getSection()['title'];

        $item->addService(new Service($issueName, $sectionName));
    }

    /**
     * {@inheritdoc}
     */
    public function setExtra(ArticleInterface $article, Item $item): void
    {
        // not implemented by default
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function isSupported(ContentInterface $content): bool
    {
        return true;
    }

    protected function removeStylingFromBody(ArticleInterface $article)
    {
        $output = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $article->getBody());
        $article->setBody($output);
    }

    /**
     * @param ArticleInterface      $article
     * @param Item                  $item
     * @param ArticleRendition|null $rendition
     */
    protected function setAuthor(ArticleInterface $article, Item $item, ArticleRendition $rendition = null)
    {
        if (null !== $rendition) {
            $author = new Author();
            $author->setName($rendition->getDetails()['photographer']);
            $author->setRole('photographer');
            $item->setByline($author->getName());
            $item->addAuthor($author);
        }

        $articleAuthors = $article->getAuthors();

        if (null === $articleAuthors) {
            $item->setByline('editoria');

            return;
        }

        $bloggers = [
            '5' => 'Leonardo Attuch',
            '10' => 'Paulo Moreira Leite',
            '20' => 'Tereza Cruvinel',
            '30' => 'Breno Altman',
            '40' => 'Hélio Doyle',
            '50' => 'Luiz Moreira Júnior',
            '60' => 'Alex Solnik',
            '70' => 'Plínio Zúnica',
            '80' => 'Emir Sader',
            '90' => 'Carlos Lindenberg',
            '91' => 'Mauro Lopes',
            '110' => 'Lúcia Helena Issa',
        ];

        $byline = [];
        foreach ($articleAuthors as $articleAuthor) {
            $author = new Author();
            $author->setName($articleAuthor['name']);
            if (isset($articleAuthor['biography'])) {
                $author->setBiography($articleAuthor['biography']);
            }
            if (isset($articleAuthor['image'])) {
                $author->setAvatarUrl($articleAuthor['image']);
            }
            if (\in_array($articleAuthor['name'], $bloggers, true)) {
                $author->setRole('blogger');
            } else {
                $author->setRole('editor');
            }
            $byline[] = $articleAuthor['name'];
            $item->addAuthor($author);
        }
        $item->setByline(implode(', ', $byline));
    }
}
