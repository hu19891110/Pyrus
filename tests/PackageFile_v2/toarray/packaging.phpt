--TEST--
PackageFile v2: test package.xml toArray(), for packaging
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$c = getTestConfig();
require __DIR__ . '/../setupFiles/setupPackageFile.php.inc';
$reg = $package; // simulate registry package using packagefile
require __DIR__ . '/../../Registry/AllRegistries/package/extended/toarray.packaging.template';

?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===