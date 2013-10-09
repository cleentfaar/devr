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

/**
 * Class ConfigGetCommand
 * @package Cleentfaar\Devr\Command
 */
class ConfigSetCommand extends Command
{
    /**
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this->setName('config:set');
        $this->setDescription('Changes the value for the given key');
        $this->addArgument(
            'key',
            InputArgument::REQUIRED,
            'The name of the key to get the value for'
        );
        $this->addArgument(
            'value',
            InputArgument::REQUIRED,
            'The new value for this key'
        );
        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'Use this option if you would like to create the key if it does not exist yet'
        );
    }

    /**
     * @see \Symfony\Component\Console\Command\Command::execute($input, $output)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configuration = $this->getApplication()->getConfiguration();
        $createIfNotExists = $input->getOption('force');
        $key = $input->getArgument('key');
        $value = $input->getArgument('value');
        $new = false;
        if (!isset($configuration[$key])) {
            if (stristr($key,'.')) {
                $this->setArrayFromString($configuration,$key,$value);
            } else {
                $new = true;
                if ($createIfNotExists == false) {
                    $output->writeln("There is no key with the name '$key' defined in the configuration");
                    return 0;
                }
            }
        } else {
            $configuration[$key] = $value;
        }
        $success = $this->getApplication()->saveConfiguration($configuration);
        if ($success === false) {
            $output->writeln("Failed to save the new configuration");
        }
        if ($new === true) {
            $output->writeln("New key '$key' was added with the following value: $value");
        } else {
            $output->writeln("Value of key '$key' was changed to: $value");
        }
        return 1;
    }

    private function setArrayFromString(&$array, $keys, $value) {
        $keys = explode(".", $keys);
        $current = &$array;
        foreach($keys as $key) {
            $current = &$current[$key];
        }
        $current = $value;
    }
}
