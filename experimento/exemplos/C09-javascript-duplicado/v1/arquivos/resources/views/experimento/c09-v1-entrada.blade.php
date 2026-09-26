@extends('adminlte::page')

@section('title', 'Entrada')

@section('content_header')
    <h1>Entrada no caixa</h1>
@stop

@section('content')
    <div id="mensagem-movimentacao" class="alert" role="alert"></div>
    <form id="form-movimentacao" method="post" action="{{ route('caixa.add') }}">
        {{ csrf_field() }}
        <label for="valor-entrada">Valor da entrada: R$</label>
        <input id="valor-entrada" name="valor" class="form-control" required />
        <label for="descricao-entrada">Origem do valor</label>
        <input id="descricao-entrada" name="descricao" class="form-control" required />
        <button class="btn btn-success" type="submit">Adicionar</button>
    </form>
@stop

@section('js')
    <script>
        $(function () {
            $('#form-movimentacao').submit(function (evento) {
                evento.preventDefault();
                var formulario = $(this);
                $.post(formulario.attr('action'), formulario.serialize(), function (resposta) {
                    var sucesso = resposta.success === 'true';
                    $('#mensagem-movimentacao')
                        .toggleClass('alert-success', sucesso)
                        .toggleClass('alert-danger', !sucesso)
                        .text(resposta.message);
                    if (sucesso) {
                        formulario.trigger('reset');
                    }
                }, 'json');
            });
        });
    </script>
@stop
