@extends('layout')

@section('titulo', 'Editar préstamo')

@section('contenido')
    <h1>Editar préstamo</h1>

    <div class="panel formulario">
        <form method="POST" action="{{ route('prestamos.update', $prestamo) }}">
            @csrf
            @method('PUT')
            @include('prestamos._form', ['prestamo' => $prestamo])

            <p style="margin-top:1.2rem">
                <button class="btn" type="submit">Actualizar</button>
                <a class="btn secundario" href="{{ route('prestamos.show', $prestamo) }}">Cancelar</a>
            </p>
        </form>
    </div>
@endsection
