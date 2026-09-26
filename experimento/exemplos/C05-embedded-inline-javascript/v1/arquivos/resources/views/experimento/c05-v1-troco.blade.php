@extends('adminlte::page')

@section('title', 'Troco')

@section('content_header')
    <h1>Cálculo de troco</h1>
@stop

@section('content')
    <div class="col-md-6">
        <label for="total-venda">Total da venda: R$</label>
        <input id="total-venda" class="form-control" value="{{ $totalVenda }}" readonly />
        <label for="valor-pago">Valor pago: R$</label>
        <input id="valor-pago" class="form-control" placeholder="Valor entregue pelo cliente" />
        <h3>Troco: R$ <span id="valor-troco">0,00</span></h3>
        <button id="confirmar-pagamento" class="btn btn-success" type="button" disabled>Confirmar</button>
    </div>
@stop

@section('js')
    <script>
        $(function () {
            function lerValor(campo) {
                return parseFloat($(campo).val().replace('.', '').replace(',', '.')) || 0;
            }

            $('#valor-pago').on('input', function () {
                var troco = lerValor('#valor-pago') - lerValor('#total-venda');
                var suficiente = troco >= 0;
                $('#valor-troco').text(suficiente ? troco.toFixed(2).replace('.', ',') : '0,00');
                $('#confirmar-pagamento').prop('disabled', !suficiente);
            });
        });
    </script>
@stop
