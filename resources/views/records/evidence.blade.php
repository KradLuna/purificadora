@if($row->evidence_path)
    @php
        $url = asset('storage/'.$row->evidence_path);
        $ext = pathinfo($row->evidence_path, PATHINFO_EXTENSION);
    @endphp

    <div class="d-flex justify-content-center">
        @if(in_array(strtolower($ext), ['jpg','jpeg','png','gif']))
            {{-- Miniatura clickeable para modal --}}
            <img src="{{ $url }}" alt="evidence" style="height:40px; width:auto; border-radius:4px; cursor:pointer;"
                 data-toggle="modal" data-target="#evidenceModal" data-url="{{ $url }}">
        @else
            {{-- Botón para otros archivos --}}
            <a href="{{ $url }}" target="_blank" class="btn btn-sm btn-secondary">
                <i class="fas fa-file"></i> Ver
            </a>
        @endif
    </div>
@else
    <span class="text-muted">Sin evidencia</span>
@endif
