@extends('adminlte::page')

@section('title', 'Sangria')

@section('content_header')
    <h1>Retirada do caixa</h1>
@stop

@section('content')
    <div id="mensagem-movimentacao" class="alert" role="alert"></div>
    <form id="form-movimentacao" method="post" action="{{ route('sangria') }}">
        {{ csrf_field() }}
        <label for="valor-sangria">Valor da retirada: R$</label>
        <input id="valor-sangria" name="valor" class="form-control" required />
        <label for="descricao-sangria">Motivo da retirada</label>
        <input id="descricao-sangria" name="descricao" class="form-control" required />
        <button class="btn btn-danger" type="submit">Retirar</button>
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
