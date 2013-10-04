<?php
/**
 * This file is part of the DEVR CLI-script
 *
 * @author Cas Leentfaar
 * @license http://github.com/cleentfaar/devgen
 */
use Composer\Autoload\ClassLoader;

/**
 * Used for safely referencing the root directory of the DEVR installation
 */
define("DEVR_ROOT_DIR", realpath(__DIR__ . '/../'));

require_once(__DIR__ . "/../vendor/autoload.php");