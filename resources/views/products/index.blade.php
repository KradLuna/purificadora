@extends('adminlte::page')

@section('title', 'Gestión de Productos')

@section('content_header')
    <h1>Listado de Productos</h1>
@stop

@section('content')
    <div class="card">

        <div class="card-body">
            <div>
                <a href="{{ route('products.create') }}" class="btn btn-primary float-right">
                    <i class="fas fa-plus"></i> Nuevo Producto
                </a>
            </div>
            <table id="products-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Litros</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- DataTables llenará esto automáticamente -->
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('css')
    <!-- DataTables CSS con botones -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.bootstrap4.min.css">
@stop

@section('js')
    <!-- jQuery primero -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Mostrar SweetAlert si hay success en sesión
            @if (session('success'))
                Swal.fire({
                    title: '¡Éxito!',
                    text: "{{ session('success') }}",
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                });
            @endif

            // Inicializar DataTable
            if ($.fn.DataTable.isDataTable('#products-table')) {
                $('#products-table').DataTable().destroy();
            }

            $('#products-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('products.data') }}",
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'price',
                        name: 'price',
                        render: function(data, type, row) {
                            return '$' + parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'liters',
                        name: 'liters'
                    },
                    {
                        data: 'is_active',
                        name: 'is_active',
                        render: function(data, type, row) {
                            return data == 1 ? 'Activo' : 'Inactivo';
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                responsive: true,
                autoWidth: false
            });
        });
    </script>

    <script>
        function confirmDelete(productId) {
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
                    document.getElementById('delete-form-' + productId).submit();
                }
            })
        }
    </script>
@stop
