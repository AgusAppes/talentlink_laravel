@if ($experiencias->isEmpty())
    <p class="text-center text-secondary py-4 mb-0">{{ $vacio }}</p>
@else
    <ul class="exp-lista">
        @foreach ($experiencias as $experiencia)
            <li class="exp-item">
                @if ($experiencia->puesto)
                    <p class="fw-semibold mb-0">{{ $experiencia->puesto }}</p>
                @endif
                <p class="text-secondary small mb-0 mt-1">{{ $experiencia->empresa }}</p>
                @if ($experiencia->periodo() !== '')
                    <p class="text-secondary small mb-0 mt-1">{{ $experiencia->periodo() }}</p>
                @endif
                @if ($experiencia->descripcion)
                    <p class="mb-0 mt-1">{!! nl2br(e($experiencia->descripcion)) !!}</p>
                @endif
                @if ($editable)
                    <form method="POST" action="{{ route('perfil.experiencias.destroy', $experiencia->id) }}" onsubmit="return confirm('¿Eliminar esta experiencia?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger mt-2" type="submit">Eliminar</button>
                    </form>
                @endif
            </li>
        @endforeach
    </ul>
@endif
