<?php
/**
 * This file is part of the DEVR CLI-script
 *
 * @author Cas Leentfaar
 * @license http://github.com/cleentfaar/devr
 */
namespace Cleentfaar\Devr\Tests\Command\Config;

use Cleentfaar\Devr\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class ConfigGetCommandTest
 * @package Cleentfaar\Devr\Tests\Command
 */
class GetCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $application = new Application();
        $command = $application->find('config:get');
        $commandTester = new CommandTester($command);

        /**
         * Test retrieving a default key, this should give a real value that is defined initially
         */
        $commandTester->execute(
            array(
                'key' => 'application.name',
            )
        );
        $output = $commandTester->getDisplay();
        $this->assertRegExp('/DEVR/', $output);

        /**
         * Test retrieving an unexisting key, this should give us a proper error message
         */
        $uniqueKey = uniqid();
        $commandTester->execute(
            array(
                'key' => $uniqueKey,
            )
        );
        $output = $commandTester->getDisplay();
        $this->assertRegExp('/no key with the name/', $output);
    }
}
