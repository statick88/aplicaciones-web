@extends('layout')

@section('titulo', 'Lectores')

@section('contenido')
    <h1>Lectores</h1>

    <div class="panel">
        <p><a class="btn" href="{{ route('lectores.create') }}">+ Nuevo lector</a></p>

        @if ($lectores->isEmpty())
            <p class="vacio">No hay lectores registrados.</p>
        @else
            <table>
                <thead>
                <tr>
                    <th>Cédula</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Registro</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($lectores as $lector)
                    <tr>
                        <td>{{ $lector->cedula }}</td>
                        <td>{{ $lector->nombre }}</td>
                        <td>{{ $lector->correo }}</td>
                        <td>{{ $lector->telefono }}</td>
                        <td>{{ $lector->fecha_registro }}</td>
                        <td class="acciones">
                            <a class="btn secundario" href="{{ route('lectores.show', $lector) }}">Ver</a>
                            <a class="btn secundario" href="{{ route('lectores.edit', $lector) }}">Editar</a>
                            <form class="inline" method="POST" action="{{ route('lectores.destroy', $lector) }}"
                                  onsubmit="return confirm('¿Eliminar este lector?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn peligro" type="submit">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <div class="paginacion">{{ $lectores->links() }}</div>
        @endif
    </div>
@endsection
