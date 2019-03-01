<?php
/*
 * Copyright (C) Paweł Mikołajczuk Creative Apps - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Paweł Mikołajczuk <pawel@mikolajczuk.in>, 2017.
 */
declare(strict_types=1);

namespace App\Consumer;

use App\Entity\ExternalData;
use AHS\Publisher\PublisherInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use function Safe\sprintf;
use function Safe\json_decode;

class PublisherExternalDataConsumer implements ConsumerInterface
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
        /** @var PublisherInterface $publisher */
        $publisher = $this->container->get($data['publisherClass']);
        $publisher->setLogger($this->logger);

        try {
            echo sprintf('Publishing item: %s', $data['articleData']['number'])."\n";

            $externalData = [];
            $data['fields'][] = 'title';
            foreach ($data['fields'] as $field) {
                if (array_key_exists($field, $data['articleData'])) {
                    $externalData[$field] = $data['articleData'][$field];
                }
            }

            $publisher->publish(new ExternalData($data['publisherDomain'], $externalData));
        } catch (\Exception $e) {
            echo $e->getMessage()."\n";

            return ConsumerInterface::MSG_REJECT;
        }

        return ConsumerInterface::MSG_ACK;
    }
}
