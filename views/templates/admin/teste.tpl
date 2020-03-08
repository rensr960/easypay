<style>
	.susc-table th{
		border: 1px solid gray;
		padding: 10px 15px;
		text-align: center;
	}
	.susc-table tr:nth-child(odd) {
	    background: #FBFAFF;
	    
	}
	.susc-table td{
		border: 1px solid rgba(0,0,0,0.15);
		padding: 10px 15px;
		text-align: center;
	}
</style>
<div class="panel">
	<div class="panel-heading"><i class="icon-gear"></i>Listagem de suscrições - EASYPAY</div>
	<div class="panel-body">
		
		<table class="susc-table">
			<tr>
    				<th>ID. ENCOMENDA</th>
    				<th>DT. INÍCIO</th>
    				<th>DT. FIM</th>
    				<th>FREQ</th>
    				<th>Nº. COB. EFETUAR</th>
    				<th>Nº. COB. EFETUADAS</th>
    				<th>VAL. SUBSCRIÇÃO</th>
    				<th>VAL. COBRADO</th>
    				<th>DT. ULT. COBRANÇA</th>
    				<th>ESTADO ATUAL</th>
    				<th>OPÇÕES</th>
			</tr>
			
			    {foreach from=$subs item=sub key=subc}
			    <tr>
			        {$respuesta = $sub.respuesta|json_decode}
    				<td>{$sub.id_order}</td>
    				<td>{$sub.dt_init}</td>
    				<td>{$sub.dt_fin}</td>
    				<td>{$sub.freq}</td>
    				<td>{$sub.n_cob_ef}</td>
    				<td>{$sub.n_cob_eftd}</td>
    				<td>{Tools::displayPrice(($sub.val_subs*$sub.n_cob_ef)|round:2)}</td>
    				<td>{Tools::displayPrice($sub.val_cobrado|round:2)}</td>
    				<td>{$sub.dt_ult_cob}</td>
    				<td>{$sub.estado_act}</td>
    				<td><a href="http://easypay.trigenius.pt/modules/easypay/cancelSub.php?id_sub={$respuesta->id}"><button class="btn btn-danger">Cancelar Subscição</button></a></td>
    			</tr>
				{/foreach}
			
		</table>

	</div>
</div>