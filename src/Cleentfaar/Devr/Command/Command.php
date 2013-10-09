<?php
/**
 * This file is part of the DEVR CLI-script
 *
 * @author Cas Leentfaar
 * @license http://github.com/cleentfaar/devr
 */
namespace Cleentfaar\Devr\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Output\OutputInterface;

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

    protected function cancel($message, OutputInterface $output)
    {
        $output->writeln($message);
        exit;
    }
}
