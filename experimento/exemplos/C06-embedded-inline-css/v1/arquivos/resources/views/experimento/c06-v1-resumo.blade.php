@extends('adminlte::page')

@section('title', 'Resumo do caixa')

@section('css')
    <style>
        .resumo-caixa {
            border: 1px solid #d2d6de;
            padding: 12px;
        }

        .resumo-caixa .valor-negativo {
            color: #dd4b39;
            font-weight: bold;
        }
    </style>
@stop

@section('content_header')
    <h1>Resumo do caixa</h1>
@stop

@section('content')
    <div class="resumo-caixa">
        <h4>Saldo inicial: R$ {{ $saldoInicial }}</h4>
        <h4>Total de sangrias: R$ <span class="valor-negativo">-{{ $totalSangrias }}</span></h4>
        <h4>Saldo atual: R$ {{ $saldoAtual }}</h4>
    </div>
@stop
