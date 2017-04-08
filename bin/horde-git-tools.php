<?php
/**
 * Copyright 2017 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl.
 *
 * @author   Michael J Rubinsky <mrubinsk@horde.org>
 * @category Horde
 * @license  https://www.horde.org/licenses/bsd BSD
 * @package  GitTools
 */

/**
 * Wrap call to Cli::main()
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @category  Horde
 * @copyright 2017 Horde LLC
 * @license   https://www.horde.org/licenses/bsd BSD
 * @package   GitTools
 */

$autoloader = dirname(__FILE__) . '/../vendor/autoload.php';
//  ini_set('include_path', '/opt/local/lib/php/' . PATH_SEPARATOR . ini_get('include_path'));
require_once 'PEAR/Config.php';
require_once 'PEAR/PackageFile.php';
if (!file_exists($autoloader)) {
    echo "You need to run \"composer install\" first.\nFor more information on composer see https://getcomposer.org/.\n";
    exit;
}

require_once $autoloader;
\Horde\GitTools\Cli::main();
