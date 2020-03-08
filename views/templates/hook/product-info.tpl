{if $have_products_in_cart==1}
	{if $actual==0}
		{if $have_subs==1}
			<div style="padding: 10px 20px; background-color: rgba(255, 0, 0, .4); border: 1px solid rgba(255, 0, 0, .2); margin: 15px 0px">Para comprar este produto <b>não podes ter produtos de Subscrição</b> no Carrinho.</div>
			<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
			<script>$("button[data-button-action=add-to-cart]").attr("disabled", "true")</script>
		{/if}
	{else}
		{if $have_subs==0}
			<div style="padding: 10px 20px; background-color: rgba(255, 0, 0, .4); border: 1px solid rgba(255, 0, 0, .2); margin: 15px 0px">Para comprar este produto <b>não podes ter produtos de Não Subscrição</b> no Carrinho.</div>
			<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
			<script>$("button[data-button-action=add-to-cart]").attr("disabled", "true")</script>
		{/if}
	{/if}
{/if}