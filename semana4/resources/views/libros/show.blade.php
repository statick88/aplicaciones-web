@extends('layout')

@section('titulo', $libro->titulo)

@section('contenido')
    <h1>{{ $libro->titulo }}</h1>

    <div class="panel">
        <dl class="detalle">
            <dt>Año</dt><dd>{{ $libro->anio }}</dd>
            <dt>ISBN</dt><dd>{{ $libro->isbn }}</dd>
            <dt>Editorial</dt>
            <dd><a href="{{ route('editoriales.show', $libro->editorial) }}">{{ $libro->editorial->nombre }}</a></dd>
            <dt>Categoría</dt>
            <dd><a href="{{ route('categorias.show', $libro->categoria) }}">{{ $libro->categoria->nombre }}</a></dd>
            <dt>Préstamos</dt>
            <dd>
                @if ($libro->prestamos->isEmpty())
                    Sin préstamos.
                @else
                    <ul>
                        @foreach ($libro->prestamos as $prestamo)
                            <li>
                                {{ $prestamo->lector->nombre }} —
                                {{ $prestamo->fecha_prestamo }}
                                ({{ $prestamo->estado }})
                            </li>
                        @endforeach
                    </ul>
                @endif
            </dd>
        </dl>

        <p class="acciones" style="margin-top:1.2rem">
            <a class="btn secundario" href="{{ route('libros.edit', $libro) }}">Editar</a>
            <a class="btn secundario" href="{{ route('libros.index') }}">Volver</a>
            <form class="inline" method="POST" action="{{ route('libros.destroy', $libro) }}"
                  onsubmit="return confirm('¿Eliminar este libro?');">
                @csrf
                @method('DELETE')
                <button class="btn peligro" type="submit">Eliminar</button>
            </form>
        </p>
    </div>
@endsection
