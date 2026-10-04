<button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="collapse" data-bs-target="#cancelar-{{ $entrevista->id }}" aria-expanded="false">Cancelar entrevista</button>
<div class="collapse {{ (int) old('entrevista_id') === (int) $entrevista->id ? 'show' : '' }}" id="cancelar-{{ $entrevista->id }}">
    <form class="mt-1" method="POST" action="{{ route('entrevistas.cancelar', $entrevista) }}">
        @csrf
        <input type="hidden" name="entrevista_id" value="{{ $entrevista->id }}">
        <label class="form-label small mb-1" for="motivo-{{ $entrevista->id }}">Motivo</label>
        <input class="form-control form-control-sm @if ((int) old('entrevista_id') === (int) $entrevista->id) @error('motivo') is-invalid @enderror @endif" type="text" id="motivo-{{ $entrevista->id }}" name="motivo" maxlength="500" required value="{{ (int) old('entrevista_id') === (int) $entrevista->id ? old('motivo') : '' }}">
        @if ((int) old('entrevista_id') === (int) $entrevista->id)
            @error('motivo')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        @endif
        <button class="btn btn-sm btn-outline-danger mt-1" type="submit">Confirmar cancelación</button>
    </form>
</div>
