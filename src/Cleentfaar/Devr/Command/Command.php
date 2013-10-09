<?php
/**
 * This file is part of the DEVR CLI-script
 *
 * @author Cas Leentfaar
 * @license http://github.com/cleentfaar/devr
 */
namespace Cleentfaar\Devr\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;

/**
 * Class Command
 * @package Cleentfaar\Devr\Command
 */
class Command extends BaseCommand
{

    /**
     * Returns a dialog helper to use for interactive questions on the command-line
     * @return \Symfony\Component\Console\Helper\HelperInterface
     */
    protected function getDialog()
    {
        return $this->getHelperSet()->get('dialog');
    }

    /**
     * @param $message
     * @throws \RuntimeException
     */
    protected function cancel($message)
    {
        throw new \RuntimeException($message);
    }

    /**
     * @param null $key
     * @return mixed
     * @throws \RuntimeException
     */
    protected function getConfiguration($key = null)
    {
        $configuration = $this->getApplication()->getConfiguration();
        if ($key !== null) {
            if (!isset($configuration[$key])) {
                throw new \RuntimeException("Key '$key' does not exist in configuration");
            }
            return $configuration[$key];
        }
        return $configuration;
    }

    /**
     * @param $key
     * @return bool
     */
    protected function configurationExists($key)
    {
        $configuration = $this->getConfiguration();
        return isset($configuration[$key]) ? true : false;
    }
}
