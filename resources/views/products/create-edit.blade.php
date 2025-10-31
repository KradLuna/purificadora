@extends('adminlte::page')

@section('title', isset($product) ? 'Editar Producto' : 'Crear Producto')

@section('content_header')
    <h1>{{ isset($product) ? 'Editar Producto' : 'Nuevo Producto' }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ isset($product) ? route('products.update', $product->id) : route('products.store') }}" method="POST">
                @csrf
                @if (isset($product))
                    @method('PUT')
                @endif

                {{-- ===================== --}}
                {{-- NOMBRE DEL PRODUCTO --}}
                {{-- ===================== --}}
                <div class="form-group">
                    <label for="name">Nombre del producto</label>
                    <input type="text" name="name" id="name" class="form-control"
                        value="{{ old('name', $product->name ?? '') }}"
                        placeholder="Ej: Botella 2L" required>
                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- ===================== --}}
                {{-- PRECIO --}}
                {{-- ===================== --}}
                <div class="form-group">
                    <label for="price">Precio</label>
                    <input type="number" name="price" id="price" class="form-control"
                        step="0.01" min="0" placeholder="Ej: 10.00"
                        value="{{ old('price', $product->price ?? '') }}" required>
                    @error('price')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- ===================== --}}
                {{-- LITROS --}}
                {{-- ===================== --}}
                <div class="form-group">
                    <label for="liters">Litros (opcional)</label>
                    <input type="number" name="liters" id="liters" class="form-control"
                        step="0.5" min="0" placeholder="Ej: 2"
                        value="{{ old('liters', $product->liters ?? '') }}">
                    @error('liters')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- ===================== --}}
                {{-- ESTADO --}}
                {{-- ===================== --}}
                <div class="form-group">
                    <label>Estado del producto</label>
                    <div>
                        <label class="mr-3">
                            <input type="radio" name="is_active" value="1"
                                {{ old('is_active', $product->is_active ?? 1) == 1 ? 'checked' : '' }}> Activo
                        </label>
                        <label>
                            <input type="radio" name="is_active" value="0"
                                {{ old('is_active', $product->is_active ?? 1) == 0 ? 'checked' : '' }}> Inactivo
                        </label>
                    </div>
                    @error('is_active')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- ===================== --}}
                {{-- BOTONES --}}
                {{-- ===================== --}}
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ isset($product) ? 'Actualizar' : 'Guardar' }}
                    </button>
                    <a href="{{ route('products.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>

            </form>
        </div>
    </div>
@stop

@section('css')
    {{-- Si necesitas estilos adicionales --}}
@stop

@section('js')
    {{-- Si quieres agregar JS adicional --}}
@stop
