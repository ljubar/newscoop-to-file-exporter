<?php

declare(strict_types=1);

namespace App\Importer;

use AHS\Content\ContentInterface;
use App\Entity\Article;
use AHS\Serializer\SerializerInterface;
use GuzzleHttp\ClientInterface;
use Psr\Log\LogLevel;
use function Safe\json_decode;

class NewscoopApiImporter extends AbstractImporter implements ImporterInterface
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    public function __construct(ClientInterface $client, SerializerInterface $serializer)
    {
        $this->client = $client;
        $this->serializer = $serializer;
    }

    public function import(string $domain, int $articleNumber, bool $forceImageDownload = false): ContentInterface
    {
        $this->log(LogLevel::INFO, 'Fetching article '.$articleNumber);
        $response = $this->client->request('GET', $domain.'/api/articles/'.$articleNumber);
        $content = $response->getBody()->getContents();
// validate json
        json_decode($content);
        /** @var Article $article */
        $article = $this->serializer->deserialize($content, Article::class, 'json');
        $text = $this->replaceRelativeUrlsWithAbsolute($domain, $article->getFields()['body'] ?? '');
        $text = $this->fetchAndReplaceBodyImages($text, $domain, $forceImageDownload);
        $article->setBody($text);

        $this->processRenditions($domain, $article, $forceImageDownload);
        $this->processArticleAuthors($article);

        return $article;
    }
}
