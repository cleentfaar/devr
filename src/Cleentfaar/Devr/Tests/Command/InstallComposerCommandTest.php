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
 * Class InstallComposerCommandTest
 * @package Cleentfaar\Devr\Tests\Command
 */
class InstallComposerCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
	{
		$application = new Application();

		$command = $application->find('install:composer');
		$commandTester = new CommandTester($command);
		$commandTester->execute(
			array(
				'--force'  			=> true,
				'--no-interaction' 	=> true,
			)
		);
		
		$output = $commandTester->getDisplay();

		$this->assertRegExp('/Downloading composer/', $output);
		$this->assertRegExp('/Installation finished successfully/', $output);
	}
}