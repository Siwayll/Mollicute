<?php

set_include_path(
    get_include_path()
    . PATH_SEPARATOR . realpath(__DIR__ . '/../../')
);

require 'vendor/autoload.php';

// Génération d'un dossier temporaires pour les tests
define('TEST_TMP_DIR', __DIR__ . '/../tmp');
if (!is_dir(TEST_TMP_DIR)) {
    mkdir(TEST_TMP_DIR);
}

define('TEST_DATA_DIR', __DIR__ . '/../data');
