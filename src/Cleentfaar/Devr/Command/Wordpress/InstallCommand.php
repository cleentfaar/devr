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

    private $supportedVersions = array(
        'en' => array(),
        'nl' => array(
            '3.5.2',
            '3.6',
            '3.6.1',
        ),
    );

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
            'version',
            'V',
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
            'cache',
            'c',
            InputOption::VALUE_NONE,
            'Use this option to find an existing version of a Wordpress archive in DEVR\'s cache, before trying to downloa a new one. This can be useful for recurring tasks'
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
        $output->writeln('<comment>Installing new Wordpress package into target directory: ' . $targetDirectory . '</comment>');
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

        $wordpressZipPath = $this->downloadLatestWordpressZip($version, $locale, $input, $output);

        if ($wordpressZipPath === null) {
            return $this->cancel("Failed to download wordpress archive");
        }
        $extracted = $this->extractZip($wordpressZipPath, $targetDirectory);
        if (!$input->getOption('cache')) {
            $this->filesystem->remove($wordpressZipPath);
            $output->writeln('<comment>Removed downloaded archive since the --cache option was not used</comment>');
        }
        if (!$extracted) {
            return $this->cancel("Failed to extract wordpress archive");
        }
        $output->writeln('<comment>The Wordpress archive was successfully extracted to target directory: ' . $targetDirectory . '</comment>');
    }

    /**
     * @param \ZipArchive $zip
     * @param $targetDirectory
     * @return bool
     */
    private function extractZip($wordpressZipPath, $targetDirectory)
    {
        $wordpressZip = new \ZipArchive();
        $res = $wordpressZip->open($wordpressZipPath);
        if ($res !== true) {
            return $this->cancel("Failed to open wordpress archive");
        }
        $temporaryDir = DEVR_CACHE_DIR . uniqid();
        $temporarySubDir = $temporaryDir . DIRECTORY_SEPARATOR . 'wordpress';
        $wordpressZip->extractTo($temporaryDir);
        $wordpressZip->close();
        if (strtolower(substr(PHP_OS, 0, 3)) == 'win') {
            /**
             * Windows seems to have a nasty delay in it's file locking that can cause the following rename to fail
             */
            sleep(2);
        }
        $this->filesystem->rename($temporarySubDir, $targetDirectory);
        $this->filesystem->remove($temporaryDir);
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
                return 'http://nl.wordpress.org/wordpress-' . $version . '-nl_NL.zip';
            case 'en':
                return 'http://wordpress.org/wordpress-' . $version . '.zip';
        }
    }

    /**
     * @param $version
     * @param string $locale
     * @param OutputInterface $output
     * @return null|\ZipArchive
     */
    private function downloadLatestWordpressZip($version, $locale = 'en', InputInterface $input, OutputInterface $output)
    {
        if (!empty($this->supportedVersions[$locale]) && !in_array($version, $this->supportedVersions[$locale])) {
            return $this->cancel("The given version ($version) is not available for download with this locale ($locale)");
        }
        $wordpressZipUrl = $this->getWordpressZipUrl($version, $locale);
        $destination = DEVR_CACHE_DIR . basename($wordpressZipUrl);
        if ($input->getOption('cache') && $this->filesystem->exists($destination)) {
            $output->writeln('<comment>Re-using a previously downloaded archive since the --cache option was used</comment>');
            return $destination;
        }
        $output->writeln('<comment>Downloading wordpress from ' . $wordpressZipUrl . ' to ' . $destination . '</comment>');
        $browser = new Browser();
        $response = $browser->get($wordpressZipUrl);
        if (file_put_contents($destination, $response->getContent()) > 0) {
            $output->writeln('<comment>Archive was successfully downloaded</comment>');
            return $destination;
        }
        return null;
    }
}
