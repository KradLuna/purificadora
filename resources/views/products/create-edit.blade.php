@extends('adminlte::page')

@section('title')
    {{ isset($product) ? 'Editar Producto' : 'Crear Producto' }}
@endsection

@section('content_header')
    <h1>{{ isset($product) ? 'Editar Producto' : 'Nuevo Producto' }}</h1>
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

            @if (isset($product))
                {!! Form::model($product, ['route' => ['products.update', $product->id], 'method' => 'PUT']) !!}
            @else
                {!! Form::open(['route' => 'products.store', 'method' => 'POST']) !!}
            @endif

            <div class="form-group">
                {!! Form::label('name', 'Nombre del Producto') !!}
                {!! Form::text('name', isset($product) ? $product->name : null, [
                    'class' => 'form-control',
                    'placeholder' => 'Ej: Botella 2L',
                    'required',
                ]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('price', 'Precio') !!}
                {!! Form::number('price', isset($product) ? $product->price : null, [
                    'class' => 'form-control',
                    'step' => '0.01',
                    'min' => '0',
                    'placeholder' => 'Ej: 10.00',
                    'required',
                ]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('liters', 'Litros (opcional)') !!}
                {!! Form::number('liters', isset($product) ? $product->liters : null, [
                    'class' => 'form-control',
                    'step' => '0.5',
                    'min' => '0',
                    'placeholder' => 'Ej: 2',
                    'required',
                ]) !!}
            </div>

            {!! Form::label('active', 'Estado del producto') !!}
            <div>
                <label class="mr-3">
                    {!! Form::radio('is_active', 1, isset($product) ? $product->is_active == 1 : true) !!} Activo
                </label>
                <label>
                    {!! Form::radio('is_active', 0, isset($product) ? $product->is_active == 0 : false) !!} Inactivo
                </label>
            </div>

            <button type="submit" class="btn btn-primary">
                {{ isset($product) ? 'Actualizar' : 'Guardar' }}
            </button>
            <a href="{{ route('products.index') }}" class="btn btn-danger">
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
@stop
