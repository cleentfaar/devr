<?php
/**
 * This file is part of the DEVR CLI-script
 *
 * @author Cas Leentfaar
 * @license http://github.com/cleentfaar/devr
 */
namespace Cleentfaar\Devr\Tests\Command;

use Cleentfaar\Devr\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class ConfigSetCommandTest
 * @package Cleentfaar\Devr\Tests\Command
 */
class ConfigSetCommandTest extends \PHPUnit_Framework_TestCase
{
	public function testExecute()
	{
		$application = new Application();

		$command = $application->find('config:set');
		$commandTester = new CommandTester($command);
		$commandTester->execute(
			array(
				'key' => 'application.name',
				'value' => 'My Little Pony',
			)
		);
        $output = $commandTester->getDisplay();
        $this->assertRegExp('/Pony/', $output);

        /**
         * Test setting a key that does not exist yet
         */
        /**
         * Without the --force option (prevented), and with it (allowed)
         */
        $uniqueKey = uniqid();
        $commandTester->execute(
            array(
                'key' => $uniqueKey,
                'value' => 'My Little Pony',
                //'--force' => true,
            )
        );
        $output = $commandTester->getDisplay();
        $this->assertRegExp('/no key with the name/', $output);

        /**
         * With the --force option, this should be allowed
         */
        $commandTester->execute(
            array(
                'key' => $uniqueKey,
                'value' => 'My Little Pony',
                '--force' => true,
            )
        );
        $output = $commandTester->getDisplay();
        $this->assertRegExp('/Pony/', $output);
	}
}
