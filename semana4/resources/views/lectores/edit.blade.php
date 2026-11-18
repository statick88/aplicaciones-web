@extends('layout')

@section('titulo', 'Editar lector')

@section('contenido')
    <h1>Editar lector</h1>

    <div class="panel formulario">
        <form method="POST" action="{{ route('lectores.update', $lector) }}">
            @csrf
            @method('PUT')
            @include('lectores._form', ['lector' => $lector])

            <p style="margin-top:1.2rem">
                <button class="btn" type="submit">Actualizar</button>
                <a class="btn secundario" href="{{ route('lectores.show', $lector) }}">Cancelar</a>
            </p>
        </form>
    </div>
@endsection
