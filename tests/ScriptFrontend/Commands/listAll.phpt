--TEST--
PEAR2_Pyrus_ScriptFrontend_Commands::listAll()
--FILE--
<?php
/**
 * Test a dependency tree like so:
 *
 * P1 -> P2 >= 1.2.0 (1.2.3 is latest version)
 *
 * P2 1.2.3 -> P3
 *          -> P5
 *
 * P2 1.2.2 -> P3
 *
 * P3
 *
 * P4 -> P2 != 1.2.3
 *
 * P5
 *
 * This causes a conflict when P1 and P4 are installed that must resolve to installing:
 *
 * P1
 * P2 1.2.2
 * P3
 * P4
 */

define('MYDIR', __DIR__);
include __DIR__ . '/setup.php.inc';
require __DIR__ . '/../../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../../Mocks/Internet/installer.prepare.dep.versionconflict',
                       'http://pear2.php.net/');
PEAR2_Pyrus::$downloadClass = 'Internet';
PEAR2_Pyrus_Installer::begin();
PEAR2_Pyrus_Installer::prepare(new PEAR2_Pyrus_Package('pear2/P1-1.0.0'));
PEAR2_Pyrus_Installer::prepare(new PEAR2_Pyrus_Package('pear2/P4-stable', true));
PEAR2_Pyrus_Installer::commit();

ob_start();
$cli = new PEAR2_Pyrus_ScriptFrontend_Commands(true);
$cli->run($args = array ('remote-list', 'pear2.php.net'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . __DIR__ . DIRECTORY_SEPARATOR . 'testit' . "\n" .
'Remote packages for channel pear2.php.net:
Category 1:
*P1 1.0.0 testing
*P3 1.0.0 testing
 P5 0.9.0 testing
Key: * = installed, ! = upgrades available
Category 2:
!P2 1.2.3 testing
*P4 1.0.0 testing
Key: * = installed, ! = upgrades available
', $contents, 'output');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===