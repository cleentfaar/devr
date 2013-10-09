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
 * Class ConfigListCommandTest
 * @package Cleentfaar\Devr\Tests\Command
 */
class ConfigListCommandTest extends \PHPUnit_Framework_TestCase
{
	public function testExecute()
	{
		$application = new Application();

		$command = $application->find('config:list');
		$commandTester = new CommandTester($command);
		$commandTester->execute(array());

		$output = $commandTester->getDisplay();

		$this->assertRegExp('/application/', $output);
		$this->assertRegExp('/environment/', $output);
		$this->assertRegExp('/composer/', $output);
	}
}
