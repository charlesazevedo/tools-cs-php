@extends('adminlte::page')

@section('title', 'Resumo do caixa')

@section('content_header')
    <h1>Resumo do caixa</h1>
@stop

@section('content')
    <div class="box" style="border: 1px solid #d2d6de; padding: 12px;">
        <h4>Saldo inicial: R$ {{ $saldoInicial }}</h4>
        <h4>Total de sangrias: R$ <span style="color: #dd4b39; font-weight: bold;">-{{ $totalSangrias }}</span></h4>
        <h4 style="margin-bottom: 0;">Saldo atual: R$ {{ $saldoAtual }}</h4>
    </div>
@stop
