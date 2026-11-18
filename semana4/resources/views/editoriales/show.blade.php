@extends('layout')

@section('titulo', $editorial->nombre)

@section('contenido')
    <h1>{{ $editorial->nombre }}</h1>

    <div class="panel">
        <dl class="detalle">
            <dt>País</dt><dd>{{ $editorial->pais }}</dd>
            <dt>Página web</dt><dd>{{ $editorial->pagina_web ?? '—' }}</dd>
            <dt>Fundación</dt><dd>{{ $editorial->fundacion ?? '—' }}</dd>
            <dt>Estado</dt><dd>{{ $editorial->activa ? 'Activa' : 'Inactiva' }}</dd>
            <dt>Libros asociados</dt>
            <dd>
                @if ($editorial->libros->isEmpty())
                    Sin libros.
                @else
                    <ul>
                        @foreach ($editorial->libros as $libro)
                            <li><a href="{{ route('libros.show', $libro) }}">{{ $libro->titulo }}</a></li>
                        @endforeach
                    </ul>
                @endif
            </dd>
        </dl>

        <p class="acciones" style="margin-top:1.2rem">
            <a class="btn secundario" href="{{ route('editoriales.edit', $editorial) }}">Editar</a>
            <a class="btn secundario" href="{{ route('editoriales.index') }}">Volver</a>
            <form class="inline" method="POST" action="{{ route('editoriales.destroy', $editorial) }}"
                  onsubmit="return confirm('¿Eliminar esta editorial?');">
                @csrf
                @method('DELETE')
                <button class="btn peligro" type="submit">Eliminar</button>
            </form>
        </p>
    </div>
@endsection
