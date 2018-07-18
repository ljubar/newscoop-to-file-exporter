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

use App\Entity\ContentInterface;
use Behat\Transliterator\Transliterator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class ExternalDataPublisher extends AbstractPublisher implements PublisherInterface
{
    public function publish(ContentInterface $content, $printRenderedTemplate = false): void
    {
        $data = $content->getExternalData();
        $slug = Transliterator::urlize($data['title']);
        unset($data['title']);
        $client = new Client();
        try {
            $body = \json_encode($data, JSON_UNESCAPED_SLASHES);
            $requestData = ['body' => $body];
            if (null !== $content->getPublisherSecret()) {
                $token = hash_hmac('sha1', $body, $content->getPublisherSecret());
                $requestData['headers'] = ['x-publisher-signature' => $token];
            }

            $client->request('PUT', $content->getPublisherUrl() . '/app_dev.php/api/v1/packages/extra/' . $slug, $requestData);
        } catch (GuzzleException $e) {
            dump($e);
        }
    }
}
