@extends('layout')

@section('titulo', 'Libros')

@section('contenido')
    <h1>Libros</h1>

    <div class="panel">
        <p><a class="btn" href="{{ route('libros.create') }}">+ Nuevo libro</a></p>

        @if ($libros->isEmpty())
            <p class="vacio">No hay libros registrados.</p>
        @else
            <table>
                <thead>
                <tr>
                    <th>Título</th>
                    <th>Año</th>
                    <th>ISBN</th>
                    <th>Editorial</th>
                    <th>Categoría</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($libros as $libro)
                    <tr>
                        <td>{{ $libro->titulo }}</td>
                        <td>{{ $libro->anio }}</td>
                        <td>{{ $libro->isbn }}</td>
                        <td><a href="{{ route('editoriales.show', $libro->editorial) }}">{{ $libro->editorial->nombre }}</a></td>
                        <td><a href="{{ route('categorias.show', $libro->categoria) }}">{{ $libro->categoria->nombre }}</a></td>
                        <td class="acciones">
                            <a class="btn secundario" href="{{ route('libros.show', $libro) }}">Ver</a>
                            <a class="btn secundario" href="{{ route('libros.edit', $libro) }}">Editar</a>
                            <form class="inline" method="POST" action="{{ route('libros.destroy', $libro) }}"
                                  onsubmit="return confirm('¿Eliminar este libro?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn peligro" type="submit">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <div class="paginacion">{{ $libros->links() }}</div>
        @endif
    </div>
@endsection
