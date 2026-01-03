<?php
// Afficher les infos PHP pour comprendre la config OVH
header('Content-Type: text/plain; charset=utf-8');

echo "=== INFORMATIONS PHP ===\n\n";

echo "PHP Version: " . PHP_VERSION . "\n";
echo "PHP SAPI: " . PHP_SAPI . "\n";
echo "PHP_BINARY: " . PHP_BINARY . "\n";
echo "PHP_BINDIR: " . PHP_BINDIR . "\n\n";

echo "=== VARIABLES D'ENVIRONNEMENT ===\n\n";
echo "PATH: " . getenv('PATH') . "\n\n";

echo "=== TEST COMMANDES ===\n\n";

// Test which php
exec('which php 2>&1', $output1, $ret1);
echo "which php: (code $ret1)\n";
echo implode("\n", $output1) . "\n\n";

// Test whereis php
exec('whereis php 2>&1', $output2, $ret2);
echo "whereis php: (code $ret2)\n";
echo implode("\n", $output2) . "\n\n";

// Test php --version
exec('php --version 2>&1', $output3, $ret3);
echo "php --version: (code $ret3)\n";
echo implode("\n", $output3) . "\n\n";

// Test /usr/bin/php
exec('/usr/bin/php --version 2>&1', $output4, $ret4);
echo "/usr/bin/php --version: (code $ret4)\n";
echo implode("\n", $output4) . "\n\n";

// Lister /usr/bin/php*
exec('ls -la /usr/bin/php* 2>&1', $output5, $ret5);
echo "ls /usr/bin/php*: (code $ret5)\n";
echo implode("\n", $output5) . "\n\n";

// Lister /opt/alt/php*/usr/bin/php
exec('ls -la /opt/alt/php*/usr/bin/php 2>&1', $output6, $ret6);
echo "ls /opt/alt/php*/usr/bin/php: (code $ret6)\n";
echo implode("\n", $output6) . "\n\n";

echo "=== SOLUTION POSSIBLE ===\n\n";
echo "Si aucun PHP CLI n'est trouvé, on peut:\n";
echo "1. Utiliser le script PHP actuel pour charger Drupal\n";
echo "2. Appeler drupal_flush_all_caches() directement\n";
echo "3. Contacter OVH pour activer PHP CLI\n";

