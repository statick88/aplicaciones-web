@extends('layout')

@section('titulo', 'Nuevo préstamo')

@section('contenido')
    <h1>Nuevo préstamo</h1>

    <div class="panel formulario">
        <form method="POST" action="{{ route('prestamos.store') }}">
            @csrf
            @include('prestamos._form')

            <p style="margin-top:1.2rem">
                <button class="btn" type="submit">Guardar</button>
                <a class="btn secundario" href="{{ route('prestamos.index') }}">Cancelar</a>
            </p>
        </form>
    </div>
@endsection
