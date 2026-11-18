@extends('layout')

@section('titulo', 'Nuevo libro')

@section('contenido')
    <h1>Nuevo libro</h1>

    <div class="panel formulario">
        <form method="POST" action="{{ route('libros.store') }}">
            @csrf
            @include('libros._form')

            <p style="margin-top:1.2rem">
                <button class="btn" type="submit">Guardar</button>
                <a class="btn secundario" href="{{ route('libros.index') }}">Cancelar</a>
            </p>
        </form>
    </div>
@endsection
