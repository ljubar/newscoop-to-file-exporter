<?php
/*
 * Copyright (C) Paweł Mikołajczuk Creative Apps - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Paweł Mikołajczuk <pawel@mikolajczuk.in>, 2017.
 */
declare(strict_types=1);

namespace App\Consumer;

use AHS\Content\ArticleInterface;
use AHS\Factory\FactoryInterface;
use AHS\Publisher\PublisherInterface;
use App\Entity\ImageInterface;
use App\Importer\ImporterInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use function Safe\sprintf;
use function Safe\json_decode;

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

    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->logger = $logger;
    }

    public function execute(AMQPMessage $msg): int
    {
        $data = json_decode($msg->body, true);
        /** @var ImporterInterface $importer */
        $importer = $this->container->get($data['importerClass']);
        /** @var PublisherInterface $publisher */
        $publisher = $this->container->get($data['publisherClass']);
        $importer->setLogger($this->logger);
        $publisher->setLogger($this->logger);
        /** @var FactoryInterface $factory */
        $factory = $this->container->get($data['publisherFactoryClass']);
        $publisher->setFactory($factory);

        try {
            echo sprintf('Importing item: %s', $data['contentId'])."\n";
            $content = $importer->import($data['domain'], (int) $data['contentId'], $data['forceImageDownload']);
if ($content->getSection()['number'] === 170) {
echo 'section 170 skipped';
return ConsumerInterface::MSG_ACK;
}
            if ($content instanceof ArticleInterface) {
                // Check if it's in selected issue and sections
                if ('newswire' !== $content->getType()) {
                    //echo sprintf('Publishing article from issue: %s and section: %s and type: %s', $content->getIssue(), $content->getSection(), $content->getType())."\n";
                    $publisher->publish($content);
                } else {
                    echo "Newswire is ignored \n";
                }
            } elseif ($content instanceof ImageInterface) {
                if ($content->getDescription() && '' !== $content->getDescription() && 'Divulgação' !== $content->getPhotographer()) {
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
