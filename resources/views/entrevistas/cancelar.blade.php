<button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="collapse" data-bs-target="#cancelar-{{ $entrevista->id }}" aria-expanded="false">Cancelar entrevista</button>
<div class="collapse" id="cancelar-{{ $entrevista->id }}">
    <form class="mt-1" method="POST" action="{{ route('entrevistas.cancelar', $entrevista) }}">
        @csrf
        <label class="form-label small mb-1" for="motivo-{{ $entrevista->id }}">Motivo</label>
        <input class="form-control form-control-sm" type="text" id="motivo-{{ $entrevista->id }}" name="motivo" maxlength="500" required>
        <button class="btn btn-sm btn-outline-danger mt-1" type="submit">Confirmar cancelación</button>
    </form>
</div>
