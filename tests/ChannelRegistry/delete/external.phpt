--TEST--
PEAR2_Pyrus_ChannelRegistry::delete() delete external channel
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = PEAR2_Pyrus_Config::singleton(__DIR__.'/testit');
restore_include_path();
$c->saveConfig();
$chan = new PEAR2_Pyrus_Channel(file_get_contents(dirname(__DIR__).'/sample_channel.xml'));
$c->channelregistry->add($chan);
$test->assertEquals(true, $c->channelregistry->exists('pear.unl.edu'), 'successfully added the channel');
$chan = $c->channelregistry->get('pear.unl.edu');
$c->channelregistry->delete($chan);
$test->assertEquals(false, $c->channelregistry->exists('pear.unl.edu'), 'successfully deleted');

?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===