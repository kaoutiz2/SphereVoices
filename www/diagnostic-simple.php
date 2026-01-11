<?php
// Fichier de diagnostic ultra-simple
// URL: https://www.spherevoices.com/www/diagnostic-simple.php

header('Content-Type: text/plain; charset=utf-8');

echo "=== DIAGNOSTIC SIMPLE ===\n\n";

// 1. PHP fonctionne
echo "✅ PHP fonctionne !\n";
echo "Version PHP : " . PHP_VERSION . "\n\n";

// 2. Chemin actuel
echo "Répertoire actuel : " . __DIR__ . "\n";
echo "Fichier actuel : " . __FILE__ . "\n\n";

// 3. Vérifier si autoload existe
$autoload = __DIR__ . '/autoload.php';
if (file_exists($autoload)) {
    echo "✅ autoload.php existe\n";
} else {
    echo "❌ autoload.php INTROUVABLE\n";
    echo "Chemin cherché : $autoload\n";
}

// 4. Vérifier si sites/default/settings.php existe
$settings = __DIR__ . '/sites/default/settings.php';
if (file_exists($settings)) {
    echo "✅ settings.php existe\n";
    
    // Vérifier la syntaxe
    $output = [];
    $return = 0;
    exec("php -l " . escapeshellarg($settings) . " 2>&1", $output, $return);
    if ($return === 0) {
        echo "✅ settings.php syntaxe OK\n";
    } else {
        echo "❌ settings.php ERREUR DE SYNTAXE :\n";
        echo implode("\n", $output) . "\n";
    }
} else {
    echo "❌ settings.php INTROUVABLE\n";
}

echo "\n";

// 5. Lister les fichiers dans www/
echo "Fichiers dans www/ :\n";
$files = scandir(__DIR__);
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
        echo "  - $file\n";
    }
}

echo "\n";

// 6. Tester de charger Drupal
echo "=== TEST CHARGEMENT DRUPAL ===\n";
try {
    if (file_exists($autoload)) {
        require_once $autoload;
        echo "✅ Autoload chargé\n";
        
        // Tester le bootstrap Drupal
        $autoloader = require $autoload;
        echo "✅ Autoloader initialisé\n";
        
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        echo "✅ Request créée\n";
        
        $kernel = \Drupal\Core\DrupalKernel::createFromRequest($request, $autoloader, 'prod');
        echo "✅ Kernel créé\n";
        
        $kernel->boot();
        echo "✅ Kernel démarré\n";
        
        echo "\n🎉 DRUPAL FONCTIONNE !\n";
        echo "Le problème vient probablement du .htaccess ou d'une redirection\n";
        
    } else {
        echo "❌ Impossible de tester : autoload.php manquant\n";
    }
} catch (\Exception $e) {
    echo "❌ ERREUR DRUPAL :\n";
    echo "Message : " . $e->getMessage() . "\n";
    echo "Fichier : " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace :\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n=== FIN DIAGNOSTIC ===\n";


