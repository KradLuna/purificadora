@extends('adminlte::page')

@section('title', isset($sale) ? 'Editar Venta Histórica' : 'Nueva Venta Histórica')

@section('content_header')
    <h1>{{ isset($sale) ? 'Editar Venta Histórica' : 'Nueva Venta Histórica' }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ isset($sale) ? route('historic-sales.update', $sale->id) : route('historic-sales.store') }}" method="POST">
                @csrf
                @if (isset($sale))
                    @method('PUT')
                @endif

                {{-- ===================== --}}
                {{-- EMPLEADO --}}
                {{-- ===================== --}}
                <div class="form-group">
                    <label for="employee_id">Empleado</label>
                    <select name="employee_id" id="employee_id" class="form-control select2" required>
                        <option value="">Seleccione un empleado</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}"
                                {{ old('employee_id', $sale->employee_id ?? '') == $user->id ? 'selected' : '' }}>
                                {{ $user->full_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- ===================== --}}
                {{-- PRODUCTO --}}
                {{-- ===================== --}}
                <div class="form-group">
                    <label for="product_id">Producto</label>
                    <select name="product_id" id="product_id" class="form-control select2" required>
                        <option value="">Seleccione un producto</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" data-price="{{ $product->price }}"
                                {{ old('product_id', $sale->product_id ?? '') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('product_id')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- ===================== --}}
                {{-- CANTIDAD --}}
                {{-- ===================== --}}
                <div class="form-group">
                    <label for="amount">Cantidad</label>
                    <input type="number" name="amount" id="amount" class="form-control" min="1"
                        value="{{ old('amount', $sale->amount ?? 1) }}" required>
                    @error('amount')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- ===================== --}}
                {{-- TOTAL (AUTOCALCULADO) --}}
                {{-- ===================== --}}
                <div class="form-group">
                    <label for="total">Total</label>
                    <input type="text" name="total" id="total" class="form-control" readonly
                        value="{{ old('total', $sale->total ?? '') }}">
                </div>

                {{-- ===================== --}}
                {{-- FECHA DE VENTA --}}
                {{-- ===================== --}}
                <div class="form-group">
                    <label for="created_at">Fecha de venta</label>
                    <input type="date" name="created_at" id="created_at" class="form-control"
                        value="{{ old('created_at', isset($sale) ? $sale->created_at->format('Y-m-d') : date('Y-m-d')) }}"
                        max="{{ date('Y-m-d') }}" required>
                    @error('created_at')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- ===================== --}}
                {{-- BOTONES --}}
                {{-- ===================== --}}
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ isset($sale) ? 'Actualizar' : 'Guardar' }}
                    </button>
                    <a href="{{ route('sales.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    {{-- Si usas select2 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    {{-- Ajustes visuales para los combos --}}
    <style>
        /* Aumentar espacio interior del campo */
        .select2-container--default .select2-selection--single {
            height: 42px !important;
            padding: 6px 12px !important;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 6px;
        }

        /* Centrar verticalmente el texto */
        .select2-selection__rendered {
            line-height: 30px !important;
        }

        /* Ajustar el ícono del dropdown */
        .select2-selection__arrow {
            height: 40px !important;
        }
    </style>
@stop

@section('js')
    {{-- JS para Select2 y cálculo automático --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                width: '100%' // se asegura que abarque todo el contenedor
            });

            function calcularTotal() {
                const productSelect = $('#product_id option:selected');
                const price = parseFloat(productSelect.data('price')) || 0;
                const amount = parseInt($('#amount').val()) || 0;
                const total = price * amount;
                $('#total').val(total.toFixed(2));
            }

            $('#product_id, #amount').on('change keyup', calcularTotal);

            // Calcular al cargar si ya hay valores
            calcularTotal();
        });
    </script>
@stop
