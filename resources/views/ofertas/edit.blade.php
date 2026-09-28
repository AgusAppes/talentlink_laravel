@extends('layouts.app')

@section('title', 'Editar oferta')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/solicitudes.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ofertas.css') }}">
@endpush

@section('content')
    <section class="tl-card">
        <p class="ofe-fijo"><span>Puesto</span> {{ $oferta->busqueda->nombre_puesto }}</p>
        <p class="ofe-fijo"><span>Empresa</span> {{ $oferta->busqueda->empresa->nombre }}</p>

        <form method="POST" action="{{ route('ofertas.update', $oferta) }}">
            @csrf
            @method('PUT')

            <div class="sol-campo">
                <label for="estado_ofertas_id">Estado</label>
                <select id="estado_ofertas_id" name="estado_ofertas_id" required>
                    @foreach ($estados as $estado)
                        <option value="{{ $estado->id }}" @selected((string) old('estado_ofertas_id', $oferta->estado_ofertas_id) === (string) $estado->id)>
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
                    <input type="checkbox" name="requiere_cv" value="1" @checked(old('requiere_cv', $oferta->requiere_cv))>
                    Requerir CV
                </label>
                <p class="sol-ayuda">Si lo marcás, el candidato deberá adjuntar su CV en PDF para postularse.</p>
                @error('requiere_cv')
                    <p class="sol-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="sol-acciones">
                <button class="sol-btn" type="submit">Guardar</button>
                <a class="sol-btn-sec" href="{{ route('ofertas.index') }}">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
