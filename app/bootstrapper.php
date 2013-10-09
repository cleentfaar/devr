<?php
/**
 * This file is part of the DEVR CLI-script
 *
 * @author Cas Leentfaar
 * @license http://github.com/cleentfaar/devr
 */
use Composer\Autoload\ClassLoader;

/**
 * Used for safely referencing the root directory of the DEVR installation
 */
define("DEVR_ROOT_DIR", __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);

$autoloadPath = DEVR_ROOT_DIR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";
if (!file_exists($autoloadPath)) {
    die("DEVR must be installed first, use composer to install the composer.json found in ".dirname(__DIR__));
}

require_once($autoloadPath);