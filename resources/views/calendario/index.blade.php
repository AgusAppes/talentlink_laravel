@extends('layouts.app')

@section('title', 'Calendario')

@section('content')
    @if ($editar)
        @include('calendario.grilla')
    @else
        @include('calendario.semana')
    @endif
@endsection
