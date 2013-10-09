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
class ConfigListCommand extends Command
{
    /**
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this->setName('config:list');
        $this->setDescription('Lists all key/value pairs as defined in the configuration');
    }

    /**
     * @see \Symfony\Component\Console\Command\Command::execute($input, $output)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configuration = $this->getApplication()->getConfiguration();
        $formattedList = $this->formatConfiguration($configuration, $this->findLongestKeyLength($configuration));
        $output->writeln("\n" . $formattedList);
        return 1;
    }
    private function findLongestKeyLength(array $data)
    {
        $longestKeyLength = 0;
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $currentLength = $this->findLongestKeyLength($value);
            } else {
                $currentLength = strlen($value);
            }
            $longestKeyLength = $currentLength > $longestKeyLength ? $currentLength : $longestKeyLength;
        }
        return $longestKeyLength;
    }
    private function formatConfiguration(array $configuration, $longestKeyLength = 50, $depth = 0)
    {
        $output = "";
        foreach ($configuration as $key => $value) {
            if (is_array($value)) {
                $keyPadded = str_pad($key,$longestKeyLength + (4 * ($depth + 1)),' ',STR_PAD_RIGHT);
                $valueFormatted = "\n" . $this->formatConfiguration($value, $longestKeyLength, $depth + 1);
            } else {
                $keyPadded = str_pad($key,$longestKeyLength,'.',STR_PAD_RIGHT) . ": ";
                $valueFormatted = $value;
                if (is_bool($valueFormatted)) {
                    $valueFormatted = $value == true ? 'true' : 'false';
                } elseif (is_string($valueFormatted)) {
                    $valueFormatted = '"'.$valueFormatted.'"';
                }
            }
            $tabString = "";
            for ($x = 0; $x <= $depth; $x++) {
                $tabString .= "\t";
            }
            $output .= $tabString . $keyPadded . $valueFormatted . "\n";
        }
        return $output;
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
