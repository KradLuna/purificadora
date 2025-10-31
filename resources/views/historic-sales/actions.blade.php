<div class="d-flex justify-content-center">
    <a href="{{ route('historic-sales.edit', $row->id) }}" class="btn btn-sm btn-warning">
        <i class="fas fa-edit"></i>
    </a>
    <form id="delete-form-{{ $row->id }}" action="{{ route('historic-sales.destroy', $row->id) }}" method="POST"
        style="display:inline;">
        @csrf
        @method('DELETE')
        <button type="button" class="btn btn-sm btn-danger mx-1" onclick="confirmDelete({{ $row->id }})">
            <i class="fas fa-trash"></i>
        </button>
    </form>
</div>
