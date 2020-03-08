<style>
   .table-pagamentos{
       width: 80%;
       text-align: center!important;
       border: 1px solid #cccccc;
   } 
   .table-pagamentos tr:nth-child(odd){
       background-color: rgb(240, 240, 240);
   }
   .table-pagamentos th{
       padding: 5px 0px;
   }
</style>
{if $metodo == 'cc'}
<div class="row">
    <div class="col-lg-12">
        <div class="col-lg-12 box">
            <h2>Dados de pagamento</h2>
            {if $status != 'ok'}Caso não tenha concluído a operação com sucesso <a href="{$url_l}" class="btn btn-primary pointer">clique aqui!</a>
            {else}
                O Seu pagamento foi feito com successo.
            {/if}
        </div>
    </div>
</div>
{/if}
{if $metodo == 'mb'}
<div class="row">
    <div class="col-lg-12">
        <div class="col-lg-12 box">
            <h2>Dados de pagamento</h2>
            {if $status != 'ok'}Se ainda não fez o pagamento, por favor dirija-se a um terminal multibanco e utilize os seguintes dados:
                <ul>
                    <li><b>Entidade:</b> {$entidade}</li>
                    <li><b>Referencia:</b> {$referencia}</li>
                    <li><b>Montante:</b> {Tools::displayPrice($montante)}</li>
                </ul>
            
            {else}
                O Seu pagamento foi feito com successo.
            {/if}
        </div>
    </div>
</div>
{/if}
{if $metodo == 'bb'}
<div class="row">
    <div class="col-lg-12">
        <div class="col-lg-12 box">
            <h2>Dados de pagamento</h2>
            {if $status != 'ok'}Caso não tenha concluído a operação com sucesso <a href="{$url_l}" class="btn btn-primary pointer">clique aqui!</a>
            {else}
                O Seu pagamento foi feito com successo.
            {/if}
        </div>
    </div>
</div>
{/if}



{if $metodo == 'dd' && $pagamentos->transactions|@count > 0}
<div class="row">
    <div class="col-lg-12">
        <div class="col-lg-12 box">
            <h2>Dados de pagamento - <a href="{$linki}modules/easypay/cancelSub.php?id_sub={$pagamentos->id}&url_v={$smarty.server.HTTP_HOST}{$smarty.server.REQUEST_URI}"><button>Cancelar Subscrição</button></a></h2>
            <table class="table-pagamentos">
                <tr>
                    <th style="text-align: center">Nº FATURA</th>
                    <th style="text-align: center">DATA</th>
                    <th style="text-align: center">TOTAL</th>
                </tr>
                {foreach from=$pagamentos->transactions item=pagamento}
                    <tr>
                        <td>{$pagamento->document_number}</td>
                        <td>{$pagamento->date}</td>
                        <td>{$pagamento->values->paid} {$pagamentos->currency}</td>
                    </tr>
                {/foreach}
            </table>
            
        </div>
    </div>
</div>
{/if}
