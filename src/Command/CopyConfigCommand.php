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
            'config/packages/alengo_webspace_settings.yaml',
            'config/templates/settings/webspace_settings_blocks.xml',
        ];

        foreach ($files as $file) {
            $source = __DIR__ . '/../../config/app/' . $file;
            $target = $projectDir . '/' . $file;

            if (!$filesystem->exists($target)) {
                $filesystem->copy($source, $target);
                $output->writeln(\sprintf('<info>Copied config %s</info>', $file));
            } else {
                $output->writeln(\sprintf('<comment>Config file %s already exists.</comment>', $file));
            }
        }

        return Command::SUCCESS;
    }
}
