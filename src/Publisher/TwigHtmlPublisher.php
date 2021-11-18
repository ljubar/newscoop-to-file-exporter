<?php

declare(strict_types=1);

namespace App\Publisher;

use AHS\Content\ArticleInterface;
use AHS\Content\ContentInterface;
use AHS\Publisher\AbstractPublisher;
use AHS\Publisher\PublisherInterface;
use Psr\Log\LogLevel;
use Twig\Environment;

use function Safe\preg_replace;

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

    public function __construct(Environment $twig, string $projectDir)
    {
        $this->twig = $twig;
        $this->projectDir = $projectDir;
    }

    public function publish(ContentInterface $article, $printRenderedTemplate = false): ?string
    {
        if (!$article instanceof ArticleInterface) {
            return null;
        }
        $this->log(LogLevel::INFO, 'Rendering article '.$article->getIdentifier());
        $content = $this->twig->render('article.html.twig', ['article' => $article]);

        if ($printRenderedTemplate) {
            $this->log(LogLevel::INFO, $content);
        }

        $urlParts = explode('/', $article->getUrl());
        $fileName = $urlParts[count($urlParts) - 1];
        unset($urlParts[count($urlParts) - 1]);
        $path = $this->projectDir.'/public/articles/'.preg_replace('(^https?://)', '', implode('/', $urlParts));

        $this->saveContentToFile($fileName, $path, $content);
        return $content;
    }
}
