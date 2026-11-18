@extends('layout')

@section('titulo', 'Préstamos')

@section('contenido')
    <h1>Préstamos</h1>

    <div class="panel">
        <p><a class="btn" href="{{ route('prestamos.create') }}">+ Nuevo préstamo</a></p>

        @if ($prestamos->isEmpty())
            <p class="vacio">No hay préstamos registrados.</p>
        @else
            <table>
                <thead>
                <tr>
                    <th>Fecha préstamo</th>
                    <th>Devolución</th>
                    <th>Estado</th>
                    <th>Lector</th>
                    <th>Libro</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($prestamos as $prestamo)
                    <tr>
                        <td>{{ $prestamo->fecha_prestamo }}</td>
                        <td>{{ $prestamo->fecha_devolucion ?? '—' }}</td>
                        <td>{{ $prestamo->estado }}</td>
                        <td><a href="{{ route('lectores.show', $prestamo->lector) }}">{{ $prestamo->lector->nombre }}</a></td>
                        <td><a href="{{ route('libros.show', $prestamo->libro) }}">{{ $prestamo->libro->titulo }}</a></td>
                        <td class="acciones">
                            <a class="btn secundario" href="{{ route('prestamos.show', $prestamo) }}">Ver</a>
                            <a class="btn secundario" href="{{ route('prestamos.edit', $prestamo) }}">Editar</a>
                            <form class="inline" method="POST" action="{{ route('prestamos.destroy', $prestamo) }}"
                                  onsubmit="return confirm('¿Eliminar este préstamo?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn peligro" type="submit">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <div class="paginacion">{{ $prestamos->links() }}</div>
        @endif
    </div>
@endsection
