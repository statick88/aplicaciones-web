@extends('layout')

@section('titulo', 'Editar libro')

@section('contenido')
    <h1>Editar libro</h1>

    <div class="panel formulario">
        <form method="POST" action="{{ route('libros.update', $libro) }}">
            @csrf
            @method('PUT')
            @include('libros._form', ['libro' => $libro])

            <p style="margin-top:1.2rem">
                <button class="btn" type="submit">Actualizar</button>
                <a class="btn secundario" href="{{ route('libros.show', $libro) }}">Cancelar</a>
            </p>
        </form>
    </div>
@endsection
