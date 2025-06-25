<?php

declare(strict_types=1);

namespace Alengo\Bundle\AlengoWebspaceSettingsBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand('webspace:settings:copy-config', 'Copy all necessary configuration files')]
class CopyConfigCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filesystem = new Filesystem();
        $projectDir = \getcwd();

        $files = [
            [
                'source' => __DIR__ . '/../../config/app/alengo_webspace_settings.yaml',
                'target' => $projectDir . '/config/packages/alengo_webspace_settings.yaml',
            ],
            [
                'source' => __DIR__ . '/../../config/app/webspace_settings_blocks.xml',
                'target' => $projectDir . '/config/templates/settings/webspace_settings_blocks.xml',
            ],
        ];

        foreach ($files as $file) {
            if (!$filesystem->exists($file['target'])) {
                $filesystem->copy($file['source'], $file['target']);
                $output->writeln(\sprintf('<info>Copied config %s</info>', $file['target']));
            } else {
                $output->writeln(\sprintf('<comment>Config file %s already exists.</comment>', $file['target']));
            }
        }

        return Command::SUCCESS;
    }
}
