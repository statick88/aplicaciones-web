<?php

use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\EditorialController;
use App\Http\Controllers\LectorController;
use App\Http\Controllers\LibroController;
use App\Http\Controllers\PrestamoController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

// `Str::singular()` produce "lectore"/"editoriale" para estos plurales en
// inglés; sin `parameters()` el binding implícito por nombre no casa con
// los métodos del controlador ($lector, $editorial...) y estos reciben un
// modelo vacío. Se fija el nombre del parámetro en las 5 rutas.
Route::resource('editoriales', EditorialController::class)
    ->parameters(['editoriales' => 'editorial']);
Route::resource('categorias', CategoriaController::class)
    ->parameters(['categorias' => 'categoria']);
Route::resource('libros', LibroController::class)
    ->parameters(['libros' => 'libro']);
Route::resource('lectores', LectorController::class)
    ->parameters(['lectores' => 'lector']);
Route::resource('prestamos', PrestamoController::class)
    ->parameters(['prestamos' => 'prestamo']);
