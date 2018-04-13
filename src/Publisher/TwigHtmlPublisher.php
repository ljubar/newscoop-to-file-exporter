<?php

declare(strict_types=1);

/*
 * This file is part of the NewscoopExporter application.
 *
 * Copyright 2018 Sourcefabric z.ú. and contributors.
 *
 * For the full copyright and license information, please see the
 * AUTHORS and LICENSE files distributed with this source code.
 *
 * @copyright 2018 Sourcefabric z.ú
 * @license http://www.superdesk.org/license
 */

namespace App\Publisher;

use App\Entity\Article;
use App\Entity\ArticleInterface;
use App\Entity\ContentInterface;
use Psr\Log\LogLevel;
use Twig\Environment;

class TwigHtmlPublisher extends AbstractPublisher implements PublisherInterface
{
    /**
     * @var Environment
     */
    protected $twig;

    /**
     * @var string
     */
    protected $projectDir;

    /**
     * TwigHtmlPublisher constructor.
     *
     * @param Environment $twig
     * @param string      $projectDir
     */
    public function __construct(Environment $twig, string $projectDir)
    {
        $this->twig = $twig;
        $this->projectDir = $projectDir;
    }

    /**
     * {@inheritdoc}
     */
    public function publish(ContentInterface $article, $printRenderedTemplate = false): void
    {
        if (!$article instanceof ArticleInterface) {
            return;
        }
        $this->log(LogLevel::INFO, 'Rendering article '.$article->getIdentifier());
        $content = $this->twig->render('article.html.twig', ['article' => $content]);

        if ($printRenderedTemplate) {
            $this->log(LogLevel::INFO, $content);
        }

        $urlParts = explode('/', $article->getUrl());
        $fileName = $urlParts[count($urlParts) - 1];
        unset($urlParts[count($urlParts) - 1]);
        $path = $this->projectDir.'/public/articles/'.preg_replace('(^https?://)', '', implode('/', $urlParts));

        $this->saveContentToFile($fileName, $path, $content);
    }
}
