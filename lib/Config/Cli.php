<?php
/**
 */

namespace Horde\GitTools\Config;

use Horde_Argv_Parser;
use Horde_Argv_Option;

/**
 */
class Cli extends \Components_Config_Base
{
    /**
     * The command line argument parser.
     *
     * @var Horde_Argv_Parser
     */
    protected $_parser;

    /**
     * Constructor.
     *
     */
    public function __construct(Horde_Argv_Parser $parser) {
        $this->_parser = $parser;

        $parser->addOption(
            new Horde_Argv_Option(
                '-q',
                '--quiet',
                array(
                    'action' => 'store_true',
                    'help'   => 'Reduce output to a minimum'
                )
            )
        );
        $parser->addOption(
            new Horde_Argv_Option(
                '-v',
                '--verbose',
                array(
                    'action' => 'store_true',
                    'help'   => 'Raise output to a maximum'
                )
            )
        );
        $parser->addOption(
            new Horde_Argv_Option(
                '-P',
                '--pretend',
                array(
                    'action' => 'store_true',
                    'help'   => 'Just pretend and indicate what would be done rather than performing the action.',
                )
            )
        );
        $parser->addOption(
            new Horde_Argv_Option(
                '-N',
                '--nocolor',
                array(
                    'action' => 'store_true',
                    'help'   => 'Avoid colors in the output'
                )
            )
        );
        $parser->addOption(
            new Horde_Argv_Option(
                '-c',
                '--config',
                array(
                    'action' => 'store',
                    'help'   => sprintf(
                        'the path to the configuration file for the components script (default : %s).',
                        __DIR__ . '/../../config/conf.php'
                    ),
                    'default' => __DIR__ . '/../../config/conf.php'
                )
            )
        );
        $parser->addOption(
            new Horde_Argv_Option(
                '',
                '--git_base',
                array(
                    'action' => 'store',
                    'help'   => 'Path the base directory containing git checkouts.',
                )
            )
        );
        list($this->_options, $this->_arguments) = $this->_parser->parseArgs();
    }
}