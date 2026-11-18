@extends('layout')

@section('titulo', 'Editar categoría')

@section('contenido')
    <h1>Editar categoría</h1>

    <div class="panel formulario">
        <form method="POST" action="{{ route('categorias.update', $categoria) }}">
            @csrf
            @method('PUT')
            @include('categorias._form', ['categoria' => $categoria])

            <p style="margin-top:1.2rem">
                <button class="btn" type="submit">Actualizar</button>
                <a class="btn secundario" href="{{ route('categorias.show', $categoria) }}">Cancelar</a>
            </p>
        </form>
    </div>
@endsection
