<?php
/**
 * Copyright 2017 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl.
 *
 * @author   Michael J Rubinsky <mrubinsk@horde.org>
 * @author   Michael Slusarz <slusarz@horde.org>
 * @category Horde
 * @license  http://www.horde.org/licenses/lgpl LGPL
 * @package  GitTools
 */

namespace Horde\GitTools\Action;
use Horde_Cli;

/**
 * Links the framework libraries into the web directory.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @author    Michael Slusarz <slusarz@horde.org>
 * @copyright 2017 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl LGPL
 * @package   GitTools
 */
class LinkFramework extends Base
{
    /**
     *
     */
    public function run()
    {
        $horde_git = rtrim(ltrim($this->_params['git_base']), '/ ');
        $web_dir = rtrim(ltrim($this->_params['web_dir']), '/ ');
        $this->_linkFramework($horde_git, $web_dir);
    }

    /**
     * Perform the linking of the libraries in '/libs'.
     *
     * @param  string $horde_git  Path to the local base directory of all
     *                            repositories.
     * @param  string $web_dir    Path to the web accessible directory.
     * @todo  Allow overriding the '/libs' target?
     * @todo  This also assumes that the 'Horde_Cli' library is available either
     *        in the include_path, or locally checked out in $horde_git. Do
     *        we need to change this?
     */
    protected function _linkFramework($horde_git, $web_dir)
    {
        $destDir = $web_dir . DIRECTORY_SEPARATOR . '/libs';

        // Put $destDir into include_path.
        if (strpos(ini_get('include_path'), $destDir) === false) {
            ini_set('include_path', $destDir . PATH_SEPARATOR . ini_get('include_path'));
        }

        // Do CLI checks and environment setup first.
        // @todo: Anyway to be more flexible with this?
        if ((!@include_once 'Horde/Cli.php')  &&
            (!@include_once $horde_git . '/Cli/lib/Horde/Cli.php')) {
            print_usage('Horde_Cli library is not in the include_path or in the src directory.');
        }

        // Make sure no one runs this from the web.
        if (!Horde_Cli::runningFromCLI()) {
            exit;
        }

        // Load the CLI environment - make sure there's no time limit, init
        // some variables, etc.
        $cli = Horde_Cli::init();

        // Need to override the error handler Horde_Cli uses, since we can't
        // assume we have a functioning autoloader at this point and
        // Horde_Cli::fatal() relies on one.
        set_exception_handler(array($this, 'fatal'));

        $cli->message('Source directory: ' . $horde_git);
        $cli->message('Framework destination directory: ' . $destDir);
        $cli->message('Horde directory: ' . $web_dir);
        $cli->message('Create symbolic links: ' . (!empty($this->_params['copy']) ? 'NO' : 'Yes'));

        $pkg_ob = new \Horde\GitTools\PEAR\Package\Parse();
        $pkgs = $pkg_ob->getPackages(array($horde_git));

        $cli->writeLn();
        $cli->message('Package(s) to install: ' . ((count($pkgs) === 1) ? reset($pkgs) : 'ALL (' . count($pkgs) . ' packages)'));

        foreach ($pkgs as $key => $val) {
            if ($this->_params['debug']) {
                $cli->writeLn();
            }
            $cli->message('Installing package ' . $key);

            $pkg = $pkg_ob->pear_pkg->fromPackageFile($val . '/package.xml', 0);
            if ($pkg instanceof PEAR_Error) {
                $cli->message('Could not install package ' . $key . ': ' . $pkg->getMessage(), 'cli.error');
                continue;
            }
            foreach ($pkg->getInstallationFilelist() as $file) {
                if (!isset($file['attribs']['name'])) {
                    $cli->message('Invalid <install> entry: ' . print_r($file['attribs'], true), 'cli.error');
                    continue;
                }
                $orig = realpath($val . '/' . $file['attribs']['name']);
                if (empty($orig)) {
                    $cli->message('Install file does not seem to exist: ' . $val . '/' . $file['attribs']['name'], 'cli.error');
                    continue;
                }

                switch ($file['attribs']['role']) {
                case 'horde':
                    if (isset($file['attribs']['install-as'])) {
                        $dest = $web_dir . '/' . $file['attribs']['install-as'];
                    } else {
                        $cli->message('Could not determine install directory (role "horde") for ' . $web_dir, 'cli.error');
                        continue;
                    }
                    break;

                case 'php':
                    if (isset($file['attribs']['install-as'])) {
                        $dest = $destDir . '/' . $file['attribs']['install-as'];
                    } elseif (isset($file['attribs']['baseinstalldir'])) {
                        $dest = $destDir . $file['attribs']['baseinstalldir'] . '/' . $file['attribs']['name'];
                    } else {
                        $dest = $destDir . '/' . $file['attribs']['name'];
                    }
                    break;

                default:
                    $dest = null;
                    break;
                }

                if (!is_null($dest)) {
                    if (!$dryrun && file_exists($dest)) {
                        @unlink($dest);
                    } elseif (!$dryrun && !file_exists(dirname($dest))) {
                        @mkdir(dirname($dest), 0777, true);
                    }

                    if ($copy) {
                        if ($this->_params['debug']) {
                            print 'COPY: ' . $orig . ' -> ' . $dest . "\n";
                        }
                        if (!$dryrun && !copy($orig, $dest)) {
                            $cli->message('Could not link ' . $orig . '.', 'cli.error');
                        }
                    } else {
                        if ($this->_params['debug']) {
                            print 'SYMLINK: ' . $orig . ' -> ' . $dest . "\n";
                        }
                        if (!$dryrun && !symlink($orig, $dest)) {
                            $cli->message('Could not link ' . $orig . '.', 'cli.error');
                        }
                    }
                }
            }
        }
    }

}
