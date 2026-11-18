<?php

/**
 * Bootstrap de PHPUnit para la Semana 4.
 *
 * docker compose inyecta APP_ENV=local y las variables DB_* como variables
 * de entorno del contenedor, que PHP expone en $_SERVER. Laravel consulta
 * primero $_SERVER (ServerConstAdapter), y PHPUnit solo escribe $_ENV y
 * putenv: por eso los <env> de phpunit.xml no llegaban a la aplicación
 * (las pruebas corrían contra la base de desarrollo en vez de la de pruebas
 * y el CSRF seguía activo, devolviendo 419).
 *
 * Este bootstrap replica en $_SERVER los valores <env> de phpunit.xml,
 * respetando la semántica de force: sin force, un valor que ya existe en
 * el entorno real del proceso se conserva.
 */
$xml = simplexml_load_file(dirname(__DIR__) . '/phpunit.xml');

foreach ($xml->php->env as $env) {
    $name = (string) $env['name'];
    $value = (string) $env['value'];
    $force = isset($env['force']) && (string) $env['force'] === 'true';

    if ($force || !isset($_SERVER[$name])) {
        $_SERVER[$name] = $value;
    }

    if ($force || getenv($name) === false) {
        putenv($name . '=' . $value);
        $_ENV[$name] = $value;
    }
}

require dirname(__DIR__) . '/vendor/autoload.php';
