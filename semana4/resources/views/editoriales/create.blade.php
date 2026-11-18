@extends('layout')

@section('titulo', 'Nueva editorial')

@section('contenido')
    <h1>Nueva editorial</h1>

    <div class="panel formulario">
        <form method="POST" action="{{ route('editoriales.store') }}">
            @csrf
            @include('editoriales._form')

            <p style="margin-top:1.2rem">
                <button class="btn" type="submit">Guardar</button>
                <a class="btn secundario" href="{{ route('editoriales.index') }}">Cancelar</a>
            </p>
        </form>
    </div>
@endsection
