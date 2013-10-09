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

/**
 * Class ConfigGetCommand
 * @package Cleentfaar\Devr\Command
 */
class CreateGitCommand extends Command
{

    /**
     * @var string
     */
    private $argumentSeparator = ';';

    /**
     * @see \Symfony\Component\Console\Command\Command::initialize()
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        switch (strtolower(substr(PHP_OS,0,3))) {
            case 'win':
                $this->argumentSeparator = '&';
                break;
            default:
                $this->argumentSeparator = ';';
                break;
        }
    }

    /**
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this->setName('create:git');
        $this->setDescription('Creates a git repository with the given name, optionally cloning it to the \'clone-to\' value');
        $this->addArgument(
            'name',
            InputArgument::REQUIRED,
            'The name of the repository to create'
        );
        $this->addOption(
            'dry-run',
            'd',
            InputOption::VALUE_NONE,
            'Use this to only show what would happen, without writing to any files'
        );
        $this->addOption(
            'clone-to',
            'c',
            InputOption::VALUE_REQUIRED,
            'An optional path to clone the repository to, after creating it'
        );
        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'Use this to overwrite a repository if it already exists. WARNING! This can be very dangerous!'
        );
    }

    /**
     * @see \Symfony\Component\Console\Command\Command::execute($input, $output)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configuration = $this->getApplication()->getConfiguration();
        $repoName = $input->getArgument('name');

        $gitHomeDir = isset($configuration['git.home_dir']) ? $configuration['git.home_dir'] : null;
        if (!$gitHomeDir) {
            return $this->cancel('<error>No home directory is configured for git repositories, execute \'devr config:set git.home_dir HOME_DIR_HERE\' to fix this</error>', $output);
        }
        $this->prepareStructure($gitHomeDir, $repoName, $input, $output);
        $this->handleClone($gitHomeDir, $repoName, $input, $output);
    }

    /**
     * @param $gitHomeDir
     * @param $repoName
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function prepareStructure($gitHomeDir, $repoName, InputInterface $input, OutputInterface $output)
    {
        $output->writeln("<comment>Creating git repository in " . $gitHomeDir . "</comment>");
        $filesystem = new Filesystem();
        $repoPath = $gitHomeDir . DIRECTORY_SEPARATOR . $repoName . '.git';
        $command = 'cd ' . $repoPath .  ' '.$this->argumentSeparator.' git init --shared --bare';
        if (!$filesystem->exists($gitHomeDir)) {
            if (!$input->getOption('dry-run')) {
                $filesystem->mkdir($gitHomeDir);
            }
            $output->writeln("<comment>Git home directory does not exist yet, created it now in $gitHomeDir</comment>");
        }
        if (!$input->getOption('dry-run')) {
            if (!$input->getOption('force') && $filesystem->exists($repoPath)) {
                return $this->cancel('<error>Can\'t create git repository here, the directory ('.$repoPath.') already exists, use the --force option to overwrite</error>', $output);
            }
            $repoPath = realpath($repoPath);
            $filesystem->mkdir($repoPath);
            ob_start();
            exec($command);
            ob_end_clean();
            $filesystem->chmod($repoPath, 0777, 0000, true);
        }
        $output->writeln("<comment>Executed command for init: " . $command . "</comment>");
    }

    /**
     * @param $gitHomeDir
     * @param $repoName
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function handleClone($gitHomeDir, $repoName, InputInterface $input, OutputInterface $output)
    {
        $cloneDir = $input->getOption('clone-to');
        if ($cloneDir != '') {
            $output->writeln("<comment>Cloning git repository into " . $cloneDir . "</comment>");
            $command = 'git clone ' . $gitHomeDir . DIRECTORY_SEPARATOR . $repoName . '.git ' . $cloneDir;
            if (!$input->getOption('dry-run')) {
                ob_start();
                exec($command);
                ob_end_clean();
                $filesystem = new Filesystem();
                $filesystem->chmod($cloneDir, 0777, 0000, true);
            }
            $output->writeln("<comment>Executed command for clone: " . $command . "</comment>");
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("<comment>Interacting...</comment>");
    }

}
