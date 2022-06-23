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
                    'name' => 'Valjevo',
                    'code' => 'valjevo',
                ],
                20 => [
                    'name' => 'Okrug',
                    'code' => 'okrug',
                ],
                30 => [
                    'name' => 'Privreda',
                    'code' => 'privreda',
                ],
                40 => [
                    'name' => 'Sport',
                    'code' => 'sport',
                ],
                50 => [
                    'name' => 'Kultura',
                    'code' => 'kultura',
                ],
                60 => [
                    'name' => 'Život',
                    'code' => 'zivot',
                ],
                70 => [
                    'name' => 'Mi bismo to ovako',
                    'code' => 'mbto',
                ],
                71 => [
                    'name' => 'Izbori 2016',
                    'code' => 'izbori2016',
                ],
                80 => [
                    'name' => 'Dijalog',
                    'code' => 'dijalog',
                ]
            ],
        ],
        3 => [        
            'sections' => [
                31 => [
                    'name' => 'Iz redakcije',
                    'code' => 'izredakcije',
                ],
                35 => [
                    'name' => '3put odjedanput',
                    'code' => '3put',
                ],
                36 => [
                    'name' => 'Zvezdano nebo nad nama...',
                    'code' => 'zvezdano',
                ],
                37 => [
                    'name' => 'Iza kulisa',
                    'code' => 'izakulisa',
                ],
                38 => [
                    'name' => 'Bez kraja',
                    'code' => 'bezkraja',
                ],
                39 => [
                    'name' => 'Nije sve u Valjevu cirkus',
                    'code' => 'cirkus',
                ],
                40 => [
                    'name' => 'Shit happens',
                    'code' => 'shithappens',
                ],
                41 => [
                    'name' => 'Valjevo Holivud via Bolivud',
                    'code' => 'holivud',
                ],
                42 => [
                    'name' => 'Ladan oblog',
                    'code' => 'oblog',
                ],
                43 => [
                    'name' => 'Zlatno doba',
                    'code' => 'zlatnodoba',
                ],
                44 => [
                    'name' => 'Trupni portret',
                    'code' => 'trupniportret',
                ],
                200 => [
                    'name' => 'Ars Vivendi',
                    'code' => 'arsvivendi',
                ],
                210 => [
                    'name' => 'Impuls grada',
                    'code' => 'impulsgrada',
                ],
                215 => [
                    'name' => 'FotoGrad',
                    'code' => 'FotoGrad',
                ],
                220 => [
                    'name' => 'Redakcijski foto-blog',
                    'code' => 'djdj',
                ]
            ]
        ],        
    ];

    /**
     * @return array
     */
    public function getRenditionNames(): array
    {
        return ['article'];
    }

public function createMedia(ArticleInterface $article): ?Item
    {
        $rendition = null;
//dump($article->getRenditions());die;
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


    $externalUrl = urldecode('https://'.$rendition['details']['original']['src']);

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
        if (null !== $featureMedia) {
            $associations = new Associations();
            $associations->add('featuremedia', $this->createMedia($article));
            $item->setAssociations($associations);
        }

        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(ArticleInterface $article): string
    {
        $fields = $article->getFields();

        switch ($article->getType()) {
            case 'bloginfo':
            case 'link':
            case 'dossier':
            case 'poll':   
            case 'news':
            case 'blogpost':                
                return (null !== $fields['deck ']) ? $fields['deck '] : '';
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function setExtra(ArticleInterface $article, SuperdeskItem $item, $extra = null): void
    {
        $extra = new Extra();
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

        $array = ['link', 'bloginfo', 'dossier', 'like_today', 'page', 'poll'];

        if (in_array($content->getType(), $array)) {
            return false;
        }

        return true;
    }
}
