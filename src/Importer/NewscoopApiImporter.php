<?php

declare(strict_types=1);

namespace App\Importer;

use AHS\Content\ContentInterface;
use App\Entity\Article;
use AHS\Serializer\SerializerInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LogLevel;

use function Safe\sprintf;
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
        try {
            $this->log(LogLevel::INFO, 'Fetching article '.$articleNumber);
            $response = $this->client->request('GET', $domain.'/api/articles/'.$articleNumber);
            $content = $response->getBody()->getContents();
            $this->validateJson($content);
        } catch (ServerException | ClientException | GuzzleException $e) {
            $this->log(LogLevel::ERROR, sprintf("Error on fetching article: \n %s", $e->getMessage()));
        } catch (\Exception $e) {
            $this->log(LogLevel::ERROR, $e->getMessage());
        }

        if (!isset($content)) {
            throw new \Exception('Couldn\'t fetch valid article json data from Newscoop');
        }

        /** @var Article $article */
        $article = $this->serializer->deserialize($content, Article::class, 'json');
        $text = $this->replaceRelativeUrlsWithAbsolute($domain, $article->getBody());
        $text = $this->fetchAndReplaceBodyImages($text, $domain, $forceImageDownload);
        $article->setBody($text);
        $this->processRenditions($domain, $article, $forceImageDownload);
        $this->processArticleAuthors($article);

        return $article;
    }

    private function validateJson(string $string): bool
    {
        json_decode($string);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \Exception('Provided string is not valid json');
        }

        return true;
    }
}
