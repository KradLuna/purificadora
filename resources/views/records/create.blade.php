@extends('adminlte::page')

@section('title', 'Crear registro')

@section('content_header')
    <h1>Crear registro</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('records.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label for="record_type_id">Tipo de registro</label>
                    <select name="record_type_id" id="record_type_id" class="form-control" required>
                        <option value="">-- Selecciona uno --</option>
                        @foreach ($recordTypes as $type)
                            <option value="{{ $type->id }}" {{ old('record_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('record_type_id')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="value">Valor</label>
                    <input type="number" step="0.01" name="value" id="value" class="form-control"
                        value="{{ old('value') }}" required>
                    @error('value')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="evidence">Evidencia <small class="text-muted fst-italic">(No obligatorio para Corte de
                            Caja)</small></label>
                    <input type="file" name="evidence" id="evidence" class="form-control">
                    @error('evidence')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar
                </button>
                <a href="{{ route('records.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@stop

{{-- ========================= --}}
{{--  COMPRESIÓN DE IMÁGENES  --}}
{{-- ========================= --}}
@section('js')
    <script src="https://cdn.jsdelivr.net/npm/browser-image-compression@2.0.2/dist/browser-image-compression.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('evidence');

            input.addEventListener('change', async function(event) {
                const file = event.target.files[0];
                if (!file) return;

                // Validaciones previas
                if (!['image/jpeg', 'image/png'].includes(file.type)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Formato no compatible',
                        text: 'Solo JPG o PNG.'
                    });
                    input.value = '';
                    return;
                }
                if (file.size > 20 * 1024 * 1024) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Archivo muy grande',
                        text: 'Máximo permitido: 20 MB.'
                    });
                    input.value = '';
                    return;
                }

                const options = {
                    maxSizeMB: 1.8,
                    maxWidthOrHeight: 1920,
                    useWebWorker: false, // evita errores en navegadores antiguos
                    initialQuality: 0.8
                };

                try {
                    Swal.fire({
                        title: 'Comprimiendo imagen...',
                        text: 'Por favor espera unos segundos.',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    const compressedBlob = await imageCompression(file, options);

                    console.log(`Original: ${(file.size / 1024 / 1024).toFixed(2)} MB`);
                    console.log(`Comprimido: ${(compressedBlob.size / 1024 / 1024).toFixed(2)} MB`);

                    // ✅ Convertir Blob → File
                    const compressedFile = new File([compressedBlob], file.name, {
                        type: file.type
                    });

                    // Reemplazar archivo original
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(compressedFile);
                    input.files = dataTransfer.files;

                    Swal.close();
                    Swal.fire({
                        icon: 'success',
                        title: 'Imagen optimizada',
                        text: `Tamaño reducido a ${(compressedFile.size / 1024 / 1024).toFixed(2)} MB`,
                        timer: 2000,
                        showConfirmButton: false
                    });

                } catch (error) {
                    console.error('Error al comprimir:', error);
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al comprimir la imagen',
                        text: error.message || 'Intenta nuevamente o selecciona otra imagen.'
                    });
                }

            });
        });
    </script>
@stop
