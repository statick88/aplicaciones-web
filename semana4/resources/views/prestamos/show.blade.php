@extends('layout')

@section('titulo', 'Detalle del préstamo')

@section('contenido')
    <h1>Préstamo #{{ $prestamo->id }}</h1>

    <div class="panel">
        <dl class="detalle">
            <dt>Fecha de préstamo</dt><dd>{{ $prestamo->fecha_prestamo }}</dd>
            <dt>Fecha de devolución</dt><dd>{{ $prestamo->fecha_devolucion ?? 'Pendiente' }}</dd>
            <dt>Estado</dt><dd>{{ $prestamo->estado }}</dd>
            <dt>Lector</dt>
            <dd><a href="{{ route('lectores.show', $prestamo->lector) }}">{{ $prestamo->lector->nombre }} ({{ $prestamo->lector->cedula }})</a></dd>
            <dt>Libro</dt>
            <dd><a href="{{ route('libros.show', $prestamo->libro) }}">{{ $prestamo->libro->titulo }}</a></dd>
        </dl>

        <p class="acciones" style="margin-top:1.2rem">
            <a class="btn secundario" href="{{ route('prestamos.edit', $prestamo) }}">Editar</a>
            <a class="btn secundario" href="{{ route('prestamos.index') }}">Volver</a>
            <form class="inline" method="POST" action="{{ route('prestamos.destroy', $prestamo) }}"
                  onsubmit="return confirm('¿Eliminar este préstamo?');">
                @csrf
                @method('DELETE')
                <button class="btn peligro" type="submit">Eliminar</button>
            </form>
        </p>
    </div>
@endsection
