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
define("DEVR_ROOT_DIR", realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
define("DEVR_VENDOR_DIR", DEVR_ROOT_DIR . "vendor" . DIRECTORY_SEPARATOR);
define("DEVR_CACHE_DIR", DEVR_ROOT_DIR . 'app' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR);
define("DEVR_AUTOLOAD_FILE", DEVR_VENDOR_DIR . "autoload.php");
define("DEVR_CONFIGURATION_FILE", DEVR_ROOT_DIR . "app" . DIRECTORY_SEPARATOR . "db" . DIRECTORY_SEPARATOR . "devr.sq3");
switch (strtolower(substr(PHP_OS, 0, 3))) {
    case 'win':
        define("DEVR_ARGUMENT_SEPARATOR", "&");
        break;
    default:
        define("DEVR_ARGUMENT_SEPARATOR", ";");
        break;
}

/**
 * Here is a simple check to see if dependencies have been installed, and to stop if not
 */
if (!file_exists(DEVR_AUTOLOAD_FILE) || !file_exists(DEVR_CONFIGURATION_FILE)) {
    /**
     * Use the installation script that makes the necessary changes before we can do any actual commands
     * NOTE: This script loads the autoload file itself when it's ready for it, allowing the rest of the sript to continue
     * after this installation. Thus we may assume that classes can be autoloaded after this
     */
    require_once("install.php");
} else {
    /**
     * From this point on, it is assumed DEVR is set-up correctly and we can begin autoloading
     */
    require_once(DEVR_AUTOLOAD_FILE);
}