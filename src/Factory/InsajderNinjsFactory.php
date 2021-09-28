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
        3 => [
            'name' => 'blog',
            'code' => 'blog',
        ],
        5 => [
            'name' => 'blog',
            'code' => 'blog',
        ],
        6 => [
            'name' => 'Destinos',
            'code' => 'dest',
        ],
        7 => [
            'name' => 'Saude 247',
            'code' => 'Sau',
        ],
        9 => [
            'name' => 'Digiclub',
            'code' => 'Dig',
        ],
        10 => [
            'sections' => [
                1 => [
                    'name' => 'Poder',
                    'code' => 'Pod',
                ],
                5 => [
                    'name' => 'Brasil',
                    'code' => 'Br',
                ],
                10 => [
                    'name' => 'blog',
                    'code' => 'blog',
                ],
                20 => [
                    'name' => 'Mundo',
                    'code' => 'M',
                ],
                30 => [
                    'name' => 'Economia',
                    'code' => 'Ec',
                ],
                34 => [
                    'name' => 'Empreender',
                    'code' => 'Emp',
                ],
                35 => [
                    'name' => 'Seu Dinheiro',
                    'code' => 'SeD',
                ],
                45 => [
                    'name' => 'Últimas notícias',
                    'code' => 'UN',
                ],
                70 => [
                    'name' => 'Cultura',
                    'code' => 'Cult',
                ],
                80 => [
                    'name' => 'Midia',
                    'code' => 'Mid',
                ],
                100 => [
                    'name' => 'Esporte',
                    'code' => 'esp',
                ],
                101 => [
                    'name' => 'Esporte',
                    'code' => 'esp',
                ],
                110 => [
                    'name' => 'Oásis',
                    'code' => 'O',
                ],
                117 => [
                    'name' => 'Bahia 247',
                    'code' => 'Bah247',
                ],
                118 => [
                    'name' => 'Alagoas 247',
                    'code' => 'Ala247',
                ],
                120 => [
                    'name' => 'Brasilia 247',
                    'code' => 'Bra247',
                ],
                123 => [
                    'name' => 'Ceara 247',
                    'code' => 'Cea247',
                ],
                125 => [
                    'name' => 'Goias 247',
                    'code' => 'Goi247',
                ],
                130 => [
                    'name' => 'SP 247',
                    'code' => 'SP247',
                ],
                135 => [
                    'name' => 'Minas 247',
                    'code' => 'Min247',
                ],
                137 => [
                    'name' => 'Parana 247',
                    'code' => 'Par247',
                ],
                138 => [
                    'name' => 'Piaui 247',
                    'code' => 'Pia247',
                ],
                140 => [
                    'name' => 'Rio 247',
                    'code' => 'Rio247',
                ],
                142 => [
                    'name' => 'Rio Grande do Sul 247',
                    'code' => 'RGS247',
                ],
                143 => [
                    'name' => 'Tocantins 247',
                    'code' => 'Toc247',
                ],
                148 => [
                    'name' => 'Sergipe 247',
                    'code' => 'Ser247',
                ],
                149 => [
                    'name' => 'Maranhao 247',
                    'code' => 'Mar247',
                ],
                150 => [
                    'name' => 'Pernambuco 247',
                    'code' => 'Per247',
                ],
                450 => [
                    'name' => 'Revista Brasil 247',
                    'code' => 'RB',
                ],
                9999 => [
                    'name' => 'Apoio',
                    'code' => 'Apo',
                ],
            ],
        ],
    ];

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

    /**
     * {@inheritdoc}
     */
    public function setExtra(ArticleInterface $article, SuperdeskItem $item, $extra = null): void
    {
        $extra = new Extra();
        if ('revista' === $article->getType()) {
            $extra->add('pdf', $article->getFields()['link_to_pdf']);
        }
        $extra->add('original_published_at', $item->getVersioncreated());
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
        if ('revista' === $article->getType()) {
            $item->addService(new Service('Revista Oasis', 'revO'));

            return;
        }

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

        if ('newswire' === $content->getType()) {
            return false;
        }

        return true;
    }
}
