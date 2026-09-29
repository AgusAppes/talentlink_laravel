@extends('layouts.app')

@section('title', 'Publicar oferta')

@section('content')
    <section class="card shadow-sm border-0">
        <div class="card-body">
            @if ($solicitudes->isEmpty())
                <p class="text-center text-secondary py-4 mb-2">No hay solicitudes pendientes de publicar.</p>
                <div class="text-center pb-2">
                    <a class="btn btn-outline-secondary" href="{{ route('solicitudes.index') }}">Volver a solicitudes</a>
                </div>
            @else
                <form method="POST" action="{{ route('ofertas.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label" for="busquedas_id">Solicitud</label>
                        <select class="form-select @error('busquedas_id') is-invalid @enderror" id="busquedas_id" name="busquedas_id" required>
                            <option value="">Seleccioná una solicitud</option>
                            @foreach ($solicitudes as $solicitud)
                                <option value="{{ $solicitud->id }}" @selected((string) old('busquedas_id', $busquedaPreseleccionada) === (string) $solicitud->id)>
                                    {{ $solicitud->nombre_puesto }} — {{ $solicitud->empresa->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('busquedas_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="estado_ofertas_id">Estado</label>
                        <select class="form-select @error('estado_ofertas_id') is-invalid @enderror" id="estado_ofertas_id" name="estado_ofertas_id" required>
                            @foreach ($estados as $estado)
                                <option value="{{ $estado->id }}" @selected((string) old('estado_ofertas_id', '1') === (string) $estado->id)>
                                    {{ $estado->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('estado_ofertas_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input @error('requiere_cv') is-invalid @enderror" type="checkbox" id="requiere_cv" name="requiere_cv" value="1" @checked(old('requiere_cv'))>
                            <label class="form-check-label" for="requiere_cv">Requerir CV</label>
                            @error('requiere_cv')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <p class="form-text">Si lo marcás, el candidato deberá adjuntar su CV en PDF para postularse.</p>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" type="submit">Publicar</button>
                        <a class="btn btn-outline-secondary" href="{{ route('ofertas.index') }}">Cancelar</a>
                    </div>
                </form>
            @endif
        </div>
    </section>
@endsection
