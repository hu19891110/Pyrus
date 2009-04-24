--TEST--
PEAR2_Pyrus_ChannelRegistry::listChannels() default channels
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/../setup.php.inc';
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = PEAR2_Pyrus_Config::singleton(__DIR__.'/testit');
restore_include_path();
$c->saveConfig();

$chans = $c->channelregistry->listChannels();
sort($chans);

$test->assertEquals(array(
    '__uri',
    'pear.php.net',
    'pear2.php.net',
    'pecl.php.net',
), $chans, 'listChannels');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===