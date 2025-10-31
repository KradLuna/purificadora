@extends('adminlte::page')

@section('title', isset($user) ? 'Editar Usuario' : 'Crear Usuario')

@section('content_header')
    <h1>{{ isset($user) ? 'Editar Usuario' : 'Nuevo Usuario' }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ isset($user) ? route('users.update', $user->id) : route('users.store') }}" method="POST">
                @csrf
                @if (isset($user))
                    @method('PUT')
                @endif

                {{-- ===================== --}}
                {{-- NOMBRE COMPLETO --}}
                {{-- ===================== --}}
                <div class="form-group">
                    <label for="full_name">Nombre completo</label>
                    <input type="text" name="full_name" id="full_name" class="form-control"
                        value="{{ old('full_name', $user->full_name ?? '') }}" required>
                    @error('full_name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- ===================== --}}
                {{-- CONTRASEÑA --}}
                {{-- ===================== --}}
                <div class="form-group">
                    <label for="password">Contraseña
                        <small class="text-muted fst-italic">(dejar en blanco para no cambiar)</small>
                    </label>
                    <input type="password" name="password" id="password" class="form-control" autocomplete="new-password">
                    @error('password')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- ===================== --}}
                {{-- CONFIRMAR CONTRASEÑA --}}
                {{-- ===================== --}}
                <div class="form-group">
                    <label for="password_confirmation">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                </div>

                {{-- ===================== --}}
                {{-- SOLO ADMIN --}}
                {{-- ===================== --}}
                @role('administrador')
                    <div class="form-group">
                        <label for="phone_number">Teléfono</label>
                        <input type="text" name="phone_number" id="phone_number" class="form-control" maxlength="10"
                            minlength="10" pattern="[0-9]{10}" placeholder="Ej: 4444556677"
                            value="{{ old('phone_number', $user->phone_number ?? '') }}">
                        @error('phone_number')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Estado --}}
                    <div class="form-group">
                        <label>Estado del empleado</label>
                        <div>
                            <label class="mr-3">
                                <input type="radio" name="is_active" value="1"
                                    {{ old('is_active', $user->is_active ?? 1) == 1 ? 'checked' : '' }}> Activo
                            </label>
                            <label>
                                <input type="radio" name="is_active" value="0"
                                    {{ old('is_active', $user->is_active ?? 1) == 0 ? 'checked' : '' }}> Inactivo
                            </label>
                        </div>
                    </div>
                @endrole

                {{-- ===================== --}}
                {{-- BOTONES --}}
                {{-- ===================== --}}
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ isset($user) ? 'Actualizar' : 'Guardar' }}
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>

            </form>
        </div>
    </div>
@stop

@section('css')
    {{-- Si necesitas estilos adicionales --}}
    {{-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> --}}
@stop

@section('js')
    {{-- Si quieres agregar validaciones JS o algo como select2 --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> --}}
@stop
