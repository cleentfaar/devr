<?php
/**
 * This file is part of the DEVR CLI-script
 *
 * @author Cas Leentfaar
 * @license http://github.com/cleentfaar/devr
 */
namespace Cleentfaar\Devr\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CreateProjectCommand
 * @package Cleentfaar\Devr\Command
 */
class CreateProjectCommand extends Command
{

    /**
     * The path to the skeleton directory to use for projects (directories under a client)
     * @var string
     */
    private $projectSkeletonDir = '/path/to/project/skeleton/dir';

    /**
     * The path to the root directory where all clients (and their projects) are
     * @var string
     */
    private $projectsDir = '/path/to/projects';

    /**
     * The path to the git home directory, where all (shared and bare) repositories reside
     * @var string
     */
    private $gitHomeDir = '/path/to/repositories';

    /**
     * The (relative) path of a project where it's repository will be cloned into
     * @var string
     */
    private $gitCloneDir = 'project/git'; // relative to projectdir

    /**
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('create:project')
            ->setDescription('Creates a new project for a client')
            ->addOption(
                'dry-run',
                'd',
                InputOption::VALUE_NONE,
                'Use this to only show what would happen, without writing to any files'
            );
    }

    /**
     * @see \Symfony\Component\Console\Command\Command::execute($input, $output)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dryRun = false;
        if ($input->getOption('dry-run')) {
            $dryRun = true;
        }
        $interactive = true;
        if ($input->getOption('no-interaction')) {
            $interactive = false;
        }
        if ($interactive === false) {
            $output->writeln('<error>Currently not supporting non-interactive mode, coming soon!</error>');
            return;
        }

        $client = $this->getDialog()->ask($output, '<question>Which client would you like to create a project for?:</question> ');
        $validator = Validation::createValidator();
        $errors = $validator->validateValue($client, new Assert\NotBlank());
        if (count($errors)) {
            $output->writeln('<error>Client cannot be empty, project creation aborted</error>');
            return;
        }

        $project = $this->getDialog()->ask($output, '<question>What is the name of the project you would like to create?:</question> ');
        $validator = Validation::createValidator();
        $errors = $validator->validateValue($project, new Assert\NotBlank());
        if (count($errors)) {
            $output->writeln('<error>Projectname cannot be empty, project creation aborted</error>');
            return;
        }

        $projectsDirectory = $this->getProjectsDir();
        $output->writeln('');

        $confirm = $this->getDialog()->ask($output, '<question>The client will be added to the following directory: ' . $projectsDirectory . ', proceed?</question> ');
        $allowedAnswers = array("", "y", "yes");
        if (!in_array($confirm, $allowedAnswers)) {
            $output->writeln('<error>Cancelled, project creation aborted</error>');
            return;
        }
        $projectDir = $this->createProjectDir($input, $output, $client, $project, $dryRun);

        $createGit = $this->getDialog()->ask($output, '<question>Would you like to create a git repository for this project?:</question> ');
        $allowedAnswers = array("", "y", "yes");
        if (in_array($createGit, $allowedAnswers)) {
            $this->createGitRepository($input, $output, $projectDir, $dryRun);
        }

        $createDatabase = $this->getDialog()->ask($output, '<question>Would you like to create a database for this project?:</question> ');
        $allowedAnswers = array("y", "yes");
        if (in_array($createGit, $allowedAnswers)) {
            $this->createDatabase($input, $output, $projectDir, $dryRun);
        }
    }

    private function getDatabaseConnection($driver, $hostname, $username, $password)
    {
        switch ($driver) {
            case 'pdo_mysql':
            default:
                break;
        }
        $db = new \PDO($driver);
    }

    private function createDatabase(InputInterface $input, OutputInterface $output, $project, $dryRun = true)
    {
        $driver = $this->getDialog()->ask($output, '<question>Please select the driver to use for the database connection (default is "pdo_mysql")</question> ');
        if ($driver == '') {
            $driver = 'pdo_mysql';
        }

        $hostname = $this->getDialog()->ask($output, '<question>Please set the hostname to use for the database connection (default is "localhost")</question> ');
        if ($hostname == '') {
            $hostname = 'localhost';
        }

        $user = $this->getDialog()->ask($output, '<question>Please set the username to use for the database connection (default is "root")</question> ');
        if ($user == '') {
            $user = 'root';
        }

        $password = $this->getDialog()->ask($output, '<question>Please set the password to use for the database connection (default is left empty)</question> ');

        $db = $this->getDatabaseConnection($driver, $hostname, $user, $password);
        $db->execute($query);
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
        if (!$this->clientDirExists($client)) {
            $clientDir = $this->createClientDir($input, $output, $client, $dryRun);
        }

        $projectDir = $clientDir . '/' . $project;

        $useSkeleton = $this->getDialog()->ask($output, '<question>Would you like to use the skeleton-directory for creating a project within this client (default is yes)?</question> ');
        $allowedAnswers = array("", "y", "yes");
        $filesystem = new Filesystem();
        if (in_array($useSkeleton, $allowedAnswers)) {
            $projectSkeletonDir = $this->projectSkeletonDir;
            if (!is_dir($projectSkeletonDir)) {
                throw new \RuntimeException('The skeleton directory to copy does not exist (' . $projectSkeletonDir . ')');
            }
            if ($dryRun == false) {
                $filesystem->mkdir($projectDir);
                $filesystem->mirror($projectSkeletonDir, $projectDir);
                chmod($projectDir, 0777);
            }
            $output->writeln("<comment>Created projectdirectory using skeleton in $projectDir</comment>");
        } else {
            $output->writeln("<comment>Creating projectdirectory with simple directory in $projectDir</comment>");
            if ($dryRun == false) {
                $filesystem->mkdir($projectDir);
                chmod($projectDir, 0777);
            }
        }
        return $projectDir;
    }

    /**
     * Creates a git repository for a given project directory
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

        $cloneDir = $this->getDialog()->ask($output, '<question>Please enter a name for the cloned directory in this project (default is ' . $this->gitCloneDir . ')?</question> ');
        if ($cloneDir == '') {
            $cloneDir = $this->gitCloneDir;
        }

        $gitHomeDir = $this->getGitHomeDir();

        $output->writeln("<comment>Creating git repository in " . $gitHomeDir . "</comment>");
        $command = 'mkdir ' . $gitHomeDir . '/' . $repoName . '.git; cd ' . $gitHomeDir . '/' . $repoName . '.git; git init --shared --bare';
        if ($dryRun == false) {
            ob_start();
            exec($command);
            ob_end_clean();
            chmod($gitHomeDir . '/' . $repoName, 0777);
        }
        $output->writeln("<comment>Executed command for init: " . $command . "</comment>");

        $output->writeln("<comment>Cloning git repository into " . $projectDir . '/' . $cloneDir . "</comment>");
        $command = 'git clone ' . $gitHomeDir . '/' . $repoName . '.git ' . $projectDir . '/' . $cloneDir;
        if ($dryRun == false) {
            ob_start();
            exec($command);
            ob_end_clean();
            chmod($projectDir . '/' . $cloneDir, 0777);
        }
        $output->writeln("<comment>Executed command for clone: " . $command . "</comment>");

    }

    /**
     * Checks if the given client directory exists
     *
     * @param unknown_type $client
     * @return boolean
     */
    private function clientDirExists($client)
    {
        $clientDir = $this->getProjectsDir() . '/' . $client;
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
        $clientDir = $this->getProjectsDir() . '/' . $client;
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
     * Returns the projects root directory, where all client folders are (with their project subfolders)
     *
     * @return string
     */
    private function getProjectsDir()
    {
        return $this->projectsDir;
    }
}