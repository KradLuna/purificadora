<div class="d-flex justify-content-between align-items-center mb-3">
    <!-- Título a la izquierda -->
    <h1 class="m-0 text-dark">{{ $title }}</h1>

    <!-- Usuario y botón a la derecha -->
    <div class="d-flex flex-column align-items-end text-right">
        <span class="text-primary font-weight-bold mb-1">
            <i class="fas fa-user mr-1"></i> {{ auth()->user()->full_name }}
        </span>
        <form action="{{ route('logout') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-danger">
                <i class="fas fa-sign-out-alt"></i> Cerrar sesión
            </button>
        </form>
    </div>
</div>
