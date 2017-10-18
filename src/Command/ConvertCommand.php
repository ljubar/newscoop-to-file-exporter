<?php

namespace App\Command;

/*
 * Class ConvertCommand
 */
use App\Entity\Article;
use JMS\Serializer\Serializer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

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
            ->addArgument('end', InputArgument::OPTIONAL, 'Number of article (stop import on it).', 100);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = (int) $input->getArgument('start');
        $end = (int) $input->getArgument('end');
        $domain = $input->getArgument('domain');
        $client = new \GuzzleHttp\Client();
        /** @var Serializer $serializer */
        $serializer = $this->getContainer()->get('jms_serializer');
        for ($number = $start; $number <= $end; ++$number) {
            try {
                $output->writeln('Fetching article '.$number);
                $response = $client->request('GET', $domain.'/api/articles/'.$number);
            } catch (\GuzzleHttp\Exception\ServerException $e) {
                $output->writeln(printf('Error on fetching article. Error message: %s', $e->getMessage()));
                continue;
            }
            /** @var Article $article */
            $article = $serializer->deserialize($response->getBody(), Article::class, 'json');
            $article->setBody($this->replaceRelativeUrlsWithAbsolute($domain, $article->getBody()));

            $output->writeln('Rendering article '.$number);
            $content = $this->getContainer()->get('twig')->render('article.html.twig', ['article' => $article]);

            $output->writeln($content);

            $this->saveContentToFile($domain, $article, $content);
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
