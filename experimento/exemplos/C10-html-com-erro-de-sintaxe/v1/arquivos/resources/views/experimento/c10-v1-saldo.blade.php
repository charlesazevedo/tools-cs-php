@extends('adminlte::page')

@section('title', 'Saldo do caixa')

@section('content_header')
    <h1>Saldo do caixa</h1>
@stop

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">Saldo em {{ $data }}</div>
        <div class="panel-body">
            <h4>Saldo inicial: R$ {{ $saldoInicial }}</h4>
            <h4>Saldo atual: R$ {{ $saldoAtual }}</h4>
    </div>
@stop
