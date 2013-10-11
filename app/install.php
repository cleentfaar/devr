<?php
function devr_write($message = "")
{
    echo $message;
}

function devr_writeln($message = "")
{
    echo $message . "\n";
}

function devr_execute($command)
{
    return exec($command);
}

/**
 * Install Composer dependencies
 */
$composerCommand = "composer install";
devr_writeln("DEVR has not been installed yet, doing so now...");
devr_writeln("Installing Composer dependencies with command: $composerCommand");
devr_execute("cd " . DEVR_ROOT_DIR . " " . DEVR_ARGUMENT_SEPARATOR . " $composerCommand");
devr_writeln("Dependencies have been installed where necessary");
require_once(DEVR_AUTOLOAD_FILE);

/**
 * Install a default configuration to be used by the commands
 */

use Cleentfaar\Devr\Console\Application;

$application = new Application();
$application->add(new \Cleentfaar\Devr\Command\Devr\InstallCommand());
$command = $application->find('devr:install');
$arguments = array(
    'command' => 'devr:install',
);
$input = new \Symfony\Component\Console\Input\ArrayInput($arguments);
$command->run($input, new Symfony\Component\Console\Output\ConsoleOutput());
