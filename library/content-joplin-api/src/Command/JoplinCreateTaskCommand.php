<?php

declare(strict_types=1);

namespace App\Command;

use App\Enum\ProjectType;
use App\Enum\TaskTemplate;
use App\Service\JoplinService;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use ValueError;

use function sprintf;

#[AsCommand(
    name: 'app:joplin:create-task',
    description: 'Create a new task in Joplin',
)]
class JoplinCreateTaskCommand extends Command
{
    private const DEFAULT_FOLDER_ID = '045feac2008e4481bf3fdf5d87332cc4';

    public function __construct(
        private JoplinService $joplinService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('title', InputArgument::OPTIONAL, 'The title of the task')
            ->addArgument('template', InputArgument::OPTIONAL, 'The template to use for the task (empty, meeting, call, follow-up)')
            ->addOption('folder', 'f', InputOption::VALUE_OPTIONAL, 'The folder ID where to create the task', self::DEFAULT_FOLDER_ID)
            ->addOption('emoji', 'emo', InputOption::VALUE_OPTIONAL, 'The emoji to use for the task')
            ->addOption('project-type', 'p', InputOption::VALUE_OPTIONAL, 'The type of project to create')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$this->joplinService->ping()) {
            $io->error('Could not connect to Joplin. Make sure the Joplin desktop application is running and the Web Clipper is enabled.');

            return Command::FAILURE;
        }

        $title = $input->getArgument('title');
        if (!$title) {
            $title = $io->ask('Please enter the task title');
        }

        $templateName = $input->getArgument('template');
        if (!$templateName) {
            $templateName = $io->choice(
                'Select a template',
                array_map(static fn (TaskTemplate $template) => $template->value, TaskTemplate::cases()),
                TaskTemplate::EMPTY->value
            );
        }

        try {
            $template = TaskTemplate::from($templateName);
        } catch (ValueError $e) {
            $io->error(sprintf('Invalid template "%s". Please select a valid one.', $templateName));

            return Command::FAILURE;
        }

        $body = $template->getBody();

        $folderId = $input->getOption('folder');

        $projectTypeName = $input->getOption('project-type');
        if (!$projectTypeName) {
            $projectTypeName = $io->choice(
                'Select a project type',
                array_map(static fn (ProjectType $projectType) => $projectType->value, ProjectType::cases()),
                ProjectType::NONE->value
            );
        }

        try {
            $projectType = ProjectType::from($projectTypeName);
        } catch (ValueError $e) {
            $io->error(sprintf('Invalid project type "%s". Please select a valid one.', $projectTypeName));

            return Command::FAILURE;
        }

        try {
            $result = $this->joplinService->createNote(
                sprintf('%s', $title),
                $body,
                $folderId
            );

            $io->success(sprintf('Joplin Task created successfully with ID: %s', $result['id']));

            return Command::SUCCESS;
        } catch (Exception $e) {
            $io->error(sprintf('Failed to create task: %s', $e->getMessage()));

            return Command::FAILURE;
        }
    }
}
