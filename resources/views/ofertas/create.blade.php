@extends('layouts.app')

@section('title', 'Publicar oferta')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/solicitudes.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ofertas.css') }}">
@endpush

@section('content')
    <section class="tl-card">
        @if ($solicitudes->isEmpty())
            <p class="sol-vacio">No hay solicitudes pendientes de publicar.</p>
            <a href="{{ route('solicitudes.index') }}">Volver a solicitudes</a>
        @else
            <form method="POST" action="{{ route('ofertas.store') }}">
                @csrf

                <div class="sol-campo">
                    <label for="busquedas_id">Solicitud</label>
                    <select id="busquedas_id" name="busquedas_id" required>
                        <option value="">Seleccioná una solicitud</option>
                        @foreach ($solicitudes as $solicitud)
                            <option value="{{ $solicitud->id }}" @selected((string) old('busquedas_id', $busquedaPreseleccionada) === (string) $solicitud->id)>
                                {{ $solicitud->nombre_puesto }} — {{ $solicitud->empresa->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('busquedas_id')
                        <p class="sol-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sol-campo">
                    <label for="estado_ofertas_id">Estado</label>
                    <select id="estado_ofertas_id" name="estado_ofertas_id" required>
                        @foreach ($estados as $estado)
                            <option value="{{ $estado->id }}" @selected((string) old('estado_ofertas_id', '1') === (string) $estado->id)>
                                {{ $estado->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('estado_ofertas_id')
                        <p class="sol-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sol-campo">
                    <label>
                        <input type="checkbox" name="requiere_cv" value="1" @checked(old('requiere_cv'))>
                        Requerir CV
                    </label>
                    <p class="sol-ayuda">Si lo marcás, el candidato deberá adjuntar su CV en PDF para postularse.</p>
                    @error('requiere_cv')
                        <p class="sol-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sol-acciones">
                    <button class="sol-btn" type="submit">Publicar</button>
                    <a class="sol-btn-sec" href="{{ route('ofertas.index') }}">Cancelar</a>
                </div>
            </form>
        @endif
    </section>
@endsection
