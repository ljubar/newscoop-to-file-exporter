<?php
/*
 * Copyright (C) Paweł Mikołajczuk Creative Apps - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Paweł Mikołajczuk <pawel@mikolajczuk.in>, 2017.
 */
declare(strict_types=1);

namespace App\Factory;

use AHS\Ninjs\Item;
use AHS\Ninjs\Superdesk\Service;
use App\Entity\ArticleInterface;
use AHS\Ninjs\Superdesk\Item as SuperdeskItem;

/**
 * Class Brasil247NinjsFactory.
 */
class Brasil247NinjsFactory extends NinjsFactory
{
    const ISSUES = [
        3 => 'Blogs',
        5 => 'Colunistas',
        6 => 'Destinos 247',
        7 => 'Saude 247',
        9 => 'Digiclub',
        10 => [
            1 => 'Poder',
            5 => 'Brasil',
            10 => 'Colunistas',
            20 => 'Mundo',
            30 => 'Economia',
            34 => 'Emprender',
            35 => 'Seu Dinheiro',
            45 => 'Últimas notícias',
            70 => 'Cultura',
            80 => 'Midia',
            100 => 'Esporte',
            101 => 'Esporte',
            110 => 'Oásis', // pdf type to Revista Oasis
            117 => 'Bahia 247',
            118 => 'Alagoas 247',
            120 => 'Brasilia 247',
            123 => 'Ceara 247',
            125 => 'Goias 247',
            130 => 'SP 247',
            135 => 'Minas 247',
            137 => 'Parana 247',
            138 => 'Piaui 247',
            140 => 'Rio 247',
            142 => 'Rio Grande do Sul 247',
            143 => 'Tocantins 247',
            148 => 'Sergipe 247',
            149 => 'Maranhao 247',
            150 => 'Pernambuco 247',
            450 => 'Revista',
            9999 => 'Apoio',
        ],
    ];

    /**
     * @param ArticleInterface $article
     * @param Item             $item
     */
    protected function setCategory(ArticleInterface $article, SuperdeskItem $item)
    {
        $issueNumber = $article->getIssue()['number'];
        $sectionNumber = $article->getSection()['number'];

        if (array_key_exists($issueNumber, self::ISSUES)) {
            if (is_string(self::ISSUES[$issueNumber])) {
                $category = self::ISSUES[$issueNumber];
                $code = $issueNumber;
            } elseif (is_array(self::ISSUES[$issueNumber])) {
                $category = self::ISSUES[$issueNumber][$sectionNumber];
                $code = $issueNumber.'_'.$sectionNumber;
            }
        }

        if (!isset($category)) {
            return;
        }

        $item->addService(new Service($category, (string) $code));
    }

    /**
     * @return array
     */
    public function getRenditionNames(): array
    {
        return ['topfront', 'front_big'];
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(ArticleInterface $article): string
    {
        $fields = $article->getFields();
        switch ($article->getType()) {
            case 'materia':
            case 'opiniao':
                return (null !== $fields['deck']) ? $fields['deck'] : '';
            case 'revista':
                return (null !== $fields['description']) ? $fields['description'] : '';
        }

        return '';
    }
}
