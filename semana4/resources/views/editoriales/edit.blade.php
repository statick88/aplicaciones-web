@extends('layout')

@section('titulo', 'Editar editorial')

@section('contenido')
    <h1>Editar editorial</h1>

    <div class="panel formulario">
        <form method="POST" action="{{ route('editoriales.update', $editorial) }}">
            @csrf
            @method('PUT')
            @include('editoriales._form', ['editorial' => $editorial])

            <p style="margin-top:1.2rem">
                <button class="btn" type="submit">Actualizar</button>
                <a class="btn secundario" href="{{ route('editoriales.show', $editorial) }}">Cancelar</a>
            </p>
        </form>
    </div>
@endsection
