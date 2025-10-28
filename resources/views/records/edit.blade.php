@extends('adminlte::page')

@section('title', 'Editar Registro')

@section('content_header')
    <h1>Editar Registro</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('records.update', $record->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- Tipo de registro --}}
                <div class="form-group">
                    <label for="record_type_id">Tipo de Registro</label>
                    <select name="record_type_id" id="record_type_id" class="form-control" disabled>
                        @foreach ($recordTypes as $type)
                            <option value="{{ $type->id }}"
                                {{ $type->id == $record->record_type_id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Valor --}}
                <div class="form-group">
                    <label for="value">Valor</label>
                    <input type="number" step="0.01" class="form-control" name="value" id="value"
                        value="{{ old('value', $record->value) }}">
                </div>

                {{-- Evidencia --}}
                <div class="form-group">
                    <label for="evidence">Evidencia</label>
                    {!! view('records.evidence', ['row' => $record])->render() !!}
                    <input type="file" name="evidence" id="evidence" class="form-control mt-2">
                </div>

                {{-- Fecha --}}
                <div class="form-group">
                    <label for="record_date">Fecha</label>
                    <input type="datetime-local" class="form-control" name="record_date" id="record_date"
                        value="{{ old('record_date', $record->created_at->format('Y-m-d\TH:i')) }}"
                        {{ auth()->user()->isAdmin ? '' : 'readonly' }}>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-success">Actualizar</button>
                    <a href="{{ route('records.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    @include('records.modal') {{-- Modal para imágenes --}}
@stop
