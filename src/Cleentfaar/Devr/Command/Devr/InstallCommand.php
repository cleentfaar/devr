<?php
/**
 * This file is part of the DEVR CLI-script
 *
 * @author Cas Leentfaar
 * @license http://github.com/cleentfaar/devr
 */
namespace Cleentfaar\Devr\Command\Devr;

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
 * Class InstallCommand
 * @package Cleentfaar\Devr\Command\Devr
 */
class InstallCommand extends Command
{

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->filesystem = new Filesystem();
    }

    /**
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this->setName('devr:install');
        $this->setDescription('This command prepares DEVR dependencies and configuration for further use');
    }

    /**
     * @see \Symfony\Component\Console\Command\Command::execute($input, $output)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Creating database for configuration");
        $configuration = $this->getConfiguration();

        $output->writeln("Inserting default values into configuration");
        $configuration['composer.download_url'] = 'http://getcomposer.org/composer.phar';
        $configuration['application.hierarchy'] = 'clients -> client -> project';

        if (defined("DEVR_TEST_MODE")) {
            $configuration['application.name'] = 'DEVR';
            $configuration['application.version'] = '0.1';
            $configuration['projects.clients_dir'] = '';
            $configuration['projects.relative_repository_dir'] = '';
            $configuration['projects.skeleton_dir'] = '';
            $configuration['git.home_dir'] = '';
            $configuration['wordpress.default_version'] = '3.6';
            $configuration['wordpress.default_locale'] = 'en';
        } else {

            /**
             * Optional values
             */
            $name = $this->getDialog()->ask($output, '<question>Would you like to give your own name (brand) to the DEVR CLI-script? (default is \'DEVR\')</question> ');
            if ($name == '') {
                $name = 'DEVR';
            }
            $configuration['application.name'] = $name;

            $version = $this->getDialog()->ask($output, '<question>Would you like to give your own version to the DEVR CLI-script? (default is \'1.0\')</question> ');
            if ($version == '') {
                $version = '1.0';
            }
            $configuration['application.version'] = $version;

            /**
             * Required values for some commands
             */
            $clientsDir = $this->getDialog()->ask($output, '<question>Would you like to indicate a root directory for all your clients? (leave empty to ignore for now)</question> ');
            $configuration['projects.clients_dir'] = (string)$clientsDir;

            $skeletonDir = $this->getDialog()->ask($output, '<question>Would you like to indicate a skeleton project directory to use? (leave empty to ignore for now)</question> ');
            $configuration['projects.skeleton_dir'] = (string)$skeletonDir;

            $gitHomeDir = $this->getDialog()->ask($output, '<question>Would you like to indicate a home directory for git repositories? (leave empty to ignore for now)</question> ');
            $configuration['git.home_dir'] = (string)$gitHomeDir;

            $relativeRepositoryDir = $this->getDialog()->ask($output, '<question>Would you like to indicate a relative directory for a project\'s repository? (leave empty to ignore for now)</question> ');
            $configuration['projects.relative_repository_dir'] = (string)$relativeRepositoryDir;

            $wordpressVersion = $this->getDialog()->ask($output, '<question>Would you like to indicate a default version to use for Wordpress installations? (default is \'3.6\')</question> ');
            $configuration['wordpress.default_version'] = $wordpressVersion !== null ? $wordpressVersion : '3.6';

            $wordpressLocale = $this->getDialog()->ask($output, '<question>Would you like to indicate a default locale to use for Wordpress installations? (default is \'en\')</question> ');
            $configuration['wordpress.default_locale'] = $wordpressLocale !== null ? $wordpressLocale : 'en';

            $wordpressPlugins = $this->getDialog()->ask($output, '<question>Would you like to indicate some default plugins to install after each Wordpress installation? (must be space separated value)</question> ');
            $configuration['wordpress.default_plugins'] = $wordpressPlugins !== null ? $wordpressPlugins : '';
        }
        $this->getApplication()->saveConfiguration($configuration);
    }
}
