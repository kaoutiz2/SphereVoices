<?php
// Script de diagnostic ultra-simple
error_reporting(E_ALL);
ini_set('display_errors', 1);
echo "<h1>Quick Check</h1><pre>";
echo "PHP Version: " . phpversion() . "\n";
echo "DocumentRoot: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n";
echo "Script path: " . __FILE__ . "\n";
echo "www/ exists: " . (is_dir(dirname(__FILE__)) ? 'YES' : 'NO') . "\n";
echo "index.php exists: " . (file_exists(dirname(__FILE__) . '/index.php') ? 'YES' : 'NO') . "\n";
$root = dirname(dirname(__FILE__));
echo "Root: $root\n";
echo "vendor/ exists: " . (is_dir($root . '/vendor') ? 'YES' : 'NO') . "\n";
echo "settings.php exists: " . (file_exists(dirname(__FILE__) . '/sites/default/settings.php') ? 'YES' : 'NO') . "\n";
echo "</pre>";
?>
