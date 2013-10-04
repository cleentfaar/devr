<?php
/**
 * This file is part of the DEVR CLI-script
 *
 * @author Cas Leentfaar
 * @license http://github.com/cleentfaar/devr
 */
namespace Cleentfaar\Devr\Console;

use Cleentfaar\Devr\Command\ConfigGetCommand;
use Cleentfaar\Devr\Command\ConfigListCommand;
use Cleentfaar\Devr\Command\ConfigSetCommand;
use Cleentfaar\Devr\Command\InstallComposerCommand;
use Cleentfaar\Devr\Command\CreateProjectCommand;
use Cleentfaar\Devr\Config\Loader\DatabaseLoader;
use Symfony\Component\Console\Application as BaseApplication;

/**
 * Class Application
 * @package Cleentfaar\Console
 */
class Application extends BaseApplication {

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
    public function __construct(DatabaseLoader $configurationLoader = null) {
        if ($configurationLoader === null) {
            $configurationLoader = new DatabaseLoader();
        }
        $this->configurationLoader = $configurationLoader;
        parent::__construct($configurationLoader->get('application.name'), $configurationLoader->get('application.version'));
	}

    /**
     * @return array|\Symfony\Component\Console\Command\Command[]
     */
    public function getDefaultCommands() {
        $commands = parent::getDefaultCommands();

        $commands[] = new ConfigListCommand();
        $commands[] = new ConfigGetCommand();
        $commands[] = new ConfigSetCommand();
        $commands[] = new InstallComposerCommand();
        $commands[] = new CreateProjectCommand();

        return $commands;
    }

    public function saveConfiguration(array $configuration = null)
    {
        if ($configuration === null) {
            $configuration = $this->getConfiguration();
        }
        return $this->configurationLoader->save($configuration);
    }

    public function getConfiguration()
    {
        return $this->configurationLoader->getAll();
    }
}
