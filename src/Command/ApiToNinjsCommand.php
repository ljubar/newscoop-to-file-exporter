<?php
/*
 * Copyright (C) Paweł Mikołajczuk Creative Apps - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Paweł Mikołajczuk <pawel@mikolajczuk.in>, 2017.
 */
declare(strict_types=1);

namespace App\Command;

use AHS\Content\Article;
use AHS\Publisher\NinjsPublisher;
use App\Importer\NewscoopApiImporter;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class ApiToNinjsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('newscoop:import-and-save-to-ninjs')
            ->setDescription('Imports newscoop articles and save it to json files with content in ninjs format.')
            ->setHelp('This command allows to import Newscoop articles with API usage and save them to json files in ninjs format')
            ->addArgument('domain', InputArgument::REQUIRED, 'Newscoop instance domain to fetch data from it.')
            ->addArgument('start', InputArgument::OPTIONAL, 'Number of article (start import from it).', 1)
            ->addArgument('end', InputArgument::OPTIONAL, 'Number of article (stop import on it).', 100)
            ->addOption('force-image-download', null, InputOption::VALUE_NONE, 'Re-download images even if they are already fetched')
            ->addOption('print-output', null, InputOption::VALUE_NONE, 'Prints result of publishing');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = (int) $input->getArgument('start');
        $end = (int) $input->getArgument('end');

        $logger = new ConsoleLogger($output);
        $importer = $this->getContainer()->get(NewscoopApiImporter::class);
        $publisher = $this->getContainer()->get(NinjsPublisher::class);
        $importer->setLogger($logger);
        $publisher->setLogger($logger);
        for ($number = $start; $number <= $end; ++$number) {
            try {
                /** @var Article $article */
                $article = $importer->import(
                    $input->getArgument('domain'),
                    $number,
                    $input->getOption('force-image-download')
                );
            } catch (\Exception $e) {
                $logger->error($e->getMessage());
                continue;
            }

            if (!$article->isPublished()) {
                continue;
            }

            $publisher->publish($article, $input->getOption('print-output'));
        }
    }
}
