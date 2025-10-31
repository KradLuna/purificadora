@extends('adminlte::page')

@section('title', 'Gestión de Registros')

@section('content_header')
    <x-header-section title="" />
    <div class="card-header mb-2">
        <div class="row align-items-center">
            <!-- Columna del título -->
            <div class="col-6">
                <h1 class="card-title mb-0">Registros</h1>
            </div>

            <!-- Columna del botón -->
            <div class="col-6 text-right">
                <a href="{{ route('records.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nuevo Registro
                </a>
            </div>
        </div>
    </div>
@endsection

@section('content')
    @php
        $isAdmin = auth()->user()->is_admin; // como variable para este scope
    @endphp
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="records-table" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            @if ($isAdmin)
                                <th>Empleado</th>
                            @endif
                            <th>Tipo</th>
                            <th>Valor</th>
                            <th>Evidencia</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables llenará esto automáticamente -->
                    </tbody>
                </table>
            </div>
            @include('records.modal')

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

            // Error
            @if ($errors->any())
                Swal.fire({
                    title: 'Error',
                    text: "{{ $errors->first() }}", // mostramos el primer error
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            @endif

            // Inicializar DataTable
            if ($.fn.DataTable.isDataTable('#records-table')) {
                $('#records-table').DataTable().destroy();
            }

            // Delegación: escucha clicks en cualquier imagen dentro de #records-table
            $('#records-table').on('click', 'img[data-toggle="modal"]', function() {
                var url = $(this).data('url');
                $('#modal-image').attr('src', url);
                $('#evidenceModal').modal('show');
            });

            $(function() {
                let columns = [
                    //         {data: 'id',
                    //         name: 'id',
                    //         visible: false
                    //     }, // ID real, oculto
                ];

                @if ($isAdmin)
                    columns.push({
                        data: 'user.full_name',
                        name: 'user.full_name'
                    });
                @endif

                columns.push({
                    data: 'record_type.name',
                    name: 'record_type.name'
                }, {
                    data: 'value',
                    name: 'value'
                }, {
                    data: 'evidence',
                    name: 'evidence',
                    orderable: false,
                    searchable: false
                }, {
                    data: 'created_at',
                    name: 'created_at'
                }, {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false
                });

                $('#records-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('records.data') }}",
                    columns: columns,
                    autoWidth: false,
                    order: [
                        [4, 'desc']
                    ],
                    responsive: true, // <-- esto hace que sea responsive
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                    }
                });
            });
        });

        function confirmDelete(recordId) {
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
                    document.getElementById('delete-form-' + recordId).submit();
                }
            })
        }
    </script>
@stop
