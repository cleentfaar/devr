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
	}
}