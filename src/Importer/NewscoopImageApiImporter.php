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

use AHS\Content\ContentInterface;
use AHS\Serializer\SerializerInterface;
use App\Entity\Image;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LogLevel;

use function Safe\sprintf;
use function Safe\json_decode;

class NewscoopImageApiImporter extends AbstractImporter implements ImporterInterface
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

    public function import(string $domain, int $number, bool $forceImageDownload = false): ContentInterface
    {
        try {
            $this->log(LogLevel::INFO, 'Fetching image '.$number);
            $response = $this->client->request('GET', $domain.'/api/images/'.$number);
            $content = $response->getBody()->getContents();
            $this->validateJson($content);
        } catch (ServerException | ClientException | GuzzleException $e) {
            $this->log(LogLevel::ERROR, sprintf('Error on fetching article. Error message: %s', $e->getMessage()));
        } catch (\Exception $e) {
            $this->log(LogLevel::ERROR, $e->getMessage());
        }

        if (!isset($content)) {
            throw new \Exception('Couldn\'t fetch valid image json data from Newscoop');
        }

        /** @var Image $image */
        $image = $this->serializer->deserialize($content, Image::class, 'json');
        $image->setDomain($domain);

        return $image;
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
