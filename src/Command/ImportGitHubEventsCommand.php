<?php

declare(strict_types=1);

namespace App\Command;

use App\GithubEvent\GitHubEventImporterInterface;
use App\Util\DateTimeRangeParser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressIndicator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:import-github-events', 'Import GH events')]
class ImportGitHubEventsCommand extends Command
{
    public function __construct(
        private readonly DateTimeRangeParser $dateTimeRangeParser,
        private readonly GitHubEventImporterInterface $eventImporter,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('date', InputArgument::REQUIRED, 'Import events within this specific date ranges')
            ->setHelp(
                <<<HELP
Allowed date formats are: Y-m-d | Y-m-d-G | Y-m-d-{G..G}
Eg:
    2024-01-01
    2024-01-01-0
    2024-01-01-{0..23}
HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $dates = $this->dateTimeRangeParser->parse($input->getArgument('date'));
        $counter = 0;
        foreach ($dates as $date) {
            $formattedDate = $date->format('d/m/Y G\h');
            $message = sprintf('<comment>Importing %s : %%u...</comment>', $formattedDate);

            $progressIndicator = new ProgressIndicator($output, 'very_verbose');
            $progressIndicator->start(sprintf($message, 0));

            $processedEvents = $this->eventImporter->import($date, static function (int $processedEvents) use ($progressIndicator, $message) {
                $progressIndicator->advance();
                $progressIndicator->setMessage(sprintf($message, $processedEvents));
            });

            $progressIndicator->finish(sprintf('<comment>Importing %s : %u events imported</comment>', $formattedDate, $processedEvents));

            $counter += $processedEvents;
        }

        $io->success(sprintf('%u events imported !', $counter));

        return 0;
    }
}
