@extends('adminlte::page')

@section('title', 'Registrar Venta')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">Registrar Venta</h1>
        <h1 id="total-sales" class="badge bg-success">
            Total: ${{ number_format($totalSales, 2) }}
        </h1>
    </div>
@stop

@section('content')
    @php
        $errorMsg = '';
        $canDoASale = auth()->user()->canDoASale($errorMsg);
    @endphp

    @if (!$canDoASale)
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> {{ $errorMsg }}
        </div>
    @endif
    <div class="row mb-4">
        @foreach ($products as $product)
            <div class="col-md-3">
                <div class="card text-center product-card shadow-lg" data-id="{{ $product->id }}"
                    data-name="{{ $product->name }}" data-price="{{ $product->price }}" style="cursor: pointer;">
                    <div class="card-body">
                        <h5 class="card-title">{{ $product->name }}</h5>
                        <p class="card-text h4 text-success">
                            ${{ number_format($product->price, 2) }}
                        </p>
                        <small class="text-muted">Presiona para vender</small>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Tabla de ventas con DataTables -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">Mis Ventas</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="sales-table" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Total</th>
                            <th>Hora</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables llenará esto -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
@stop

@section('js')
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            $('#sales-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('sales.data') }}",
                columns: [{
                        data: 'product.name',
                        name: 'product.name'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'price_unit',
                        name: 'price_unit'
                    },
                    {
                        data: 'total',
                        name: 'total'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    } // 👈
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                order: [
                    [4, 'desc']
                ],
                responsive: true,
                autoWidth: false
            });


            // Acción de vender producto
            $(".product-card").on("click", function() {
                let productId = $(this).data("id");
                let name = $(this).data("name");
                let price = $(this).data("price");

                Swal.fire({
                    title: `Vender: ${name}`,
                    text: `Precio unitario: $${parseFloat(price).toFixed(2)}`,
                    input: 'number',
                    inputAttributes: {
                        min: 1,
                        step: 1
                    },
                    inputValue: 1,
                    inputLabel: 'Cantidad',
                    inputPlaceholder: 'Ingrese cantidad vendida',
                    showCancelButton: true,
                    confirmButtonText: 'Registrar venta',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed && result.value > 0) {
                        $.ajax({
                            url: "{{ route('sales.store') }}",
                            method: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                product_id: productId,
                                amount: result.value
                            },
                            success: function(response) {
                                Swal.fire('¡Éxito!', 'Venta registrada.', 'success');
                                $('#sales-table').DataTable().ajax
                                    .reload();
                                // Actualizar total
                                $('#total-sales').text(
                                    `Total: $${parseFloat(response.totalSales).toFixed(2)}`
                                );
                            },
                            error: function(xhr) {
                                let errorMessage = xhr.responseJSON?.message ??
                                    'No se pudo registrar la venta';
                                Swal.fire('Error', errorMessage, 'error');
                            }
                        });
                    }
                });
            });
        });

        function confirmDelete(saleId) {
            Swal.fire({
                title: '¿Está seguro?',
                text: "¡No podrás revertir esto!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + saleId).submit();
                }
            })
        }
    </script>
@stop
