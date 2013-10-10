<?php
/**
 * This file is part of the DEVR CLI-script
 *
 * @author Cas Leentfaar
 * @license http://github.com/cleentfaar/devr
 */
namespace Cleentfaar\Devr\Tests\Command\Composer;

use Cleentfaar\Devr\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class InstallCommandTest
 * @package Cleentfaar\Devr\Tests\Command\Composer
 */
class InstallCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $application = new Application();

        $command = $application->find('composer:install');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                '--force' => true,
                '--auto-install' => true,
                '--no-interaction' => true,
            )
        );

        $output = $commandTester->getDisplay();

        $this->assertRegExp('/Downloading composer/', $output);
        $this->assertRegExp('/Installation finished successfully/', $output);
    }
}
