@extends('adminlte::page')

@section('title', 'Movimentações')

@section('content_header')
    <h1>Movimentações do dia</h1>
@stop

@section('content')
    <table id="tabela-movimentacoes" class="table table-bordered">
        <thead>
            <tr>
                <th>Descrição</th>
                <th>Valor R$</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($movimentacoes as $movimentacao)
                <tr>
                    <td>{{ $movimentacao->descricao }}</td>
                    <td class="{{ $movimentacao->valor < 0 ? 'saldo-negativo' : '' }}">{{ $movimentacao->valor }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@stop

@section('js')
    <script>
        $(function () {
            $('#tabela-movimentacoes .saldo-negativo').css('color', '#dd4b39').css('font-weight', 'bold');
        });
    </script>
@stop
