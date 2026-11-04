<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Cliente.php';
require_once __DIR__ . '/models/ValidadorClientes.php';
require_once __DIR__ . '/interfaces/RepositorioClientes.php';
require_once __DIR__ . '/repositories/ClienteRepositorioMysql.php';
require_once __DIR__ . '/controllers/ClienteController.php';

$repositorio = new ClienteRepositorioMysql($conexion);
$controlador = new ClienteController($repositorio, new ValidadorClientes());
$controlador->index();
