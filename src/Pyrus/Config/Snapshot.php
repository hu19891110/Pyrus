<?php
/**
 * PEAR2_Pyrus_Config
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */

/**
 * Pyrus's master configuration manager
 *
 * Unlike PEAR version 1.x, the new Pyrus configuration manager is tightly bound
 * to include_path, and will search through include_path for system configuration
 * Pyrus installations.
 *
 * The User configuration file will be looked for in these locations:
 *
 * Unix:
 *
 * - home directory
 * - current directory
 *
 * Windows:
 *
 * - local settings directory on windows for the current user.
 *   This is looked up directly in the windows registry using COM
 * - current directory
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Config_Snapshot extends PEAR2_Pyrus_Config
{
    /**
     * parse a configuration for a PEAR2 installation
     *
     * @param string $pearDirectory This can be either a single path, or a
     *                              PATH_SEPARATOR-separated list of directories
     * @param string $userfile
     */
    public function __construct($snapshot, PEAR2_Pyrus_Config $config = null)
    {
        self::constructDefaults();
        if (!$config) {
            $config = PEAR2_Pyrus_Config::current();
        }
        $this->loadConfigFile($config->path, $snapshot);
        $this->pearDir = $config->path;
    }

    /**
     * Extract configuration from system + user configuration files
     *
     * Configuration is stored in XML format, in two locations.
     *
     * The system configuration contains all of the important directory
     * configuration variables like data_dir, and the location of php.ini and
     * the php executable php.exe or php.  This configuration is tightly bound
     * to the repository, and cannot be moved.  As such, php_dir is auto-defined
     * as dirname(/path/to/pear/.config), or /path/to/pear.
     *
     * Only 1 user configuration file is allowed, and contains user-specific
     * settings, including the locations where to download package releases
     * and where to cache files downloaded from the internet.  If false is passed
     * in, PEAR2_Pyrus_Config will attempt to guess at the config file location as
     * documented in the class docblock {@link PEAR2_Pyrus_Config}.
     * @param string $pearDirectory
     * @param string|false $userfile
     */
    protected function loadConfigFile($pearDirectory, $snapshot = null)
    {
        if (!file_exists($pearDirectory . DIRECTORY_SEPARATOR . '.configsnapshots')) {
            // no configurations - this may be an extracted-from-disk install.
            // in this case, we use the defaults, as this is intended
            return;
        }
        $snapshotdir = $pearDirectory . DIRECTORY_SEPARATOR . '.configsnapshots';
        $snapshotfile = $snapshotdir . DIRECTORY_SEPARATOR . $snapshot;
        if (!file_exists($snapshotfile)) {
            if (preg_match('/^\\d{4}\\-\\d{2}\\-\\d{2} \\d{2}:\\d{2}:\\d{2}$/', $snapshot)) {
                // passed a date, locate a matching snapshot
                $us = new DateTime($snapshot);
                $dir = new RegexIterator(
                    new RecursiveDirectoryIterator($snapshotdir), '/configsnapshot\\-\\d{4}\\-\\d{2}\\-\\d{2} \\d{2}:\\d{2}:\\d{2}.xml/',
                    RegexIterator::MATCH,
                    RegexIterator::USE_KEY);
                foreach ($dir as $match) {
                    $matches[] = $match;
                }
                usort($matches, array($this, 'datediff'));
                unset($match);
                foreach ($matches as $match) {
                    $match = substr($match->getFileName(), strlen('configsnapshot-'));
                    $match = str_replace('.xml', '', $match);
                    $diff = $us->diff(new DateTime($match))->format("%r%s");
                    if (!$diff) {
                        // found a snapshot match
                        break;
                    }
                    if (!isset($last)) {
                        if ($diff > 0) {
                            // oldest snapshot is newer than us, resort to default config
                            return parent::loadConfigFile($pearDirectory);
                        }
                        $last = $match;
                        continue;
                    }

                    if ($diff < 0) {
                        $last = $match;
                        continue;
                    }
                    // the current snapshot is newer than our install
                    // the last snapshot was the one we used
                    $match = $last;
                    break;
                }
                if (!isset($match)) {
                    // no config snapshots
                    return parent::loadConfigFile($pearDirectory);
                }
                $snapshotfile = $snapshotdir . DIRECTORY_SEPARATOR . 'configsnapshot-' .
                    $match . '.xml';
            }
            if (!file_exists($snapshotfile)) {
                throw new PEAR2_Pyrus_Config_Exception('Cannot retrieve non-existent config ' .
                                                   'snapshot ' . $snapshot);
            }
        }

        PEAR2_Pyrus_Log::log(5, 'Loading configuration snapshot ' .
                             $snapshotfile . ' for ' . $pearDirectory);
        try {
            $this->helperLoadConfigFile($pearDirectory, $snapshotfile, 'snapshot');
        } catch (Exception $e) {
            // no config snapshots found, so simply load the existing config
            return parent::loadConfigFile($pearDirectory);
        }
    }

    function datediff($a, $b)
    {
        $us = new DateTime($a);
        $diff = $us->diff(new DateTime($match))->format("%r%s");
        if (!$diff) return 0;
        if ($diff > 0) return 1;
        return -1;
    }

    /**
     * Save both the user configuration file and the system file
     *
     * If the userfile is not passed in, it is saved in the default
     * location which is either in ~/.pear/pearconfig.xml or on Windows
     * in the Documents and Settings directory
     * @param string $userfile path to alternate user configuration file
     */
    function saveConfig($userfile = false)
    {
    }

    /**
     * Save a snapshot of the current config, and return the file name
     *
     * If the latest snapshot is the same as the existing configuration,
     * simply return the filename
     * @return string basename of the snapshot file of the current configuration
     */
    static public function configSnapshot()
    {
    }
}
