<?php
/**
 * @file
 * Fichier de routeur personnalisé pour le serveur PHP intégré.
 * 
 * Ce fichier gère les fichiers statiques et laisse Drupal gérer le reste.
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Pour les fichiers qui existent physiquement (CSS, JS, images, etc.), les servir directement
if ($uri !== '/' && file_exists(__DIR__ . $uri) && !is_dir(__DIR__ . $uri)) {
  return false;
}

// Pour tout le reste (y compris /agenda), laisser Drupal gérer via index.php
$_SERVER['SCRIPT_NAME'] = '/index.php';
require 'index.php';
