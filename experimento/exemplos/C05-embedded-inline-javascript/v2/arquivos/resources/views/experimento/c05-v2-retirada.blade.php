@extends('adminlte::page')

@section('title', 'Retirada')

@section('content_header')
    <h1>Retirada do caixa</h1>
@stop

@section('content')
    <div class="col-md-12">
        <form id="form-retirada" method="post" action="{{ route('sangria') }}"
              onsubmit="return confirm('Confirmar a retirada do caixa?');">
            {{ csrf_field() }}
            <label for="valor-retirada">Valor da retirada: R$</label>
            <input id="valor-retirada" name="valor" class="form-control"
                   onblur="this.value = this.value.trim();" required />
            <label for="descricao-retirada">Descrição</label>
            <input id="descricao-retirada" name="descricao" class="form-control" required />
            <button class="btn btn-success" type="submit">Retirar</button>
            <button class="btn btn-default" type="button"
                    onclick="document.getElementById('form-retirada').reset();">Limpar</button>
        </form>
    </div>
@stop
