@extends('adminlte::page')

@section('title', 'Caixa fechado')

@section('content_header')
    <h1>Vendas</h1>
@stop

@section('content')
    <div id="aviso-caixa-fechado" class="alert">O caixa está fechado. Abra o caixa antes de registrar vendas.</div>
@stop

@section('js')
    <script>
        window.addEventListener('load', function () {
            document.getElementById('aviso-caixa-fechado').style.backgroundColor = '#f39c12';
            document.getElementById('aviso-caixa-fechado').style.color = '#ffffff';
        });
    </script>
@stop
