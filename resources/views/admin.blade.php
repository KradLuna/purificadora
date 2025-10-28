@extends('adminlte::page')

@section('title', 'Panel de Administración')

@section('content_header')
@parent  <!-- Mantiene el contenido original -->
    <div class="float-right">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="btn btn-sm btn-danger">Logout</button>
        </form>
    </div>
    <h1>Panel de Administración</h1>
@endsection

@section('content')
    <p>Este es tu dashboard de Purificadora Luna usando AdminLTE.</p>
@endsection
