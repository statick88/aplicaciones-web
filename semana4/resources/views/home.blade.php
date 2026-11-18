@extends('layout')

@section('titulo', 'Inicio')

@section('contenido')
    <h1>Sistema Biblioteca — Gestión con scaffolding</h1>

    <div class="panel">
        <p>
            Sistema desarrollado con <strong>Laravel</strong> y scaffolding artisan para la
            gestión de una biblioteca. Cada módulo ofrece el CRUD completo
            (listar, crear, ver, editar, eliminar) sobre las cinco entidades del modelo:
        </p>

        <table>
            <thead>
            <tr>
                <th>Entidad</th>
                <th>Descripción</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><strong>Editoriales</strong></td>
                <td>Casas editoriales de los libros.</td>
                <td class="acciones"><a class="btn" href="{{ route('editoriales.index') }}">Gestionar</a></td>
            </tr>
            <tr>
                <td><strong>Categorías</strong></td>
                <td>Clasificación temática de los libros.</td>
                <td class="acciones"><a class="btn" href="{{ route('categorias.index') }}">Gestionar</a></td>
            </tr>
            <tr>
                <td><strong>Libros</strong></td>
                <td>Catálogo, vinculado a editorial y categoría.</td>
                <td class="acciones"><a class="btn" href="{{ route('libros.index') }}">Gestionar</a></td>
            </tr>
            <tr>
                <td><strong>Lectores</strong></td>
                <td>Usuarios registrados de la biblioteca.</td>
                <td class="acciones"><a class="btn" href="{{ route('lectores.index') }}">Gestionar</a></td>
            </tr>
            <tr>
                <td><strong>Préstamos</strong></td>
                <td>Relación lector–libro con fechas y estado.</td>
                <td class="acciones"><a class="btn" href="{{ route('prestamos.index') }}">Gestionar</a></td>
            </tr>
            </tbody>
        </table>
    </div>
@endsection
