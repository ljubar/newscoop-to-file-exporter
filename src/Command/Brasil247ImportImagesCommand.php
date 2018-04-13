<?php
/*
 * Copyright (C) Paweł Mikołajczuk Creative Apps - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Paweł Mikołajczuk <pawel@mikolajczuk.in>, 2017.
 */
declare(strict_types=1);

namespace App\Command;

use App\Entity\Article;
use App\Factory\NinjsFactory;
use App\Importer\ImporterInterface;
use App\Importer\NewscoopImageApiImporter;
use App\Publisher\NinjsJsonPublisher;
use App\Publisher\PublisherInterface;
use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Brasil247ImportImagesCommand.
 */
class Brasil247ImportImagesCommand extends ContainerAwareCommand
{
    /**
     * @var ImporterInterface
     */
    protected $importer;

    /**
     * @var PublisherInterface
     */
    protected $publisher;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('newscoop:import-brasil247:images')
            ->setDescription('Imports newscoop images from brasil247 and save it to json (ninjs).')
            ->setHelp('This command allows to import Newscoop images with API and save them to nonjs formated json files')
            ->addArgument('domain', InputArgument::REQUIRED, 'Newscoop instance domain to fetch data from it.')
            ->addArgument('start', InputArgument::OPTIONAL, 'Number of image (start import from it).', null)
            ->addOption('force-image-download', null, InputOption::VALUE_NONE, 'Re-download images even if they are already fetched')
            ->addOption('print-output', null, InputOption::VALUE_NONE, 'Prints result of publishing')
            ->addOption('single-fetch', '-s', InputOption::VALUE_OPTIONAL, 'Article number to fetch');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = $input->getArgument('start');
        if (null !== $start) {
            $start = (int) $start;
        }
        $logger = new ConsoleLogger($output);
        $client = new Client();
        /** @var ProducerInterface $producer */
        $producer = $this->getContainer()->get('old_sound_rabbit_mq.newscoop_import_producer');

        if (null === $input->getOption('single-fetch')) {
            $this->processImages(
                $producer,
                $logger,
                $client,
                $input->getArgument('domain').'/api/images?items_per_page=600&sort[created]=desc',
                $input->getArgument('domain'),
                $start
            );
        } else {
            $this->processImage(
                $producer,
                $logger,
                $client,
                $input->getArgument('domain').'/api/images/'.$input->getOption('single-fetch'),
                $input->getArgument('domain')
            );
        }
    }

    /**
     * @param ProducerInterface $producer
     * @param ConsoleLogger     $logger
     * @param Client            $client
     * @param string            $url
     * @param string            $domain
     */
    protected function processImage(ProducerInterface $producer, ConsoleLogger $logger, Client $client, string $url, string $domain): void
    {
        $image = $this->getData($logger, $client, $url);

        try {
            $producer->publish(json_encode([
                'domain' => $domain,
                'contentId' => $image['id'],
                'forceImageDownload' => true,
                'importerClass' => NewscoopImageApiImporter::class,
                'publisherClass' => NinjsJsonPublisher::class,
                'publisherFactoryClass' => NinjsFactory::class,
            ]));
        } catch (\Exception $e) {
            $logger->log(LogLevel::ERROR, $e->getMessage());
        }
    }

    /**
     * @param ProducerInterface $producer
     * @param ConsoleLogger     $logger
     * @param Client            $client
     * @param string            $url
     * @param string            $domain
     * @param int|null          $start
     */
    protected function processImages(ProducerInterface $producer, ConsoleLogger $logger, Client $client, string $url, string $domain, int $start = null): void
    {
        $images = $this->getData($logger, $client, $url);
        $processedImages = 0;
        foreach ($images['items'] as $image) {
            if (null !== $start && $image['id'] > $start) {
                continue;
            }
            try {
                $producer->publish(json_encode([
                    'domain' => $domain,
                    'contentId' => $image['id'],
                    'forceImageDownload' => true,
                    'importerClass' => NewscoopImageApiImporter::class,
                    'publisherClass' => NinjsJsonPublisher::class,
                    'publisherFactoryClass' => NinjsFactory::class,
                ]));
                ++$processedImages;
            } catch (\Exception $e) {
                $logger->log(LogLevel::ERROR, $e->getMessage());
            }
        }
        $logger->log(LogLevel::INFO, 'Processed '.$processedImages.' images');

        if (isset($images['pagination']['nextPageLink'])) {
            $this->processImages($producer, $logger, $client, $images['pagination']['nextPageLink'], $domain);
        }
    }

    /**
     * @param ConsoleLogger $logger
     * @param Client        $client
     * @param string        $url
     *
     * @return array|null
     */
    protected function getData(ConsoleLogger $logger, Client $client, string $url): ?array
    {
        $logger->log(LogLevel::INFO, 'Fetching images from url: '.$url);
        $response = $client->request('GET', $url, [
            'on_stats' => function (TransferStats $stats) use ($logger) {
                $logger->log(LogLevel::INFO, 'request time: '.$stats->getTransferTime());
            },
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
