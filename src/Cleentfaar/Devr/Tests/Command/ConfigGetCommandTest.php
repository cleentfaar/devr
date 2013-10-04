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
 * Class ConfigGetCommandTest
 * @package Cleentfaar\Devr\Tests\Command
 */
class ConfigGetCommandTest extends \PHPUnit_Framework_TestCase
{
	public function testExecute()
	{
		$application = new Application();

		$command = $application->find('config:get');
		$commandTester = new CommandTester($command);
		$commandTester->execute(
			array(
				'key' => 'application.name',
			)
		);

		$output = $commandTester->getDisplay();

		$this->assertRegExp('/DEVR/', $output);
	}
}