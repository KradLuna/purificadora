<div class="d-flex justify-content-center">
    {{-- Botón Editar --}}
    <a href="{{ $canDoASale ? route('sales.edit', $sale->id) : '#' }}"
       class="btn btn-sm btn-warning mx-1 {{ $canDoASale ? '' : 'disabled pointer-events-none opacity-50' }}"
       {{ $canDoASale ? '' : 'aria-disabled=true' }}>
        <i class="fas fa-edit"></i>
    </a>

    {{-- Botón Eliminar --}}
    <form id="delete-form-{{ $sale->id }}" action="{{ route('sales.destroy', $sale->id) }}" method="POST" style="display:inline;">
        @csrf
        @method('DELETE')
        <button type="button"
                class="btn btn-sm btn-danger mx-1 {{ $canDoASale ? '' : 'disabled opacity-50' }}"
                {{ $canDoASale ? '' : 'disabled' }}
                onclick="{{ $canDoASale ? 'confirmDelete(' . $sale->id . ')' : 'return false;' }}">
            <i class="fas fa-trash"></i>
        </button>
    </form>
</div>
