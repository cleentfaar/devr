<?php
/**
 * This file is part of the DEVR CLI-script
 *
 * @author Cas Leentfaar
 * @license http://github.com/cleentfaar/devr
 */
namespace Cleentfaar\Devr\Command;

use Buzz\Browser;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ComposerInstallCommand
 * @package Cleentfaar\Devr\Command
 */
class ComposerInstallCommand extends Command
{

	private $composerUrl = 'http://getcomposer.org/composer.phar';
	
	/**
	 * @see \Symfony\Component\Console\Command\Command::configure()
	 */
    protected function configure()
    {
        $this->setName('composer:install');
        $this->setDescription('Installs composer in the working directory');
        $this->addOption(
			'auto-install',
        	'a',
        	InputOption::VALUE_NONE,
        	'Use this to let composer automatically install any dependencies found in composer.json after the installation succeeded'
		);
        $this->addOption(
			'force',
        	'f',
        	InputOption::VALUE_NONE,
        	'Use this to overwrite any available composer.phar with the latest version downloaded from '.$this->composerUrl
		);
    }

    /**
     * @see \Symfony\Component\Console\Command\Command::execute($input, $output)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	$autoInstall = false;
    	$force = false;
    	if ($input->getOption('auto-install')) {
    		$autoInstall = true;
    	}
    	if ($input->getOption('force')) {
    		$force = true;
    	}
    	if ($force == false && $this->isComposerAvailable()) {
        	$output->writeln('<comment>The composer.phar has already been downloaded to this directory, use --force to overwrite this version</comment>');
            return 1;
    	} else {
    		$this->downloadComposer($input, $output);
    	}
        if ($autoInstall === true) {
        	$this->installDependencies($input, $output);
        } else {
        	$output->writeln('<comment>The auto-install option was not used: you will need to run "php composer.phar install" manually!</comment>');
        }
    	$output->writeln('<comment>Installation finished successfully!</comment>');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param bool $composerInstalledGlobally
     * @throws \RuntimeException
     */
    private function installDependencies(InputInterface $input, OutputInterface $output, $composerInstalledGlobally = false)
    {
        if (!$this->composerJsonExists()) {
            $output->writeln('<comment>There is no composer.json in the working directory, auto-install has no effect</comment>');
            return false;
        }
    	$action = 'install';
    	if ($this->composerLockExists()) {
    		$action = 'update';
    	}
    	if ($composerInstalledGlobally === true) {
    		$command = 'composer';
    	} else {
    		if (!$this->isComposerAvailable() && !$this->downloadComposer($input, $output)) {
    			throw new \RuntimeException("Failed to download composer");
    		}
    		$command = 'composer.phar';
    	}
    	$command = 'php '.$command.' '.$action;

    	$output->writeln('<comment>Executing composer '.$action.' command: '.$command.'</comment>');
    	exec($command, $execOutput);
    	foreach ($execOutput as $line) {
    		$output->writeln("\t".$line);
    	}
    }

    /**
     * @return bool
     */
    private function composerJsonExists()
    {
        return file_exists($this->getComposerJsonPath());
    }

    /**
     * @return bool
     */
    private function composerLockExists()
    {
    	return file_exists($this->getComposerLockPath());
    }

    /**
     * @return bool
     */
    private function isComposerAvailable()
    {
    	return file_exists($this->getComposerPharPath());
    }

    /**
     * @return string
     */
    private function getComposerJsonPath()
    {
        return getcwd() . '/composer.json';
    }

    /**
     * @return string
     */
    private function getComposerLockPath()
    {
    	return getcwd() . '/composer.lock';
    }

    /**
     * @return string
     */
    private function getComposerPharPath()
    {
    	return getcwd() . '/composer.phar';
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     */
    private function downloadComposer(InputInterface $input, OutputInterface $output)
    {
    	$configuration = $this->getApplication()->getConfiguration();
        $composerUrl = $configuration['composer.download_url'];
    	$destination = $this->getComposerPharPath();
    	$output->writeln('<comment>Downloading composer from '.$composerUrl.' to '.$destination.'</comment>');
    	$browser = new Browser();
    	$response = $browser->get($composerUrl);
    	if (file_put_contents($destination, $response->getContent()) > 0) {
    		return true;
    	}
    	return false;
    }
}
