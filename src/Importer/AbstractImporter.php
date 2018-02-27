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
use App\Entity\Rendition;
use App\LoggerTrait;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LogLevel;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractImporter
{
    use LoggerTrait;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @param string $text
     * @param string $domain
     * @param bool   $forceImageDownload
     *
     * @return string
     */
    protected function fetchAndReplaceBodyImages(string $text, string $domain, bool $forceImageDownload): string
    {
        if ('' === $text) {
            return $text;
        }

        libxml_use_internal_errors(true);
        $dom = new \DOMdocument();

        /*** load the html into the object ***/
        $dom->loadHTML(mb_convert_encoding($text, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        /*** discard white space ***/
        $dom->preserveWhiteSpace = false;
        $images = $dom->getElementsByTagName('img');

        $filesystem = new Filesystem();
        $downloadedImages = [];
        /** @var \DOMElement $img */
        foreach ($images as $img) {
            $originalImageUrl = $img->getAttribute('src');
            $parts = parse_url($originalImageUrl);
            if (!isset($parts['query'])) {
                continue;
            }

            parse_str($parts['query'], $query);
            if (!isset($query['ImageId'])) {
                continue;
            }
            $imageId = $query['ImageId'];

            $fileName = $imageId.'.jpg';
            $filePath = str_replace('https://', '', str_replace('http://', '', $domain)).'/images';
            $path = __DIR__.'/../../public/articles/'.$filePath;
            $filesystem->mkdir($path);
            if ((!file_exists($path.'/'.$fileName) || $forceImageDownload) && !in_array($originalImageUrl, $downloadedImages)) {
                try {
                    $response = $this->client->request('GET', $originalImageUrl);
                } catch (ServerException | ClientException | GuzzleException $e) {
                    $this->log(LogLevel::INFO, sprintf('Error on fetching image. Error message: %s', $e->getMessage()));
                    continue;
                }

                $downloadedImages[] = $originalImageUrl;
                $this->log(LogLevel::INFO, sprintf('Downloading body image from path: %s', $originalImageUrl));
                file_put_contents($path.'/'.$fileName, $response->getBody());
            }
            $img->setAttribute('src', '/'.$filePath.'/'.$fileName);
        }

        return $dom->saveHTML($dom->documentElement);
    }

    /**
     * @param string  $domain
     * @param Article $article
     * @param bool    $forceImageDownload
     */
    protected function processRenditions(string $domain, Article $article, bool $forceImageDownload)
    {
        $downloadedImages = [];
        /* @var Rendition $rendition */
        foreach ($article->getRenditions() as $rendition) {
            $renditionDetails = $rendition->getDetails();
            if (isset($renditionDetails['original']['src'])) {
                $src = str_replace('%7C', '/', urldecode($renditionDetails['original']['src']));
                $filesystem = new Filesystem();
                $urlParts = explode('/', str_replace('cache/', '', $src));

                $fileName = $urlParts[count($urlParts) - 1];
                $pos = strpos($fileName, 'cms-image-');
                if (is_numeric($pos)) {
                    $fileName = substr($fileName, $pos);
                    $originalImageUrl = $domain.'/images/'.$fileName;
                } else {
                    $originalImageUrl = preg_replace('/\/images\/cache\/([\d]+)x([\d]+)\/(fit|crop)\/images/', '/images', $src);
                }
                unset($urlParts[count($urlParts) - 1]);
                $filePath = str_replace('https://', '', str_replace('http://', '', implode('/', $urlParts)));
                $path = __DIR__.'/../../public/articles/'.$filePath;
                $filesystem->mkdir($path);
                if ((!file_exists($path.'/'.$fileName) || $forceImageDownload) && !in_array($originalImageUrl, $downloadedImages)) {
                    try {
                        $response = $this->client->request('GET', $originalImageUrl);
                    } catch (ServerException | ClientException | GuzzleException $e) {
                        $this->log(LogLevel::INFO, sprintf('Error on fetching image. Error message: %s', $e->getMessage()));
                        continue;
                    }

                    $downloadedImages[] = $originalImageUrl;
                    $this->log(LogLevel::INFO, sprintf('Downloading rendition image from path: %s', $originalImageUrl));
                    file_put_contents($path.'/'.$fileName, $response->getBody());
                    $this->log(LogLevel::INFO, sprintf('Saving file in path: %s', $path.'/'.$fileName));
                }
                $rendition->setLink('/articles/'.$filePath.'/'.$fileName);
                $renditionDetails['original']['src'] = '/articles/'.$filePath.'/'.$fileName;
                $renditionDetails['original']['external_src'] = $originalImageUrl;
                $rendition->setDetails($renditionDetails);
            }
        }
    }

    /**
     * @param string $domain
     * @param string $text
     *
     * @return string
     */
    protected function replaceRelativeUrlsWithAbsolute(string $domain, string $text): string
    {
        return preg_replace("/(href|src)\=\"([^(http)])(\/)?/", "$1=\"$domain$2", $text);
    }
}
