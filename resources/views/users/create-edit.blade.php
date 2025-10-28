@extends('adminlte::page')

@section('title')
    {{ isset($user) ? 'Editar Usuario' : 'Crear Usuario' }}
@endsection


@section('content_header')
    <h1>{{ isset($user) ? 'Editar Usuario' : 'Nuevo Usuario' }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if (isset($user))
                {!! Form::model($user, ['route' => ['users.update', $user->id], 'method' => 'PUT']) !!}
            @else
                {!! Form::open(['route' => 'users.store', 'method' => 'POST']) !!}
            @endif

            <div class="form-group">
                {!! Form::label('full_name', 'Nombre') !!}
                {!! Form::text('full_name', isset($user) ? $user->full_name : null, [
                    'class' => 'form-control',
                ]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('password', 'Contraseña (dejar en blanco para no cambiar)') !!}
                {!! Form::password('password', ['class' => 'form-control', 'autocomplete' => 'new-password']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('password_confirmation', 'Confirmar Contraseña') !!}
                {!! Form::password('password_confirmation', ['class' => 'form-control']) !!}
            </div>

            @role('administrador')
                <div class="form-group">
                    {!! Form::label('phone_number', 'Teléfono') !!}
                    {!! Form::text('phone_number', isset($user) ? $user->phone_number : null, [
                        'class' => 'form-control',
                        'maxlength' => 10,
                        'minlength' => 10,
                        'pattern' => '[0-9]{10}', // opcional, asegura solo 10 dígitos
                        'placeholder' => 'Ej: 5523456789',
                    ]) !!}
                </div>

                {!! Form::label('active', 'Estado del empleado') !!}
                <div>
                    <label class="mr-3">
                        {!! Form::radio('is_active', 1, isset($user) ? $user->is_active == 1 : true) !!} Activo
                    </label>
                    <label>
                        {!! Form::radio('is_active', 0, isset($user) ? $user->is_active == 0 : false) !!} Inactivo
                    </label>
                </div>
            @endrole



            <button type="submit" class="btn btn-primary">
                {{ isset($user) ? 'Actualizar' : 'Guardar' }}
            </button>
            <a href="{{ route('users.index') }}" class="btn btn-danger">
                Cancelar
            </a>

            {!! Form::close() !!}
        </div>
    </div>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    {{-- <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "Seleccione roles",
                allowClear: true
            });
        });
    </script> --}}
@stop
