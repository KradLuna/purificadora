@php
    $user = auth()->user();
@endphp

<div class="d-flex justify-content-center">
    {{-- Ver --}}
    <a href="{{ route('records.show', $row->id) }}" class="btn btn-sm btn-info">
        <i class="fas fa-eye"></i>
    </a>

    {{-- Editar: solo dueño o admin --}}
    @if($user->id === $row->user_id || $user->isAdmin)
        <a href="{{ route('records.edit', $row->id) }}" class="btn btn-sm btn-warning mx-1">
            <i class="fas fa-edit"></i>
        </a>
    @endif

    {{-- Eliminar: solo admin --}}
    @role('administrador')
        <form id="delete-form-{{ $row->id }}" action="{{ route('records.destroy', $row->id) }}" method="POST"
              style="display:inline;">
            @csrf
            @method('DELETE')
            <button type="button" class="btn btn-sm btn-danger mx-1" onclick="confirmDelete({{ $row->id }})">
                <i class="fas fa-trash"></i>
            </button>
        </form>
    @endrole
</div>
