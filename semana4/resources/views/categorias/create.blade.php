@extends('layout')

@section('titulo', 'Nueva categoría')

@section('contenido')
    <h1>Nueva categoría</h1>

    <div class="panel formulario">
        <form method="POST" action="{{ route('categorias.store') }}">
            @csrf
            @include('categorias._form')

            <p style="margin-top:1.2rem">
                <button class="btn" type="submit">Guardar</button>
                <a class="btn secundario" href="{{ route('categorias.index') }}">Cancelar</a>
            </p>
        </form>
    </div>
@endsection
