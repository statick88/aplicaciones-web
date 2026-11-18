@extends('layout')

@section('titulo', 'Editoriales')

@section('contenido')
    <h1>Editoriales</h1>

    <div class="panel">
        <p><a class="btn" href="{{ route('editoriales.create') }}">+ Nueva editorial</a></p>

        @if ($editoriales->isEmpty())
            <p class="vacio">No hay editoriales registradas.</p>
        @else
            <table>
                <thead>
                <tr>
                    <th>Nombre</th>
                    <th>País</th>
                    <th>Fundación</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($editoriales as $editorial)
                    <tr>
                        <td>{{ $editorial->nombre }}</td>
                        <td>{{ $editorial->pais }}</td>
                        <td>{{ $editorial->fundacion ?? '—' }}</td>
                        <td>{{ $editorial->activa ? 'Activa' : 'Inactiva' }}</td>
                        <td class="acciones">
                            <a class="btn secundario" href="{{ route('editoriales.show', $editorial) }}">Ver</a>
                            <a class="btn secundario" href="{{ route('editoriales.edit', $editorial) }}">Editar</a>
                            <form class="inline" method="POST" action="{{ route('editoriales.destroy', $editorial) }}"
                                  onsubmit="return confirm('¿Eliminar esta editorial?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn peligro" type="submit">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <div class="paginacion">{{ $editoriales->links() }}</div>
        @endif
    </div>
@endsection
