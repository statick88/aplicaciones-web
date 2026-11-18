@extends('layout')

@section('titulo', 'Categorías')

@section('contenido')
    <h1>Categorías</h1>

    <div class="panel">
        <p><a class="btn" href="{{ route('categorias.create') }}">+ Nueva categoría</a></p>

        @if ($categorias->isEmpty())
            <p class="vacio">No hay categorías registradas.</p>
        @else
            <table>
                <thead>
                <tr>
                    <th>Orden</th>
                    <th>Nombre</th>
                    <th>Color</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($categorias as $categoria)
                    <tr>
                        <td>{{ $categoria->orden }}</td>
                        <td>{{ $categoria->nombre }}</td>
                        <td>
                            <span style="display:inline-block;width:14px;height:14px;border-radius:3px;background:{{ $categoria->color }};vertical-align:middle"></span>
                            {{ $categoria->color }}
                        </td>
                        <td>{{ $categoria->activa ? 'Activa' : 'Inactiva' }}</td>
                        <td class="acciones">
                            <a class="btn secundario" href="{{ route('categorias.show', $categoria) }}">Ver</a>
                            <a class="btn secundario" href="{{ route('categorias.edit', $categoria) }}">Editar</a>
                            <form class="inline" method="POST" action="{{ route('categorias.destroy', $categoria) }}"
                                  onsubmit="return confirm('¿Eliminar esta categoría?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn peligro" type="submit">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <div class="paginacion">{{ $categorias->links() }}</div>
        @endif
    </div>
@endsection
