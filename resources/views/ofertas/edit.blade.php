@extends('layouts.app')

@section('title', 'Editar oferta')

@section('content')
    <section class="card shadow-sm border-0">
        <div class="card-body">
            <p class="mb-2"><span class="d-block text-secondary small">Puesto</span><span class="fw-semibold">{{ $oferta->busqueda->nombre_puesto }}</span></p>
            <p class="mb-3"><span class="d-block text-secondary small">Empresa</span><span class="fw-semibold">{{ $oferta->busqueda->empresa->nombre }}</span></p>

            <form method="POST" action="{{ route('ofertas.update', $oferta) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label" for="estado_ofertas_id">Estado</label>
                    <select class="form-select" id="estado_ofertas_id" name="estado_ofertas_id" required>
                        @foreach ($estados as $estado)
                            <option value="{{ $estado->id }}" @selected((string) old('estado_ofertas_id', $oferta->estado_ofertas_id) === (string) $estado->id)>
                                {{ $estado->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="requiere_cv" name="requiere_cv" value="1" @checked(old('requiere_cv', $oferta->requiere_cv))>
                        <label class="form-check-label" for="requiere_cv">Requerir CV</label>
                    </div>
                    <p class="form-text">Si lo marcás, el candidato deberá adjuntar su CV en PDF para postularse.</p>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Guardar</button>
                    <a class="btn btn-outline-secondary" href="{{ route('ofertas.index') }}">Cancelar</a>
                </div>
            </form>
        </div>
    </section>
@endsection
