<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:import-github-events', 'Import GH events')]
class ImportGitHubEventsCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Let's rock !
        // It's up to you now

        return 1;
    }
}
