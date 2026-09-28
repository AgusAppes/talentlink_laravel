@if ($experiencias->isEmpty())
    <p class="sol-vacio">{{ $vacio }}</p>
@else
    <ul class="exp-lista">
        @foreach ($experiencias as $experiencia)
            <li class="exp-item">
                @if ($experiencia->puesto)
                    <p class="exp-puesto">{{ $experiencia->puesto }}</p>
                @endif
                <p class="exp-empresa">{{ $experiencia->empresa }}</p>
                @if ($experiencia->periodo() !== '')
                    <p class="exp-periodo">{{ $experiencia->periodo() }}</p>
                @endif
                @if ($experiencia->descripcion)
                    <p class="exp-texto">{!! nl2br(e($experiencia->descripcion)) !!}</p>
                @endif
                @if ($editable)
                    <form method="POST" action="{{ route('perfil.experiencias.destroy', $experiencia->id) }}" onsubmit="return confirm('¿Eliminar esta experiencia?')">
                        @csrf
                        @method('DELETE')
                        <button class="exp-eliminar" type="submit">Eliminar</button>
                    </form>
                @endif
            </li>
        @endforeach
    </ul>
@endif
