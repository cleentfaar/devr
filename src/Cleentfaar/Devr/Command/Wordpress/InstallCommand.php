<?php
/**
 * This file is part of the DEVR CLI-script
 *
 * @author Cas Leentfaar
 * @license http://github.com/cleentfaar/devr
 */
namespace Cleentfaar\Devr\Command\Wordpress;

use Cleentfaar\Devr\Command\Command;
use Guzzle\Http\Client;
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

    /**
     * @var array
     */
    private $supportedVersions = array(
        'en' => array(),
        'nl' => array(
            '3.5.2',
            '3.6',
            '3.6.1',
        ),
    );

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
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
            'plugin',
            'p',
            InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
            "Can be used once or more in a command to install certain plugins after installing Wordpress itself, besides the ones defined in the configuration\'s wordpress.default_plugins that will already be installed.\n" .
            "You can append a semicolon followed by a version number to each plugin to indicate a specific version to download, for example: devr wordpress:install /path/to/target --plugin=foo:1.5 --plugin=bar:1.8"
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
        $this->processWordpress($targetDirectory, $input, $output);
        $this->processPlugins($targetDirectory, $input, $output);
    }

    /**
     * @param $targetDirectory
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \RuntimeException
     */
    private function processWordpress($targetDirectory, InputInterface $input, OutputInterface $output)
    {
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
                throw new \RuntimeException("Target directory already exists, use the --force option to ignore this warning and overwrite the contents");
            }
        }
        $wordpressZipPath = $this->downloadWordpress($version, $locale, $input, $output);

        if ($wordpressZipPath === null) {
            throw new \RuntimeException("Failed to download wordpress archive");
        }
        $extracted = $this->installWordpress($wordpressZipPath, $targetDirectory);
        if (!$input->getOption('cache')) {
            $this->filesystem->remove($wordpressZipPath);
            $output->writeln('<comment>Removed downloaded archive since the --cache option was not used</comment>');
        }
        if (!$extracted) {
            throw new \RuntimeException("Failed to extract wordpress archive");
        }
        $output->writeln('<comment>The Wordpress archive was successfully extracted to target directory: ' . $targetDirectory . '</comment>');
    }

    /**
     * @param $targetDirectory
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     */
    private function processPlugins($targetDirectory, InputInterface $input, OutputInterface $output)
    {
        $plugins = array();
        $defaultPlugins = $this->getConfiguration('wordpress.default_plugins');
        if ($defaultPlugins != '') {
            $plugins = explode(' ', $defaultPlugins);
        }
        if ($extraPlugins = $input->getOption('plugin')) {
            $plugins = array_merge($plugins, $extraPlugins);
        }
        $wordpressPluginDirectory = realpath($targetDirectory . DIRECTORY_SEPARATOR . 'wp-content' . DIRECTORY_SEPARATOR . 'plugins');

        if (empty($plugins)) {
            $output->writeln("<comment>No plugins to install</comment>");
            return false;
        } else {
            $output->writeln("<comment>Installing plugins: " . implode(", ", $plugins) . "</comment>");
            foreach ($plugins as $plugin) {
                if (stristr($plugin, ':')) {
                    $parts = explode(':', $plugin);
                    $plugin = $parts[0];
                    $version = $parts[1];
                } else {
                    $version = null;
                }
                $zipLocation = $this->downloadPlugin($plugin, $version, $wordpressPluginDirectory, $input, $output);
                $this->installPlugin($zipLocation, $wordpressPluginDirectory, $output);
            }
            $output->writeln("<comment>Plugins were installed successfully</comment>");
            return true;
        }
    }

    /**
     * @param $name
     * @param $wordpressPluginDirectory
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string|void
     */
    private function downloadPlugin($name, $version, $wordpressPluginDirectory, InputInterface $input, OutputInterface $output)
    {
        try {
            $nameAndVersionTag = $name . ($version != '' ? '.' . $version : '');
            $pluginUrl = 'http://downloads.wordpress.org/plugin/' . $nameAndVersionTag . '.zip';
            $zipLocation = $wordpressPluginDirectory . DIRECTORY_SEPARATOR . basename($pluginUrl);
            return $this->downloadZip($pluginUrl, $zipLocation);
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to download plugin from URL: $pluginUrl", $e->getCode(), $e);
        }
    }

    /**
     * @param $zipLocation
     * @param $pluginsDir
     * @return bool|void
     */
    private function installPlugin($zipLocation, $pluginsDir, OutputInterface $output)
    {
        return $this->extractZip($zipLocation, $pluginsDir, true);
    }

    /**
     * @param \ZipArchive $zip
     * @param $targetDirectory
     * @return bool
     */
    private function installWordpress($wordpressZipPath, $targetDirectory)
    {
        return $this->extractZip($wordpressZipPath, $targetDirectory, true, 'wordpress');
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
    private function downloadWordpress($version, $locale = 'en', InputInterface $input, OutputInterface $output)
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
        return $this->downloadZip($wordpressZipUrl, $destination);
    }

    /**
     * @param $zipLocation
     * @param $destination
     * @param bool $removeAfterwards
     * @param null $subDir
     * @return bool|void
     */
    private function extractZip($zipLocation, $destination, $removeAfterwards = false, $subDir = null)
    {
        $wordpressZip = new \ZipArchive();
        $res = $wordpressZip->open($zipLocation);
        if ($res !== true) {
            return $this->cancel("Failed to open archive ($zipLocation)");
        }
        if ($subDir !== null) {
            $temporaryDir = DEVR_CACHE_DIR . uniqid();
            $temporarySubDir = $temporaryDir . DIRECTORY_SEPARATOR . 'wordpress';
            $wordpressZip->extractTo($temporaryDir);
            $wordpressZip->close();
            if (strtolower(substr(PHP_OS, 0, 3)) == 'win') {
                /**
                 * Windows seems to have a nasty delay in it's file locking that can cause the following rename to fail
                 */
                sleep(1);
            }
            $this->filesystem->rename($temporarySubDir, $destination);
            $this->filesystem->remove($temporaryDir);
        } else {
            $extractionDir = $destination;
            $wordpressZip->extractTo($destination);
            $wordpressZip->close();
        }
        if ($removeAfterwards == true) {
            $this->filesystem->remove($zipLocation);
        }
        return true;
    }

    /**
     * @param $url
     * @param $destination
     * @return mixed
     * @throws \RuntimeException
     */
    private function downloadZip($url, $destination)
    {
        try {
            $client = new Client();
            $request = $client->get($url);
            $request->setResponseBody($destination);
            $request->send();
            return $destination;
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to download zip from URL: $url", $e->getCode(), $e);
        }
    }
}
