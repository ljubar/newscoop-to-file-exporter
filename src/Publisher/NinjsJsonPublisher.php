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
use App\Factory\FactoryInterface;
use App\Factory\NinjsFactory;
use App\Serializer\SerializerInterface;
use Psr\Log\LogLevel;
use SWP\Component\Bridge\Validator\NinjsValidator;

class NinjsJsonPublisher extends AbstractPublisher implements PublisherInterface
{
    /**
     * @var string
     */
    protected $projectDir;

    /**
     * @var NinjsFactory
     */
    protected $ninjsFactory;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * NinjsJsonPublisher constructor.
     *
     * @param string              $projectDir
     * @param FactoryInterface    $ninjsFactory
     * @param SerializerInterface $serializer
     */
    public function __construct(string $projectDir, FactoryInterface $ninjsFactory, SerializerInterface $serializer)
    {
        $this->projectDir = $projectDir;
        $this->ninjsFactory = $ninjsFactory;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function publish(Article $article, $printRenderedTemplate = false): void
    {
        $this->log(LogLevel::INFO, 'Creating NINJS for article with number: '.$article->getNumber());

        $item = $this->ninjsFactory->create($article);
        $ninJs = $this->serializer->serialize($item, 'json', ['json_encode_options' => JSON_PRETTY_PRINT]);

        $validator = new NinjsValidator($this->logger);
        if (!$validator->isValid($ninJs)) {
            throw new \Exception('Generated ninjs is not valid');
        }

        $this->log(LogLevel::INFO, 'Generated ninjs is valid');

        if ($printRenderedTemplate) {
            $this->log(LogLevel::INFO, $ninJs);
        }

        $domain = explode('/', $article->getUrl())[2];
        $fileName = $article->getNumber().'.json';
        $path = $this->projectDir.'/public/ninjs/'.$domain;

        $this->saveContentToFile($fileName, $path, $ninJs);
    }

    /**
     * @param FactoryInterface $factory
     */
    public function setFactory(FactoryInterface $factory)
    {
        $this->ninjsFactory = $factory;
    }
}
