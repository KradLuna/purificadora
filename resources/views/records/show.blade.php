@extends('adminlte::page')

@section('title', 'Detalle del Registro')

@section('content_header')
    <h1>Detalle del Registro</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-6"><strong>Empleado:</strong> {{ $record->user->full_name }}</div>
                <div class="col-md-6"><strong>Tipo de registro:</strong> {{ $record->record_type->name }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-md-6"><strong>Valor:</strong> {{ $record->value }}</div>
                <div class="col-md-6"><strong>Fecha:</strong>
                    {{ $record->created_at->locale('es')->isoFormat('dddd, hh:mm A | D MMMM YYYY') }}
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-12">
                    <strong>Evidencia:</strong>
                    {!! view('records.evidence', ['row' => $record])->render() !!}
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <a href="{{ route('records.index') }}" class="btn btn-secondary">Volver</a>
                    @if (auth()->user()->id === $record->user_id || auth()->user()->hasRole('adminitrador'))
                        <a href="{{ route('records.edit', $record->id) }}" class="btn btn-primary">Editar</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('records.modal') {{-- Modal para imágenes --}}
@section('js')
    <script>
        $(document).ready(function() {
            $('img[data-toggle="modal"]').on('click', function() {
                var url = $(this).data('url');
                $('#modal-image').attr('src', url);
                $('#evidenceModal').modal('show');
            });
        });
    </script>
@stop
@stop
