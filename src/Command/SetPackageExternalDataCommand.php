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
use App\Publisher\ExternalDataPublisher;
use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class SetPackageExternalDataCommand extends ContainerAwareCommand
{
    protected function configure(): void
    {
        $this
            ->setName('newscoop:package-external-data')
            ->setDescription('Import selected newscoop article data, add them to queue for push to publisher')
            ->setHelp('This command allows to import Newscoop data and send them as external data to Superdesk Publisher packages')
            ->addArgument('newscoopDomain', InputArgument::REQUIRED, 'Newscoop instance domain to fetch data from it.')
            ->addArgument('publisherDomain', InputArgument::REQUIRED, 'Publisher instance domain to push data to it.')
            ->addArgument('publisherSecret', InputArgument::OPTIONAL, 'Publisher instance secret used for authorization.')
            ->addArgument('start', InputArgument::OPTIONAL, 'Number of article (start import from it).', null)
            ->addOption('fields', '-f', InputOption::VALUE_OPTIONAL, 'Article fields passed to Publisher (comma,separated)', 'url,number,webcode')
            ->addOption('single-fetch', '-s', InputOption::VALUE_OPTIONAL, 'Article number to fetch');
    }


    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $start = $input->getArgument('start');
        if (null !== $start) {
            $start = (int) $start;
        }
        $logger = new ConsoleLogger($output);
        $client = new Client();
        /** @var ProducerInterface $producer */
        $producer = $this->getContainer()->get('old_sound_rabbit_mq.newscoop_external_data_producer');

        if (null === $input->getOption('single-fetch')) {
            $this->processArticles(
                $producer,
                $logger,
                $client,
                $input->getArgument('newscoopDomain').'/api/articles?items_per_page=600&language=pt&sort[created]=desc',
                $input->getArgument('newscoopDomain'),
                $input->getArgument('publisherDomain'),
                $input->getArgument('publisherSecret'),
                explode(',', $input->getOption('fields')),
                $start
            );
        } else {
            $this->processArticle(
                $producer,
                $logger,
                $client,
                $input->getArgument('newscoopDomain').'/api/articles/'.$input->getOption('single-fetch'),
                $input->getArgument('newscoopDomain'),
                $input->getArgument('publisherDomain'),
                $input->getArgument('publisherSecret'),
                explode(',', $input->getOption('fields'))
            );
        }
    }

    protected function processArticle(
        ProducerInterface $producer,
        ConsoleLogger $logger,
        Client $client,
        string $url,
        string $newscoopDomain,
        string $publisherDomain,
        ?string $publisherSecret,
        array $fields
    ): void {
        $article = $this->getArticles($logger, $client, $url);
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $processedArticle = $em->getRepository(Article::class)->findOneBy(['number' => $article['number'], 'command' => SetPackageExternalDataCommand::class]);
        if ($processedArticle) {
            return;
        }

        try {
            $processedArticle = $this->publishArticleData($producer, $newscoopDomain, $publisherDomain, $publisherSecret, $fields, $article);
            $em->persist($processedArticle);
            $em->flush();
        } catch (\Exception $e) {
            $logger->log(LogLevel::ERROR, $e->getMessage());
        }
    }

    protected function processArticles(
        ProducerInterface $producer,
        ConsoleLogger $logger,
        Client $client,
        string $url,
        string $newscoopDomain,
        string $publisherDomain,
        ?string $publisherSecret,
        array $fields,
        int $start = null
    ): void {
        $articles = $this->getArticles($logger, $client, $url);
        $processedArticles = 0;
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        foreach ($articles['items'] as $article) {
            if (null !== $start && $article['number'] > $start) {
                continue;
            }
            $processedArticle = $em->getRepository(Article::class)->findOneBy(['number' => $article['number'], 'command' => SetPackageExternalDataCommand::class]);
            if ($processedArticle) {
                continue;
            }

            try {
                $processedArticle = $this->publishArticleData($producer, $newscoopDomain, $publisherDomain, $publisherSecret, $fields, $article);
                ++$processedArticles;
                $em->persist($processedArticle);
            } catch (\Exception $e) {
                $logger->log(LogLevel::ERROR, $e->getMessage());
            }
        }
        $em->flush();
        $logger->log(LogLevel::INFO, 'Processed '.$processedArticles.' articles');

        if (isset($articles['pagination']['nextPageLink'])) {
            $this->processArticles($producer, $logger, $client, $articles['pagination']['nextPageLink'], $domain);
        }
    }

    protected function publishArticleData(ProducerInterface $producer, string $newscoopDomain, string $publisherDomain, ?string $publisherSecret, array $fields, array $article): Article
    {
        $producer->publish(json_encode([
            'newscoopDomain' => $newscoopDomain,
            'publisherDomain' => $publisherDomain,
            'publisherSecret' => $publisherSecret,
            'articleData' => $article,
            'fields' => $fields,
            'publisherClass' => ExternalDataPublisher::class,
        ]));
        $processedArticle = new Article();
        $processedArticle->setNumber($article['number']);
        $processedArticle->setCommand(SetPackageExternalDataCommand::class);

        return $processedArticle;
    }

    /**
     * @param ConsoleLogger $logger
     * @param Client        $client
     * @param string        $url
     *
     * @return array|null
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
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
