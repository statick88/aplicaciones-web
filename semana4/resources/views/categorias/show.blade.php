@extends('layout')

@section('titulo', $categoria->nombre)

@section('contenido')
    <h1>{{ $categoria->nombre }}</h1>

    <div class="panel">
        <dl class="detalle">
            <dt>Descripción</dt><dd>{{ $categoria->descripcion ?? '—' }}</dd>
            <dt>Color</dt>
            <dd>
                <span style="display:inline-block;width:14px;height:14px;border-radius:3px;background:{{ $categoria->color }};vertical-align:middle"></span>
                {{ $categoria->color }}
            </dd>
            <dt>Orden</dt><dd>{{ $categoria->orden }}</dd>
            <dt>Estado</dt><dd>{{ $categoria->activa ? 'Activa' : 'Inactiva' }}</dd>
            <dt>Libros asociados</dt>
            <dd>
                @if ($categoria->libros->isEmpty())
                    Sin libros.
                @else
                    <ul>
                        @foreach ($categoria->libros as $libro)
                            <li><a href="{{ route('libros.show', $libro) }}">{{ $libro->titulo }}</a></li>
                        @endforeach
                    </ul>
                @endif
            </dd>
        </dl>

        <p class="acciones" style="margin-top:1.2rem">
            <a class="btn secundario" href="{{ route('categorias.edit', $categoria) }}">Editar</a>
            <a class="btn secundario" href="{{ route('categorias.index') }}">Volver</a>
            <form class="inline" method="POST" action="{{ route('categorias.destroy', $categoria) }}"
                  onsubmit="return confirm('¿Eliminar esta categoría?');">
                @csrf
                @method('DELETE')
                <button class="btn peligro" type="submit">Eliminar</button>
            </form>
        </p>
    </div>
@endsection
