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

                {{-- VALOR --}}
                <div class="form-group" id="group_value">
                    <label for="value" id="label_value">Valor</label>
                    <input type="number" step="0.01" name="value" id="value" class="form-control"
                        value="{{ old('value') }}" required>
                    <small class="text-muted fst-italic" id="msg_value" style="display:none;">No aplica para este tipo de
                        registro.</small>
                    @error('value')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- EVIDENCIA --}}
                <div class="form-group" id="group_evidence">
                    <label for="evidence">Evidencia <small class="text-muted fst-italic"></small></label>
                    <input type="file" name="evidence" id="evidence" class="form-control">
                    <small class="text-muted fst-italic" id="msg_evidence" style="display:none;">No es necesario subir
                        evidencia para este tipo de registro.</small>
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

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/browser-image-compression@2.0.2/dist/browser-image-compression.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectTipo = document.getElementById('record_type_id');

            const groupValue = document.getElementById('group_value');
            const inputValue = document.getElementById('value');
            const msgValue = document.getElementById('msg_value');

            const groupEvidence = document.getElementById('group_evidence');
            const inputEvidence = document.getElementById('evidence');
            const msgEvidence = document.getElementById('msg_evidence');

            function toggleFields() {
                const tipo = parseInt(selectTipo.value);

                // Valor
                if (tipo === 3) {
                    groupValue.style.display = 'none';
                    inputValue.value = '';
                    msgValue.style.display = 'inline';
                    inputValue.removeAttribute('required'); // <--- quitar required
                } else {
                    groupValue.style.display = 'block';
                    msgValue.style.display = 'none';
                    inputValue.setAttribute('required', 'required'); // <--- agregar required de nuevo
                }

                // Evidencia
                if (tipo === 2) {
                    groupEvidence.style.display = 'none';
                    inputEvidence.value = '';
                    msgEvidence.style.display = 'inline';
                    inputEvidence.removeAttribute('required'); // opcional si estaba required
                } else {
                    groupEvidence.style.display = 'block';
                    msgEvidence.style.display = 'none';
                    inputEvidence.setAttribute('required', 'required'); // si quieres que sea obligatorio
                }
            }

            // Ejecutar al cambiar tipo
            selectTipo.addEventListener('change', toggleFields);
            // Ejecutar al cargar, por si hay old()
            toggleFields();

            // =========================
            // COMPRESIÓN DE IMÁGENES
            // =========================
            inputEvidence.addEventListener('change', async function(event) {
                const file = event.target.files[0];
                if (!file) return;

                if (!['image/jpeg', 'image/png'].includes(file.type)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Formato no compatible',
                        text: 'Solo JPG o PNG.'
                    });
                    inputEvidence.value = '';
                    return;
                }

                if (file.size > 20 * 1024 * 1024) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Archivo muy grande',
                        text: 'Máximo permitido: 20 MB.'
                    });
                    inputEvidence.value = '';
                    return;
                }

                const options = {
                    maxSizeMB: 1.8,
                    maxWidthOrHeight: 1920,
                    useWebWorker: false,
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
                    const compressedFile = new File([compressedBlob], file.name, {
                        type: file.type
                    });

                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(compressedFile);
                    inputEvidence.files = dataTransfer.files;

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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectTipo = document.getElementById('record_type_id');
            const groupValue = document.getElementById('group_value');
            const inputValue = document.getElementById('value');
            const msgValue = document.getElementById('msg_value');
            const labelValue = document.getElementById('label_value');

            const groupEvidence = document.getElementById('group_evidence');
            const inputEvidence = document.getElementById('evidence');
            const msgEvidence = document.getElementById('msg_evidence');

            function toggleFields() {
                const tipo = parseInt(selectTipo.value);

                // Cambiar texto de label según tipo
                if (tipo === 1 || tipo === 4) {
                    labelValue.textContent = 'Valor del medidor en litros';
                } else if (tipo === 2) {
                    labelValue.textContent = 'Dinero en caja';
                } else {
                    labelValue.textContent = 'Valor';
                }

                // Valor
                if (tipo === 3) {
                    groupValue.style.display = 'none';
                    inputValue.value = '';
                    msgValue.style.display = 'inline';
                } else {
                    groupValue.style.display = 'block';
                    msgValue.style.display = 'none';
                }

                // Evidencia
                if (tipo === 2) {
                    groupEvidence.style.display = 'none';
                    inputEvidence.value = '';
                    msgEvidence.style.display = 'inline';
                } else {
                    groupEvidence.style.display = 'block';
                    msgEvidence.style.display = 'none';
                }
            }

            selectTipo.addEventListener('change', toggleFields);
            toggleFields(); // ejecutar al cargar
        });
    </script>
@stop
