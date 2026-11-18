@extends('layout')

@section('titulo', $lector->nombre)

@section('contenido')
    <h1>{{ $lector->nombre }}</h1>

    <div class="panel">
        <dl class="detalle">
            <dt>Cédula</dt><dd>{{ $lector->cedula }}</dd>
            <dt>Correo</dt><dd>{{ $lector->correo }}</dd>
            <dt>Teléfono</dt><dd>{{ $lector->telefono }}</dd>
            <dt>Fecha de registro</dt><dd>{{ $lector->fecha_registro }}</dd>
            <dt>Préstamos</dt>
            <dd>
                @if ($lector->prestamos->isEmpty())
                    Sin préstamos.
                @else
                    <ul>
                        @foreach ($lector->prestamos as $prestamo)
                            <li>
                                <a href="{{ route('prestamos.show', $prestamo) }}">{{ $prestamo->libro->titulo }}</a>
                                — {{ $prestamo->fecha_prestamo }} ({{ $prestamo->estado }})
                            </li>
                        @endforeach
                    </ul>
                @endif
            </dd>
        </dl>

        <p class="acciones" style="margin-top:1.2rem">
            <a class="btn secundario" href="{{ route('lectores.edit', $lector) }}">Editar</a>
            <a class="btn secundario" href="{{ route('lectores.index') }}">Volver</a>
            <form class="inline" method="POST" action="{{ route('lectores.destroy', $lector) }}"
                  onsubmit="return confirm('¿Eliminar este lector?');">
                @csrf
                @method('DELETE')
                <button class="btn peligro" type="submit">Eliminar</button>
            </form>
        </p>
    </div>
@endsection
