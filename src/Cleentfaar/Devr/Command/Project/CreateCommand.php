<?php
/**
 * This file is part of the DEVR CLI-script
 *
 * @author Cas Leentfaar
 * @license http://github.com/cleentfaar/devr
 */
namespace Cleentfaar\Devr\Command\Project;

use Cleentfaar\Devr\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CreateCommand
 * @package Cleentfaar\Devr\Command\Project
 */
class CreateCommand extends Command
{

    /**
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this->setName('project:create');
        $this->setDescription('Creates a new project for a client');
        $this->addArgument(
            'client',
            InputArgument::OPTIONAL,
            'The name of the client to create a project for (used in no-interaction mode)'
        );
        $this->addArgument(
            'project',
            InputArgument::OPTIONAL,
            'The name of the project for the given client (used in no-interaction mode)'
        );
        $this->addOption(
            'dry-run',
            'd',
            InputOption::VALUE_NONE,
            'Use this to only show what would happen, without writing to any files'
        );
        $this->addOption(
            'create-git',
            'g',
            InputOption::VALUE_NONE,
            'Flag to indicate the creation of a git repository for this project, this uses the configuration\'s \'git.home_dir\' key'
        );
        $this->addOption(
            'use-skeleton',
            's',
            InputOption::VALUE_NONE,
            'Flag to indicate the use of a skeleton directory as a base for the new project, this uses the configuration\'s \'project.skeleton_dir\' key'
        );
    }

    /**
     * @see \Symfony\Component\Console\Command\Command::execute($input, $output)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('no-interaction')) {
            list($client, $project) = $this->executeNonInteractively($input, $output);
        } else {
            list($client, $project) = $this->executeInteractively($input, $output);
        }

        $projectDir = $this->createProjectDir($input, $output, $client, $project, $input->getOption('dry-run'));

        if ($input->getOption('no-interaction')) {
            $createGit = $input->getOption('create-git') == true ? 'y' : 'n';
        } else {
            $createGit = $this->getDialog()->ask($output, '<question>Would you like to create a git repository for this project?:</question> ');
        }
        $allowedAnswers = array("", "y", "yes");
        if (in_array($createGit, $allowedAnswers)) {
            $this->createGitRepository($input, $output, $projectDir, $input->getOption('dry-run'));
        }
        /**
         * @todo coming soon...
         *
        $createDatabase = $this->getDialog()->ask($output, '<question>Would you like to create a database for this project?:</question> ');
        $allowedAnswers = array("y", "yes");
        if (in_array($createDatabase, $allowedAnswers)) {
        $this->createDatabase($input, $output, $projectDir, $input->getOption('dry-run'));
        }
         */
    }

    /**
     * @param InputInterface $input
     * @return array|void
     */
    private function executeNonInteractively(InputInterface $input, OutputInterface $output)
    {
        $client = $input->getArgument('client');
        if (!$client) {
            return $this->cancel('Client\'s name cannot be empty, project creation aborted');
        }
        $project = $input->getArgument('project');
        if (!$client) {
            return $this->cancel('Project\'s name cannot be empty, project creation aborted');
        }
        return array($client, $project);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array|void
     */
    private function executeInteractively(InputInterface $input, OutputInterface $output)
    {
        $client = $this->getDialog()->ask($output, '<question>Which client would you like to create a project for?:</question> ');
        $validator = Validation::createValidator();
        $errors = $validator->validateValue($client, new Assert\NotBlank());
        if (count($errors)) {
            return $this->cancel('Client\'s name cannot be empty, project creation aborted');
        }

        $project = $this->getDialog()->ask($output, '<question>What is the name of the project you would like to create?:</question> ');
        $validator = Validation::createValidator();
        $errors = $validator->validateValue($project, new Assert\NotBlank());
        if (count($errors)) {
            return $this->cancel('Project\'s name cannot be empty, project creation aborted');
        }

        return array($client, $project);

    }

    /**
     * Creates a project directory for a given client
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $client
     * @param string $project
     * @param bool $dryRun
     * @throws \RuntimeException
     * @return string
     */
    private function createProjectDir(InputInterface $input, OutputInterface $output, $client, $project, $dryRun = true)
    {
        $projectsDirectory = $this->getClientsDir();
        if (!$this->clientDirExists($client)) {
            if (!$input->getOption('no-interaction')) {
                $confirm = $this->getDialog()->ask($output, '<question>The client will be added to the following directory: ' . $projectsDirectory . ', proceed?</question> ');
                $allowedAnswers = array("", "y", "yes");
                if (!in_array($confirm, $allowedAnswers)) {
                    return $this->cancel('Cancelled, project creation aborted');
                }
            }
            $clientDir = $this->createClientDir($input, $output, $client, $dryRun);
        }

        $projectDir = $clientDir . '/' . $project;

        if ($input->getOption('no-interaction')) {
            $useSkeleton = $input->getOption('use-skeleton') == true ? 'y' : 'n';
        } else {
            $useSkeleton = $this->getDialog()->ask($output, '<question>Would you like to use the skeleton-directory for creating a project within this client (default is yes)?</question> ');
        }
        $allowedAnswers = array("", "y", "yes");
        $filesystem = new Filesystem();
        if (in_array($useSkeleton, $allowedAnswers)) {
            $projectSkeletonDir = $this->getProjectSkeletonDir();
            if (!is_dir($projectSkeletonDir)) {
                throw new \RuntimeException('The skeleton directory to copy does not exist (' . $projectSkeletonDir . ')');
            }
            if ($dryRun == false) {
                $filesystem->mkdir($projectDir);
                $filesystem->mirror($projectSkeletonDir, $projectDir);
                chmod($projectDir, 0777);
            }
            $output->writeln("<comment>Created project using skeleton in $projectDir</comment>");
        } else {
            $output->writeln("<comment>Created project as an empty directory in $projectDir</comment>");
            if ($dryRun == false) {
                $filesystem->mkdir($projectDir);
                chmod($projectDir, 0777);
            }
        }
        return $projectDir;
    }

    /**
     * Creates a git repository for a given project directory
     * This uses the git:create command internally
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $projectDir
     * @param bool $dryRun
     */
    private function createGitRepository(InputInterface $input, OutputInterface $output, $projectDir, $dryRun = true)
    {
        $defaultRepoName = str_replace(' ', '', strtolower(basename($projectDir)));
        $repoName = $this->getDialog()->ask($output, '<question>Please enter a name for the repository (without .git suffix, default is ' . $defaultRepoName . '):</question> ');
        if (!$repoName) {
            $repoName = $defaultRepoName;
        }

        $defaultCloneDir = $this->getDefaultCloneDir();
        $cloneDir = $this->getDialog()->ask($output, '<question>Please enter a name for the cloned directory in this project (default is ' . $defaultCloneDir . ')?</question> ');
        if ($cloneDir == '') {
            $cloneDir = $defaultCloneDir;
        }


        $command = $this->getApplication()->find('git:create');
        $arguments = array(
            //'--force' => true
            'name' => $repoName,
            '--clone-to' => $cloneDir
        );
        $input = new ArrayInput($arguments);
        $returnCode = $command->run($input, $output);

        if ($returnCode == 0) {
            return $this->cancel('Failed to create repository for this project');
        }
        $output->writeln('<comment>Repository was successfully created for this project</comment>');
        return true;
    }

    /**
     * Checks if the given client directory exists
     *
     * @param unknown_type $client
     * @return boolean
     */
    private function clientDirExists($client)
    {
        $clientDir = $this->getClientsDir() . '/' . $client;
        return is_dir($client);
    }

    /**
     * Creates a directory for the given client in the projects root directory
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $client
     * @param bool $dryRun
     * @return string
     */
    private function createClientDir(InputInterface $input, OutputInterface $output, $client, $dryRun = true)
    {
        $clientDir = $this->getClientsDir() . '/' . $client;
        if ($dryRun == false) {
            $filesystem = new Filesystem();
            $filesystem->mkdir($clientDir);
            chmod($clientDir, 0777);
        }
        $output->writeln('<comment>Created client directory in ' . $clientDir . '</comment>');
        return $clientDir;
    }

    /**
     * Returns the home directory of git (where all repositories reside)
     *
     * @return string
     */
    private function getGitHomeDir()
    {
        return $this->gitHomeDir;
    }

    /**
     * Returns the path to the project skeleton directory, to be used for new project folders
     *
     * @return string
     */
    private function getProjectSkeletonDir()
    {
        $configuration = $this->getApplication()->getConfiguration();
        if (!isset($configuration['projects.skeleton_dir'])) {
            return $this->cancel("No clients dir is set in the configuration, use 'devgen config:set projects.skeleton_dir PATH_TO_CLIENTS_DIR_HERE' to fix this");
        }
        return $configuration['projects.skeleton_dir'];
    }

    /**
     * Returns the clients directory, where all client folders are found as subfolders
     *
     * @return string
     */
    private function getClientsDir()
    {
        $configuration = $this->getApplication()->getConfiguration();
        if (!isset($configuration['projects.clients_dir'])) {
            return $this->cancel("No clients dir is set in the configuration, use 'devgen config:set projects.clients_dir PATH_TO_CLIENTS_DIR_HERE' to fix this");
        }
        return $configuration['projects.clients_dir'];
    }

    /**
     * Returns the default clone directory, being relative to the project's root directory
     *
     * @return string
     */
    private function getDefaultCloneDir()
    {
        $configuration = $this->getApplication()->getConfiguration();
        if (!isset($configuration['projects.default_clone_dir'])) {
            return $this->cancel("No clone dir is set in the configuration, use 'devgen config:set projects.default_clone_dir PATH_TO_CLIENTS_DIR_HERE' to fix this");
        }
        return $configuration['projects.default_clone_dir'];
    }
}
