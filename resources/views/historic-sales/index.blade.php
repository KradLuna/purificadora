@extends('adminlte::page')

@section('title', 'Historial de Ventas')

@section('content_header')
    <h1>Historial de Ventas</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Listado de Ventas</h3>
            <a href="{{ route('historic-sales.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva venta histórica
            </a>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="historic-sales-table" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Empleado</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Total ($)</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables llenará esto automáticamente -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.bootstrap4.min.css">
@stop

@section('js')
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Mostrar mensaje de éxito
            @if (session('success'))
                Swal.fire({
                    title: '¡Éxito!',
                    text: "{{ session('success') }}",
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                });
            @endif

            // Inicializar DataTable
            $('#historic-sales-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('historic-sales.data') }}",
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'employee_name',
                        name: 'employee_name'
                    },
                    {
                        data: 'product_name',
                        name: 'product_name'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'total',
                        name: 'total',
                        render: function(data) {
                            return '$' + parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                },
                responsive: true,
                autoWidth: false
            });
        });

        // Confirmación de eliminación
        function confirmDelete(saleId) {
            Swal.fire({
                title: '¿Está seguro?',
                text: "¡No podrá revertir esta acción!",
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
            });
        }
    </script>
@stop
