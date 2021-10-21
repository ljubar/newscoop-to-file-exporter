<?php
declare(strict_types=1);

namespace App\Factory;

use AHS\Factory\NinjsFactory;
use AHS\Ninjs\Superdesk\Extra;
use AHS\Ninjs\Superdesk\Service;
use AHS\Content\ArticleInterface;
use AHS\Ninjs\Superdesk\Item as SuperdeskItem;
use AHS\Content\ContentInterface;

class InsajderNinjsFactory extends NinjsFactory
{
    const ISSUES = [
        10 => [        
            'sections' => [
                10 => [
                    'name' => 'Tema',
                    'code' => 'tema',
                ],
                12 => [
                    'name' => 'Pitanje dana',
                    'code' => 'pitanjedana',
                ],
                15 => [
                    'name' => 'Najvažnije vesti',
                    'code' => 'najvaznijevesti',
                ],
                23 => [
                    'name' => 'Istraživačke priče',
                    'code' => 'istrazivackeprice',
                ],
                25 => [
                    'name' => '???',
                    'code' => '???',
                ],
                26 => [
                    'name' => 'Podkast',
                    'code' => 'podkast',
                ],
                30 => [
                    'name' => 'Stav redakcije',
                    'code' => 'stav',
                ],
                97 => [
                    'name' => 'Pregled nedelje',
                    'code' => 'preglednedelje',
                ],
                98 => [
                    'name' => 'Insajder debata',
                    'code' => 'debata',
                ],
                99 => [
                    'name' => 'Insajder bez ograničenja',
                    'code' => 'bezogranicenja',
                ],
                413 => [
                    'name' => 'Politika kao biznis, država kao partijski plen',
                    'code' => 'partijskadrzava',
                ],
                412 => [
                    'name' => 'Epidemija tajni',
                    'code' => 'epidemijatajni',
                ],
                414 => [
                    'name' => 'Lokalni šerifi',
                    'code' => 'lokalniserifi',
                ],
                411 => [
                    'name' => 'Insajder: Tačka',
                    'code' => 'mediji',
                ],
                415 => [
                    'name' => 'Dovršen plan',
                    'code' => 'dovrsenplan',
                ],
                410 => [
                    'name' => 'Prodaja pod zavetom ćutanja',
                    'code' => 'prodajagalenika',
                ],
                400 => [
                    'name' => 'Prodaja',
                    'code' => 'prodaja',
                ],
                390 => [
                    'name' => 'Srpsko-arapska posla',
                    'code' => 'srpskoarapskaposla',
                ],
                380 => [
                    'name' => 'Insajder specijal',
                    'code' => 'specijal',
                ],
                370 => [
                    'name' => 'Prvenstvo u prevari',
                    'code' => 'prvenstvouprevari',
                ],
                240 => [
                    'name' => 'Patriotska pljačka, nastavak',
                    'code' => 'patriotskapljackanastavak',
                ],
                140 => [
                    'name' => 'Energetski (ne)sporazum',
                    'code' => 'energetski',
                ],
                200 => [
                    'name' => 'Rudnik dugova',
                    'code' => 'rudnikdugova',
                ],
                210 => [
                    'name' => 'Pravila pljačke',
                    'code' => 'pravilapljacke',
                ],
                100 => [
                    'name' => 'Patriotska pljačka',
                    'code' => 'patriotskapljacka',
                ],
                360 => [
                    'name' => 'Kupoprodaja zdravlja',
                    'code' => 'vakcine',
                ],
                150 => [
                    'name' => 'Prevara veka',
                    'code' => 'prevaraveka',
                ],
                220 => [
                    'name' => 'Insajder o Insajderu',
                    'code' => 'insoins',
                ],
                160 => [
                    'name' => 'Nasilje uz blagoslov',
                    'code' => 'nasilje',
                ],
                230 => [
                    'name' => '(Ne)moć države',
                    'code' => 'nemocdrzave',
                ],
                110 => [
                    'name' => 'Službena (zlo)upotreba',
                    'code' => 'sluzbena',
                ],
                190 => [
                    'name' => 'Pravila igre',
                    'code' => 'pravilaigre',
                ],
                260 => [
                    'name' => 'Mreža - šverc cigareta',
                    'code' => 'sverc',
                ],
                350 => [
                    'name' => 'Paravojna formacija Škorpioni',
                    'code' => 'skorpioni',
                ],
                340 => [
                    'name' => 'Ubistvo u Višnjićevu',
                    'code' => 'visnjicevo',
                ],
                270 => [
                    'name' => 'Ubistvo na Ibarskoj magistrali',
                    'code' => 'ibarska',
                ],
                280 => [
                    'name' => 'Srpska pravda',
                    'code' => 'pravosudje',
                ],
                320 => [
                    'name' => 'Makina grupa',
                    'code' => 'mgrupa',
                ],
                310 => [
                    'name' => 'Intervju - Milan Obradović',
                    'code' => 'mobradovic',
                ],
                300 => [
                    'name' => 'Intervju - Čedomir Jovanović',
                    'code' => 'cjovanovic',
                ],
                330 => [
                    'name' => 'Ubistvo Zorana Đinđića',
                    'code' => 'ubistvopremijera',
                ],
                290 => [
                    'name' => 'Intervju - Vladimir Beba Popović',
                    'code' => 'vbpopovic',
                ],
                130 => [
                    'name' => 'Rukopisi ne gore',
                    'code' => 'rukopisi',
                ],
                250 => [
                    'name' => 'Tragom Ratka Mladića',
                    'code' => 'mladic',
                ],
                555 => [
                    'name' => 'Zloupotrebama do Pančićevog vrha',
                    'code' => 'pancicevvrh',
                ],
                700 => [
                    'name' => 'Izbori 2018',
                    'code' => 'izbori2018',
                ],
                600 => [
                    'name' => 'Državna pljačka države',
                    'code' => 'drzavnapljacka',
                ],
                560 => [
                    'name' => 'Insajder na lokalu',
                    'code' => 'insajderlokal',
                ],
                550 => [
                    'name' => 'Predsednički izbori 2017',
                    'code' => 'predsednicki2017',
                ],
                543 => [
                    'name' => 'Cena kosovske iluzije',
                    'code' => 'kosovo',
                ],
                542 => [
                    'name' => '"Posao veka" koji je propao',
                    'code' => 'juznitok',
                ],
                541 => [
                    'name' => 'Komunalna policija',
                    'code' => 'komunalci',
                ],
                540 => [
                    'name' => 'DIPOS - Rasipnik državnog novca',
                    'code' => 'dipos',
                ],
                530 => [
                    'name' => 'Mediji: Rat za istinu?',
                    'code' => 'ratzaistinu',
                ],
                520 => [
                    'name' => 'Izbeglice na ruti užasa',
                    'code' => 'izbeglice',
                ],
                510 => [
                    'name' => 'Izbori 2016.',
                    'code' => 'izbori2016',
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
            $extra->add('feature_video', $article->getFields()['youtube_shortcode']);
        }
        $extra->add('original_publish_date', $item->getVersioncreated());
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

        $issueNumber = $article->getIssue();
        $sectionNumber = $article->getSection();
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
