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

namespace App\Importer;

use App\Entity\Article;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\GuzzleException;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LogLevel;

class NewscoopApiImporter extends AbstractImporter implements ImporterInterface
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * NewscoopApiImporter constructor.
     *
     * @param ClientInterface     $client
     * @param SerializerInterface $serializer
     */
    public function __construct(ClientInterface $client, SerializerInterface $serializer)
    {
        $this->client = $client;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function import(string $domain, int $articleNumber, bool $forceImageDownload = false): Article
    {
        try {
            $this->log(LogLevel::INFO, 'Fetching article '.$articleNumber);
            $response = $this->client->request('GET', $domain.'/api/articles/'.$articleNumber);
            $content = $response->getBody()->getContents();
            $this->validateJson($content);
        } catch (ServerException | ClientException | GuzzleException $e) {
            $this->log(LogLevel::ERROR, sprintf('Error on fetching article. Error message: %s', $e->getMessage()));
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

        return $article;
    }

    /**
     * @param string $string
     *
     * @return bool
     *
     * @throws \Exception
     */
    private function validateJson(string $string): bool
    {
        json_decode($string);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \Exception('Provided string is not valid json');
        }

        return true;
    }
}
