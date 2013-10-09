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
 * Class ProjectCreateCommandTest
 * @package Cleentfaar\Devr\Tests\Command
 */
class ProjectCreateCommandTest extends \PHPUnit_Framework_TestCase
{
	public function testExecute()
	{
		$application = new Application();

		$command = $application->find('project:create');
		$commandTester = new CommandTester($command);
		$commandTester->execute(
			array(
				'--dry-run'			=> true,
				'--no-interaction' 	=> true,
			)
		);

		$output = $commandTester->getDisplay();

		$this->assertRegExp('/Currently not supporting non-interactive mode/', $output);
	}
}
