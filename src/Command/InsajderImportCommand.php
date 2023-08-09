<?php
declare(strict_types=1);

namespace App\Command;

use App\Entity\Article;
use App\Factory\InsajderNinjsFactory;
use App\Importer\NewscoopApiImporter;
use AHS\Publisher\NinjsPublisher;
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

use function Safe\json_encode;
use function Safe\json_decode;

class InsajderImportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('newscoop:import-insajder')
            ->setDescription('Imports newscoop articles from Insajder and save it to json.')
            ->setHelp('This command allows to import Newscoop articles with API usage and save them to predefined structure of html files')
            ->addArgument('domain', InputArgument::REQUIRED, 'Newscoop instance domain to fetch data from it.')
            ->addArgument('start', InputArgument::OPTIONAL, 'Number of article (start import from it).', null)
            ->addOption('force-image-download', null, InputOption::VALUE_NONE, 'Re-download images even if they are already fetched')
            ->addOption('single-fetch', '-s', InputOption::VALUE_OPTIONAL, 'Article number to fetch');
    }

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
            $this->processArticles(
                $producer,
                $logger,
                $client,
                $input->getArgument('domain').'/api/articles?items_per_page=600&fields=number&language=en&sort[created]=desc',
                $input->getArgument('domain'),
                $start
            );
        } else {
            $this->processArticle(
                $producer,
                $logger,
                $client,
                $input->getArgument('domain').'/api/articles/'.$input->getOption('single-fetch'),
                $input->getArgument('domain')
            );
        }
    }

    protected function processArticle(ProducerInterface $producer, ConsoleLogger $logger, Client $client, string $url, string $domain): void
    {
        $article = $this->getArticles($logger, $client, $url);

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $processedArticle = $em->getRepository(Article::class)->findOneBy(['number' => $article['number']]);
        if ($processedArticle) {
            return;
        }

        try {
            $producer->publish(json_encode([
                'domain' => $domain,
                'contentId' => $article['number'],
                'forceImageDownload' => true,
                'importerClass' => NewscoopApiImporter::class,
                'publisherClass' => NinjsPublisher::class,
                'publisherFactoryClass' => InsajderNinjsFactory::class,
            ]));
            $processedArticle = new Article();
            $processedArticle->setNumber($article['number']);
            $em->persist($processedArticle);
            $em->flush();
        } catch (\Exception $e) {
            $logger->log(LogLevel::ERROR, $e->getMessage());
        }
    }

    protected function processArticles(ProducerInterface $producer, ConsoleLogger $logger, Client $client, string $url, string $domain, int $start = null): void
    {
        $articles = $this->getArticles($logger, $client, $url);
        $processedArticles = 0;
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
$arts = [689,773,575,566,4189,650,506,3566,3541,569,4483,574,799];
        foreach ($arts as $article) {
            if (null !== $start && $article > $start) {
                continue;
            }
            $processedArticle = $em->getRepository(Article::class)->findOneBy(['number' => $article]);
            if ($processedArticle) {
                continue;
            }

            try {
                $producer->publish(json_encode([
                    'domain' => $domain,
                    'contentId' => $article,
                    'forceImageDownload' => true,
                    'importerClass' => NewscoopApiImporter::class,
                    'publisherClass' => NinjsPublisher::class,
                    'publisherFactoryClass' => InsajderNinjsFactory::class,
                ]));
                ++$processedArticles;
                $processedArticle = new Article();
                $processedArticle->setNumber($article);
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
