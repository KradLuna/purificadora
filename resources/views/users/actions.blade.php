<div class="d-flex justify-content-center">
    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-warning mx-1">
        <i class="fas fa-edit"></i>
    </a>
    <form id="delete-form-{{ $user->id }}" action="{{ route('users.destroy', $user->id) }}" method="POST"
        style="display:inline;">
        @csrf
        @method('DELETE')
        <button type="button" class="btn btn-sm btn-danger mx-1" onclick="confirmDelete({{ $user->id }})">
            <i class="fas fa-trash"></i>
        </button>
    </form>
</div>
