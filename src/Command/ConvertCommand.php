<?php

namespace App\Command;

/*
 * Class ConvertCommand
 */
use App\Entity\Article;
use GuzzleHttp\Client;
use JMS\Serializer\Serializer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ClientException;

class ConvertCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('newscoop:import-and-save-to-html')
            ->setDescription('Imports newscoop articles and saved it to html.')
            ->setHelp('This command allows to import Newscoop articles with API usage and save them to predefined structure of html files')
            ->addArgument('domain', InputArgument::REQUIRED, 'Newscoop instance domain to fetch data from it.')
            ->addArgument('start', InputArgument::OPTIONAL, 'Number of article (start import from it).', 1)
            ->addArgument('end', InputArgument::OPTIONAL, 'Number of article (stop import on it).', 100)
            ->addOption('force-image-download', null, InputOption::VALUE_NONE, 'Re-download images even if they are already fetched')
            ->addOption('print-rendered-template', null, InputOption::VALUE_NONE, 'Prints result of template rendering');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = (int) $input->getArgument('start');
        $end = (int) $input->getArgument('end');
        $domain = $input->getArgument('domain');
        $forceImageDownload = $input->getOption('force-image-download');
        $printRenderedTemplate = $input->getOption('print-rendered-template');
        $client = new \GuzzleHttp\Client();
        /** @var Serializer $serializer */
        $serializer = $this->getContainer()->get('jms_serializer');
        for ($number = $start; $number <= $end; ++$number) {
            try {
                $output->writeln('Fetching article '.$number);
                $response = $client->request('GET', $domain.'/api/articles/'.$number);
                $content = $response->getBody();
            } catch (ServerException |ClientException $e) {
                $output->writeln(printf('Error on fetching article. Error message: %s', $e->getMessage()));
                continue;
            }

            if (!$this->isJson($content)) {
                $output->writeln('Content is not valid JSON string');
                continue;
            }

            /** @var Article $article */
            $article = $serializer->deserialize($content, Article::class, 'json');
            $article->setBody($this->replaceRelativeUrlsWithAbsolute($domain, $article->getBody()));
            $this->processRenditions($client, $domain, $article, $forceImageDownload, $output);

            $output->writeln('Rendering article '.$number);
            $content = $this->getContainer()->get('twig')->render('article.html.twig', ['article' => $article]);

            if ($printRenderedTemplate) {
                $output->writeln($content);
            }

            $this->saveContentToFile($domain, $article, $content);
        }
    }

    protected function isJson($string)
    {
        json_decode($string);

        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * @param Client          $client
     * @param string          $domain
     * @param Article         $article
     * @param bool            $forceImageDownload
     * @param OutputInterface $output
     */
    protected function processRenditions(Client $client, string $domain, Article $article, bool $forceImageDownload, OutputInterface $output)
    {
        $downloadedImages = [];
        foreach ($article->getRenditions() as $rendition) {
            $renditionDetails = $rendition->getDetails();
            if (isset($renditionDetails['original']['src'])) {
                $src = $renditionDetails['original']['src'];
                $filesystem = new Filesystem();
                $urlParts = explode('/', str_replace('cache/', '', $src));

                $fileName = $urlParts[count($urlParts) - 1];
                $fileName = substr($fileName, strpos($fileName, 'cms-image-'));
                unset($urlParts[count($urlParts) - 1]);

                $filePath = str_replace('https://', '', str_replace('http://', '', implode('/', $urlParts)));
                $path = __DIR__.'/../../public/articles/'.$filePath;
                $filesystem->mkdir($path);
                $originalImageUrl = $domain.'/images/'.$fileName;
                if ((!file_exists($path.'/'.$fileName) || $forceImageDownload) && !in_array($originalImageUrl, $downloadedImages)) {
                    try {
                        $response = $client->get($originalImageUrl);
                    } catch (ServerException | ClientException $e) {
                        $output->writeln(printf('Error on fetching image. Error message: %s', $e->getMessage()));
                        continue;
                    }

                    $downloadedImages[] = $originalImageUrl;
                    $output->writeln(sprintf('Downloading image from path: %s', $originalImageUrl));
                    file_put_contents($path.'/'.$fileName, $response->getBody());
                }
                $rendition->setLink('/images/'.$filePath.'/'.$fileName);
                $renditionDetails['original']['src'] = '/images/'.$filePath.'/'.$fileName;
                $rendition->setDetails($renditionDetails);
            }
        }
    }

    /**
     * @param string  $domain
     * @param Article $article
     * @param string  $content
     */
    protected function saveContentToFile($domain, $article, $content)
    {
        $filesystem = new Filesystem();
        $urlParts = explode('/', $article->getUrl());
        $fileName = $urlParts[count($urlParts) - 1];
        unset($urlParts[count($urlParts) - 1]);
        $path = __DIR__.'/../../public/articles/'.str_replace('https://', '', str_replace('http://', '', implode('/', $urlParts)));
        $filesystem->mkdir($path);
        file_put_contents($path.'/'.$fileName, $content);
    }

    /**
     * @param $domain
     * @param $text
     *
     * @return mixed
     */
    private function replaceRelativeUrlsWithAbsolute($domain, $text)
    {
        return preg_replace("/(href|src)\=\"([^(http)])(\/)?/", "$1=\"$domain$2", $text);
    }
}
