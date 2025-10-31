@extends('adminlte::page')

@section('title', 'Editar Venta')

@section('content_header')
    <h1>Editar Venta</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('sales.update', $sale->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Producto --}}
                <div class="form-group">
                    <label for="product_id">Producto</label>
                    <select name="product_id" id="product_id" class="form-control">
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}"
                                {{ $product->id == old('product_id', $sale->product_id) ? 'selected' : '' }}>
                                {{ $product->name }} - ${{ number_format($product->price, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Cantidad --}}
                <div class="form-group">
                    <label for="amount">Cantidad</label>
                    <input type="number" name="amount" id="amount" class="form-control"
                        value="{{ old('amount', $sale->amount) }}" min="1" step="1">
                </div>

                <div class="form-group mt-3">
                    <button type="submit" class="btn btn-success">Actualizar</button>
                    <a href="{{ route('sales.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@stop
