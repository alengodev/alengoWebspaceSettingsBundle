<?php

declare(strict_types=1);

namespace Alengo\Bundle\AlengoWebspaceSettingsBundle\Command;

use Alengo\Bundle\AlengoWebspaceSettingsBundle\Entity\WebspaceSettings;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('webspace:settings:json-migrate', 'Json Migration Command v0.6 to >= 0.7')]
class DataJsonMigrationCommand extends Command
{
    use LockableTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        /*$this
            ->addOption('force', 'f', InputOption::VALUE_NEGATABLE, 'Force update')
        ;*/
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return Command::SUCCESS;
        }

        $io = new SymfonyStyle($input, $output);
        $io->note('Running command');

        $webspaceSettings = $this->entityManager->getRepository(WebspaceSettings::class)->findAll();

        $filteredEntities = \array_filter($webspaceSettings, function ($entity) {
            $data = $entity->getData();

            return \is_array($data) && !isset($data['_data']);
        });

        if (empty($filteredEntities)) {
            $io->success('No entities found that need migration.');
            $this->release();

            return Command::SUCCESS;
        }

        $progressBar = new ProgressBar($output, \count($filteredEntities));
        $progressBar->start();

        $i = 0;
        foreach ($filteredEntities as $entity) {
            $entity->setData([
                '_data' => $entity->getData()[0],
            ]);
            $this->entityManager->flush($entity);

            $progressBar->advance($i);
        }

        $progressBar->finish();
        $this->release();

        return Command::SUCCESS;
    }
}
