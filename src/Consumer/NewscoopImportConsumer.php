<?php
/*
 * Copyright (C) Paweł Mikołajczuk Creative Apps - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Paweł Mikołajczuk <pawel@mikolajczuk.in>, 2017.
 */
declare(strict_types=1);

namespace App\Consumer;

use App\Entity\ArticleInterface;
use App\Entity\ImageInterface;
use App\Importer\ImporterInterface;
use App\Publisher\PublisherInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class NewscoopImportConsumer implements ConsumerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * NewscoopImportConsumer constructor.
     *
     * @param ContainerInterface $container
     * @param LoggerInterface    $logger
     */
    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->logger = $logger;
    }

    /**
     * @param AMQPMessage $msg
     *
     * @return mixed|void
     */
    public function execute(AMQPMessage $msg)
    {
        $data = json_decode($msg->body, true);
        /** @var ImporterInterface $importer */
        $importer = $this->container->get($data['importerClass']);
        /** @var PublisherInterface $publisher */
        $publisher = $this->container->get($data['publisherClass']);
        $importer->setLogger($this->logger);
        $publisher->setLogger($this->logger);
        $publisher->setFactory($this->container->get($data['publisherFactoryClass']));

        try {
            echo sprintf('Importing item: %s', $data['contentId'])."\n";
            $content = $importer->import($data['domain'], (int) $data['contentId'], $data['forceImageDownload']);

            if ($content instanceof ArticleInterface) {
                // Check if it's in selected issue and sections
                if ('newswire' !== $content->getType()) {
                    echo sprintf('Publishing article from issue: %s and section: %s and type: %s', $content->getIssue()['number'], $content->getSection()['number'], $content->getType())."\n";
                    $publisher->publish($content);
                } else {
                    echo "Newswire is ignored \n";
                }
            } elseif ($content instanceof ImageInterface) {
                if ('' !== $content->getDescription()) {
                    echo sprintf('Publishing image with id: %s', $content->getIdentifier())."\n";
                    $publisher->publish($content);
                } else {
                    echo "Image without metadata is ignored \n";
                }
            }
        } catch (\Exception $e) {
            echo $e->getMessage()."\n";

            return ConsumerInterface::MSG_REJECT;
        }

        return ConsumerInterface::MSG_ACK;
    }
}
