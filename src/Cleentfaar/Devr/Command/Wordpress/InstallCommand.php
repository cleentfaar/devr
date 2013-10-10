<?php
/**
 * This file is part of the DEVR CLI-script
 *
 * @author Cas Leentfaar
 * @license http://github.com/cleentfaar/devr
 */
namespace Cleentfaar\Devr\Command\Wordpress;

use Buzz\Browser;
use Cleentfaar\Devr\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class InstallCommand
 * @package Cleentfaar\Devr\Command\Wordpress
 */
class InstallCommand extends Command
{

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->filesystem = new Filesystem();
    }

    /**
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this->setName('wordpress:install');
        $this->setDescription('Creates a new Wordpress installation in the target location, installing any default plugins defined under the configuration\'s \'wordpress.default_plugins\' key');
        $this->addArgument(
            'target',
            InputArgument::REQUIRED,
            'The target directory to use for installing Wordpress'
        );
        $this->addOption(
            'wordpress-version',
            'wv',
            InputOption::VALUE_REQUIRED,
            'The Wordpress version to use for this installation, this overrides the value stored under the configuration\'s \'wordpress.default_version\'-key'
        );
        $this->addOption(
            'locale',
            'l',
            InputOption::VALUE_REQUIRED,
            'The initial locale to use for this installation, this gives you a pre-configured wordpress for your locale, and overrides the value stored under the configuration\'s \'wordpress.default_locale\'-key'
        );
        $this->addOption(
            'extra-plugins',
            'e',
            InputOption::VALUE_REQUIRED,
            'Comma-separated string of plugins to additionally install for this specific instance, these are appended to the configuration\'s wordpress.default_plugins that will already be installed. Specific plugin versions can be chosen by appending a semicolon to the plugin\'s name followed by the version number, without this suffix the latest version will be selected'
        );
        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'Use this option if you would like to overwrite the target directory if it already exists (use with caution!)'
        );
    }

    /**
     * @see \Symfony\Component\Console\Command\Command::execute($input, $output)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $targetDirectory = $input->getArgument('target');
        $version = $input->getOption('version');
        if (!$version) {
            $version = $this->getConfiguration('wordpress.default_version');
        }
        $locale = $input->getOption('locale');
        if (!$locale) {
            $locale = $this->getConfiguration('wordpress.default_locale');
        }
        $force = $input->getOption('force');
        if ($this->filesystem->exists($targetDirectory)) {
            if ($force) {
                $this->filesystem->remove($targetDirectory);
                $output->writeln('<comment>Removed existing target directory (--force was used)</comment>');
            } else {
                return $this->cancel("Target directory already exists, use the --force option to ignore this warning and overwrite the contents");
            }
        }

        $wordpressZip = $this->downloadLatestWordpressZip($version, $locale, $output);

        if ($wordpressZip === null) {
            return $this->cancel("Failed to download wordpress archive");
        }
        if (!$this->extractZip($wordpressZip, $targetDirectory)) {
            return $this->cancel("Failed to extract wordpress archive");
        }
        $output->writeln('<comment>Archive was successfully extracted to target directory: '.$targetDirectory.'</comment>');
        $this->filesystem->remove($wordpressZip->filename);
        $output->writeln('<comment>Archive was successfully removed from temporary directory</comment>');
    }

    /**
     * @param \ZipArchive $zip
     * @param $targetDirectory
     * @return bool
     */
    private function extractZip(\ZipArchive $zip, $targetDirectory)
    {
        $temporaryDir = DEVR_CACHE_DIR . uniqid();
        $zip->extractTo($targetDirectory);
        $zip->close();
        $this->filesystem->rename($temporaryDir . DIRECTORY_SEPARATOR . 'wordpress', $targetDirectory);
        return true;
    }

    /**
     * @param $version
     * @param string $locale
     * @return string
     */
    private function getWordpressZipUrl($version, $locale = 'en')
    {
        switch ($locale) {
            case 'nl':
                return 'http://nl.wordpress.org/wordpress-'.$version.'-nl_NL.zip';
            default:
                return 'http://wordpress.org/wordpress-'.$version.'.zip';
                break;
        }
    }

    /**
     * @param $version
     * @param string $locale
     * @param OutputInterface $output
     * @return null|\ZipArchive
     */
    private function downloadLatestWordpressZip($version, $locale = 'en', OutputInterface $output)
    {
        $wordpressZipUrl = $this->getWordpressZipUrl($version, $locale);
        $destination = DEVR_CACHE_DIR . basename($wordpressZipUrl);
        if ($this->filesystem->exists($destination)) {
            $zip = new \ZipArchive();
            $res = $zip->open($destination);
            if ($res !== true) {
                return null;
            }
            return $zip;
        }
        $output->writeln('<comment>Downloading wordpress from ' . $wordpressZipUrl . ' to ' . $destination . '</comment>');
        $browser = new Browser();
        $response = $browser->get($wordpressZipUrl);
        if (file_put_contents($destination, $response->getContent()) > 0) {
            $output->writeln('<comment>Archive was successfully downloaded</comment>');
            $zip = new \ZipArchive();
            $res = $zip->open($destination);
            if ($res !== true) {
                return null;
            }
            return $zip;
        }
        return null;
    }
}