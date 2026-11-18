<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Ruta raíz mínima: bootstrap/app.php apunta `web:` a este archivo, que el
| candidato no contenía, así que el arranque de Laravel fallaba al cargar
| las rutas y GET / respondía 404 (tests/Feature/ExampleTest).
|
*/

Route::get('/', fn (): string => 'Semana 4 — scaffolding Laravel')->name('home');
