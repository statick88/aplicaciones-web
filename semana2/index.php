<?php

require_once __DIR__ . 'config/database.php';

require_once __DIR__ . 'controllers/PersonasControllers.php';
$controlleor = new PersonaController();

if ($_SERVER['REQUEST_METHOS'] == 'POST'){

    $controlleoR->guardar();

: