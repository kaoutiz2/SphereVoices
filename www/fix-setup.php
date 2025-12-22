<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<h1>Configuration SphereVoices</h1>
<pre>
<?php
$root = dirname(dirname(__FILE__));
$wwwRoot = $root . '/www';
$defaultSettings = $wwwRoot . '/sites/default/default.settings.php';
$settingsFile = $wwwRoot . '/sites/default/settings.php';
$envFile = $root . '/.env.production';
$filesDir = $wwwRoot . '/sites/default/files';

echo "PHP Version: " . phpversion() . "\n\n";

// Créer settings.php
if (!file_exists($settingsFile) && file_exists($defaultSettings)) {
    $content = file_get_contents($defaultSettings);
    
    $envCode = "\n\n// Load .env.production\n";
    $envCode .= "if (file_exists(__DIR__ . '/../../../.env.production')) {\n";
    $envCode .= "  \$env = __DIR__ . '/../../../.env.production';\n";
    $envCode .= "  \$vars = array();\n";
    $envCode .= "  if (is_readable(\$env)) {\n";
    $envCode .= "    \$lines = file(\$env);\n";
    $envCode .= "    foreach (\$lines as \$line) {\n";
    $envCode .= "      \$line = trim(\$line);\n";
    $envCode .= "      if (empty(\$line) || \$line[0] == '#') continue;\n";
    $envCode .= "      \$pos = strpos(\$line, '=');\n";
    $envCode .= "      if (\$pos !== false) {\n";
    $envCode .= "        \$key = trim(substr(\$line, 0, \$pos));\n";
    $envCode .= "        \$val = trim(substr(\$line, \$pos + 1));\n";
    $envCode .= "        \$vars[\$key] = \$val;\n";
    $envCode .= "      }\n";
    $envCode .= "    }\n";
    $envCode .= "    if (!empty(\$vars['DB_NAME'])) {\n";
    $envCode .= "      \$databases['default']['default'] = array(\n";
    $envCode .= "        'database' => \$vars['DB_NAME'],\n";
    $envCode .= "        'username' => \$vars['DB_USER'],\n";
    $envCode .= "        'password' => isset(\$vars['DB_PASSWORD']) ? \$vars['DB_PASSWORD'] : '',\n";
    $envCode .= "        'host' => isset(\$vars['DB_HOST']) ? \$vars['DB_HOST'] : 'localhost',\n";
    $envCode .= "        'port' => isset(\$vars['DB_PORT']) ? \$vars['DB_PORT'] : '3306',\n";
    $envCode .= "        'driver' => isset(\$vars['DB_DRIVER']) ? \$vars['DB_DRIVER'] : 'mysql',\n";
    $envCode .= "        'prefix' => isset(\$vars['DB_PREFIX']) ? \$vars['DB_PREFIX'] : '',\n";
    $envCode .= "        'collation' => isset(\$vars['DB_COLLATION']) ? \$vars['DB_COLLATION'] : 'utf8mb4_general_ci',\n";
    $envCode .= "      );\n";
    $envCode .= "    }\n";
    $envCode .= "  }\n";
    $envCode .= "}\n";
    
    $content = str_replace('$databases = [];', '$databases = array();' . $envCode, $content);
    
    if (file_put_contents($settingsFile, $content)) {
        chmod($settingsFile, 0644);
        echo "OK: settings.php cree\n";
    } else {
        echo "ERREUR: Impossible de creer settings.php\n";
    }
} else {
    echo "settings.php existe deja\n";
}

// Créer files/
if (!is_dir($filesDir)) {
    if (mkdir($filesDir, 0777, true)) {
        chmod($filesDir, 0777);
        echo "OK: Dossier files/ cree\n";
    } else {
        echo "ERREUR: Impossible de creer files/\n";
    }
} else {
    echo "files/ existe deja\n";
}

echo "\n";
echo "IMPORTANT:\n";
echo "1. CHANGEZ PHP vers 8.1+ dans OVH (CRITIQUE!)\n";
echo "2. Installez vendor/ sur le serveur\n";
echo "3. Verifiez .env.production\n";
?>
</pre>
