<?php
/**
 * This file is part of the DEVR CLI-script
 *
 * @author Cas Leentfaar
 * @license http://github.com/cleentfaar/devr
 */
namespace Cleentfaar\Devr\Console;

use Cleentfaar\Devr\Command;
use Cleentfaar\Devr\Config\Loader\DatabaseLoader;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class Application
 * @package Cleentfaar\Console
 */
class Application extends BaseApplication
{

    /**
     * The indentation level used for writing to the configuration file
     * @see http://symfony.com/doc/current/components/yaml/introduction.html#writing-yaml-files
     */
    const CONFIGURATION_REPRESENTATION = 3;

    /**
     * @var \Cleentfaar\Devr\Config\Loader\DatabaseLoader
     */
    private $configurationLoader;

    /**
     * @param DatabaseLoader $configurationLoader
     */
    public function __construct(DatabaseLoader $configurationLoader = null)
    {
        if ($configurationLoader === null) {
            $configurationLoader = new DatabaseLoader();
        }
        $this->configurationLoader = $configurationLoader;
        parent::__construct($configurationLoader->get('application.name'), $configurationLoader->get('application.version'));
    }

    /**
     * Gets the default input definition.
     *
     * @return InputDefinition An InputDefinition instance
     */
    protected function getDefaultInputDefinition()
    {
        return new InputDefinition(array(
            new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'),

            new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display this help message.'),
            new InputOption('--quiet', '-q', InputOption::VALUE_NONE, 'Do not output any message.'),
            new InputOption('--verbose', '-v|vv|vvv', InputOption::VALUE_OPTIONAL, 'Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug'),
            //new InputOption('--version',        '-V', InputOption::VALUE_NONE, 'Display this application version.'),
            new InputOption('--ansi', '', InputOption::VALUE_NONE, 'Force ANSI output.'),
            new InputOption('--no-ansi', '', InputOption::VALUE_NONE, 'Disable ANSI output.'),
            new InputOption('--no-interaction', '-n', InputOption::VALUE_NONE, 'Do not ask any interactive question.'),
        ));
    }

    /**
     * @param array $configuration
     * @return bool
     */
    public function saveConfiguration(array $configuration = null)
    {
        if ($configuration === null) {
            $configuration = $this->getConfiguration();
        }
        return $this->configurationLoader->save($configuration);
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        return $this->configurationLoader->getAll();
    }
}
