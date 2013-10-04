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
use Symfony\Component\Yaml\Yaml;

/**
 * Class ConfigGetCommand
 * @package Cleentfaar\Devr\Command
 */
class ConfigGetCommand extends Command
{
	/**
	 * @see \Symfony\Component\Console\Command\Command::configure()
	 */
    protected function configure()
    {
        $this->setName('config:get');
        $this->setDescription('Returns the value for a given key in the configuration');
        $this->addArgument(
            'key',
            InputArgument::REQUIRED,
            'The name of the key to get the value for'
        );
    }

    /**
     * @see \Symfony\Component\Console\Command\Command::execute($input, $output)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configuration = $this->getApplication()->getConfiguration();
        $key = $input->getArgument('key');
        $value = null;
        if (!isset($configuration[$key]) && stristr($key,'.')) {
            $parts = explode('.',$key);
            $base = $configuration;
            $x = 1;
            foreach ($parts as $part) {
                if (!isset($base[$part])) {
                    $output->writeln("There is no subkey with the name '$part' defined in the configuration");
                    return 0;
                }
                if ($x == count($parts)) {
                    $value = $base[$part];
                    break;
                }
                if (is_array($base[$part])) {
                    $base = $base[$part];
                } else {
                    $output->writeln("There is no array defined under the key '$part' in the configuration");
                    return 0;
                }
                $x++;
            }
        } elseif (!isset($configuration[$key])) {
            $output->writeln("There is no key with the name '$key' defined in the configuration");
            return 0;
        } else {
            $value = $configuration[$key];
        }
        $output->writeln("Value of key '$key' is: $value");
        return 1;
    }
}