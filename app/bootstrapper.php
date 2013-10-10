<?php
/**
 * This file is part of the DEVR CLI-script
 *
 * @author Cas Leentfaar
 * @license http://github.com/cleentfaar/devr
 */

/**
 * Used for safely referencing the root directory of the DEVR installation
 */
define("DEVR_ROOT_DIR", __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
define("DEVR_VENDOR_DIR", DEVR_ROOT_DIR . "vendor" . DIRECTORY_SEPARATOR);
define("DEVR_CACHE_DIR", DEVR_ROOT_DIR . 'app' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR);

/**
 * Here is a simple check to see if dependencies have been installed, and to stop if not
 */
$autoloadPath = DEVR_VENDOR_DIR . "autoload.php";
if (!file_exists($autoloadPath)) {
    die("DEVR must be installed first, use composer to install the composer.json found in " . dirname(__DIR__));
}

/**
 * From this point on, it is assumed DEVR is set-up correctly and we can begin autoloading
 */
require_once($autoloadPath);