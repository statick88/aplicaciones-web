@extends('layout')

@section('titulo', 'Nuevo lector')

@section('contenido')
    <h1>Nuevo lector</h1>

    <div class="panel formulario">
        <form method="POST" action="{{ route('lectores.store') }}">
            @csrf
            @include('lectores._form')

            <p style="margin-top:1.2rem">
                <button class="btn" type="submit">Guardar</button>
                <a class="btn secundario" href="{{ route('lectores.index') }}">Cancelar</a>
            </p>
        </form>
    </div>
@endsection
