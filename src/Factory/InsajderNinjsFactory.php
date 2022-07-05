<?php
declare(strict_types=1);

namespace App\Factory;
use AHS\Ninjs\Schema\Associations;
use Hoa\Mime\Mime;
use AHS\Ninjs\Schema\Renditions;
use AHS\Ninjs\Superdesk\Item;
use AHS\Factory\NinjsFactory;
use AHS\Ninjs\Superdesk\Extra;
use AHS\Ninjs\Superdesk\Service;
use AHS\Content\ArticleInterface;
use AHS\Ninjs\Superdesk\Item as SuperdeskItem;
use AHS\Content\ContentInterface;
use AHS\Ninjs\Superdesk\Rendition;
class InsajderNinjsFactory extends NinjsFactory
{
    const ISSUES = [
        10 => [        
            'sections' => [
                10 => [
                    'name' => 'In Focus',
                    'code' => 'focus',
                ],
                15 => [
                    'name' => 'News',
                    'code' => 'news',
                ],
                17 => [
                    'name' => 'Positive stories',
                    'code' => 'positive',
                ],
                30 => [
                    'name' => 'We recommend',
                    'code' => 'recommended',
                ],
                50 => [
                    'name' => 'About Insajder',
                    'code' => 'about',
                ],
                99 => [
                    'name' => 'Insajder without limits',
                    'code' => 'bezogranicenja',
                ],
                110 => [
                    'name' => 'Službena (zlo)upotreba',
                    'code' => 'sluzbena',
                ],
                150 => [
                    'name' => 'Prevara veka',
                    'code' => 'prevaraveka',
                ],
                170 => [
                    'name' => 'Službena tajna',
                    'code' => 'sluzbenatajna',
                ],
                190 => [
                    'name' => 'Pravila igre',
                    'code' => 'pravilaigre',
                ],
                222 => [
                    'name' => 'Full stop',
                    'code' => 'tacka',
                ],
                230 => [
                    'name' => '(Ne)moć države',
                    'code' => 'nemocdrzave',
                ],
                405 => [
                    'name' => 'prodajaeng',
                    'code' => 'prodaja',
                ],
                510 => [
                    'name' => 'Elections 2016',
                    'code' => '510',
                ],
                520 => [
                    'name' => 'Refugees on horror route',
                    'code' => 'refugees',
                ],                
                530 => [
                    'name' => 'Media: War for truth',
                    'code' => 'mediawar',
                ],
                540 => [
                    'name' => 'DIPOS, squanderer of state money',
                    'code' => 'DIPOS',
                ],
            ],
        ],
    ];

    /**
     * @return array
     */
    public function getRenditionNames(): array
    {
        return ['fullwidthfront', 'universal'];
    }

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
//dump($rendition);die;
        if (null === $rendition) {
            return null;
        }

        $imagePath = $rendition['link'];
        $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
        $imageFileName = pathinfo($imagePath, PATHINFO_BASENAME);
        //list($width, $height) = getimagesize($imagePath);


    $externalUrl = urldecode('http://'.$rendition['details']['original']['src']);

        $imageItem = new Item($externalUrl);
        $imageItem->setType('picture');
        $imageItem->setHeadline($article->getTitle());
        $caption = $rendition['details']['caption'];

        $imageItem->setDescriptionHtml($caption);
        $imageItem->setDescriptionText(strip_tags($caption));
        $imageItem->setVersion('1');
        $this->setAuthor($article, $imageItem);
        $imageItem->setUrgency(5);
        $imageItem->setPriority(5);
        $imageItem->setLanguage($article->getLanguage());
        $imageItem->setUsageterms('indefinite-usage');
        $imageItem->setPubstatus('usable');
        $imageItem->setVersioncreated(new \DateTime($article->getPublishedAt()));
        $renditions = new Renditions();
        $originalRendition = new Rendition($externalUrl);
        $originalRendition->setMimetype(Mime::getMimeFromExtension($extension) ?? '');
        $originalRendition->setWidth($rendition['details']['width']);
        $originalRendition->setHeight($rendition['details']['height']);
        $originalRendition->setMedia($imageFileName);
        $renditions->add('original', $originalRendition);
        $renditions->add('baseImage', $originalRendition);

