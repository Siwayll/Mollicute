<?php

use \mageekguy\atoum;

$report = $script->addDefaultReport();

// This will add a green or red logo after each run depending on its status.
$report->addField(new atoum\report\fields\runner\result\logo());


if (!is_dir(__DIR__ . '/../build')) {
    mkdir(__DIR__ . '/../build');
    mkdir(__DIR__ . '/../build/html');
}
$coverageField = new atoum\report\fields\runner\coverage\html('Mollicute', __DIR__ . '/../build/html');
$report->addField($coverageField);


$cloverWriter = new atoum\writers\file(__DIR__ . '/../build/atoum.clover.xml');
$cloverReport = new atoum\reports\asynchronous\clover();
$cloverReport->addWriter($cloverWriter);
$runner->addReport($cloverReport);

// Chargement du fichier bootstrap
$runner->setBootstrapFile(__DIR__ . '/.bootstrap.atoum.php');
