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
use App\Factory\Brasil247NinjsFactory;
use App\Importer\ImporterInterface;
use App\Importer\NewscoopApiImporter;
use App\Publisher\NinjsJsonPublisher;
use App\Publisher\PublisherInterface;
use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Brasil247ImportCommand.
 */
class Brasil247ImportCommand extends ContainerAwareCommand
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
            ->setName('newscoop:import-brasil247')
            ->setDescription('Imports newscoop articles from brasil247 and save it to json.')
            ->setHelp('This command allows to import Newscoop articles with API usage and save them to predefined structure of html files')
            ->addArgument('domain', InputArgument::REQUIRED, 'Newscoop instance domain to fetch data from it.')
            ->addArgument('start', InputArgument::OPTIONAL, 'Number of article (start import from it).', 1)
            ->addOption('force-image-download', null, InputOption::VALUE_NONE, 'Re-download images even if they are already fetched')
            ->addOption('print-output', null, InputOption::VALUE_NONE, 'Prints result of publishing');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = (int) $input->getArgument('start');
        $logger = new ConsoleLogger($output);
        $this->importer = $this->getContainer()->get(NewscoopApiImporter::class);
        $this->publisher = $this->getContainer()->get(NinjsJsonPublisher::class);
        $this->importer->setLogger($logger);
        $this->publisher->setLogger($logger);
        $this->publisher->setFactory($this->getContainer()->get(Brasil247NinjsFactory::class));
        $page = '';
        if ($start > 600) {
            $startPage = round($start / 600);
            $page = '&page='.$startPage;
        }

        $client = new Client();
        $this->process(
            $logger,
            $client,
            $input->getArgument('domain').'/api/articles?items_per_page=600&fields=number&language=pt&sort[created]=desc'.$page,
            $input->getArgument('domain'),
            $start
        );
    }

    /**
     * @param ConsoleLogger $logger
     * @param Client        $client
     * @param string        $url
     * @param string        $domain
     * @param int|null      $start
     */
    protected function process(ConsoleLogger $logger, Client $client, string $url, string $domain, int $start = null): void
    {
        $articles = $this->getArticles($logger, $client, $url);
        foreach ($articles['items'] as $article) {
            if (null !== $start && $article['number'] < $start) {
                continue;
            }
//            try {
            $article = $this->importer->import($domain, $article['number'], true);
            $this->publisher->publish($article, true);
//            } catch (\Exception $e) {
//                $logger->log(LogLevel::ERROR, $e->getMessage());
//            }
        }

        if (isset($articles['pagination']['nextPageLink'])) {
            $this->process($logger, $client, $articles['pagination']['nextPageLink'], $domain);
        }
    }

    /**
     * @param ConsoleLogger $logger
     * @param Client        $client
     * @param string        $url
     *
     * @return array|null
     */
    protected function getArticles(ConsoleLogger $logger, Client $client, string $url): ?array
    {
        $logger->log(LogLevel::INFO, 'Fetching articles from url: '.$url);
        $articlesResponse = $client->request('GET', $url, [
            'on_stats' => function (TransferStats $stats) use ($logger) {
                $logger->log(LogLevel::INFO, 'request time: '.$stats->getTransferTime());
            },
        ]);

        return json_decode($articlesResponse->getBody()->getContents(), true);
    }
}