        $imageItem->setRenditions($renditions);
        return $imageItem;
    }

    public function create(ArticleInterface $article): Item
    {
        $item = parent::create($article);
        $featureMedia = $this->createMedia($article);
//dump('ffffffffff',$featureMedia);
        if (null !== $featureMedia) {
            $associations = new Associations();
            $associations->add('featuremedia', $this->createMedia($article));
            $item->setAssociations($associations);
        }
dump('ssss',$item->getAssociations());
        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(ArticleInterface $article): string
    {
        $fields = $article->getFields();

        switch ($article->getType()) {
            case 'insajder':
            case 'news':
                return (null !== $fields['lead_article']) ? $fields['lead_article'] : '';
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function setExtra(ArticleInterface $article, SuperdeskItem $item, $extra = null): void
    {
        $extra = new Extra();
        if ('news' === $article->getType()) {
            if (isset($article->getFields()['youtube_shortcode'])) {
            	$extra->add('feature_video', $article->getFields()['youtube_shortcode']);
            }
            if (isset($article->getFields()['text_item'])) {
                $extra->add('itemtype', 'text_item');
            }
            if (isset($article->getFields()['photo_item']) && $article->getFields()['photo_item'] === '1') {
                $extra->add('itemtype', 'photo_item');
            }
            if (isset($article->getFields()['episode_item']) && $article->getFields()['episode_item'] === '1') {
                $extra->add('itemtype', 'episode_item');
            }
            if (isset($article->getFields()['phonecall_item']) && $article->getFields()['phonecall_item'] === '1') {
                $extra->add('itemtype', 'phonecall_item');
            }
            if (isset($article->getFields()['conference_item']) && $article->getFields()['conference_item'] === '1') {
                $extra->add('itemtype', 'conference_item');
            }
            if (isset($article->getFields()['video_item']) && $article->getFields()['video_item'] === '1') {
                $extra->add('itemtype', 'video_item');
            }
            if (isset($article->getFields()['attachment_item']) && $article->getFields()['attachment_item'] === '1') {
                $extra->add('itemtype', 'attachment_item');
            }
            if (isset($article->getFields()['results_item']) && $article->getFields()['results_item'] === '1') {
                $extra->add('itemtype', 'results_item');
            }
            if (isset($article->getFields()['epilog_item']) && $article->getFields()['epilog_item'] === '1') {
                $extra->add('itemtype', 'epilog_item');
            }
        }
        if ('insajder' === $article->getType()) {
            $extra->add('itemtype', 'wrapper_item');
        }        
$extra->add('original_published_at', $item->getVersioncreated());
$extra->add('original_article_url', $item->getGuid());
        $item->setExtra($extra);
    }

    /**
     * @param ArticleInterface $article
     * @param SuperdeskItem    $item
     *
     * @throws \Exception
     */
    public function setCategory(ArticleInterface $article, SuperdeskItem $item): void
    {
        // if ('revista' === $article->getType()) {
        //     $item->addService(new Service('Revista Oasis', 'revO'));

        //     return;
        // }

        /* Above we need 'if article type "insajder", assign content profile 'format' */

        //$issueNumber = $article->getIssue();
        //$sectionNumber = $article->getSection();
                $issueNumber = (string) $article->getIssue()['number'];
        $sectionNumber = (string) $article->getSection()['number'];
        $category = null;
        $code = null;

        if (!is_string($issueNumber)) {
            return;
        }

        if (array_key_exists((int) $issueNumber, self::ISSUES)) {
            if (isset(self::ISSUES[$issueNumber]['name'])) {
                $category = self::ISSUES[$issueNumber]['name'];
                $code = self::ISSUES[$issueNumber]['code'];
            } elseif (isset(self::ISSUES[$issueNumber]['sections'][$sectionNumber]['name'])) {
                $category = self::ISSUES[$issueNumber]['sections'][$sectionNumber]['name'];
                $code = self::ISSUES[$issueNumber]['sections'][$sectionNumber]['code'];
            }
        }

        if (null !== $category && null !== $code) {
            $item->addService(new Service($category, (string)$code));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isSupported(ContentInterface $content): bool
    {
        if (!$content instanceof ArticleInterface) {
            return false;
        }

        $array = ['Newswire', 'page', 'video'];

        if (in_array($content->getType(), $array)) {
            return false;
        }

        return true;
    }
}
