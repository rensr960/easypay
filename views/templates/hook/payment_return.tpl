{*
 * EasyPay, a module for Prestashop 1.7
 *
 * Form to be displayed in the payment step
 *}
{if isset($smarty.get.method) && $smarty.get.method=='mb'}
<h1>Obrigado por sua compra!</h1>
<P>Para fazer o pagamento deve dirigirse a um terminal multibanco e usar os seguentes dados:
</P>
<p/>Entidade: {$smarty.get.entity}</p>
<p>Referencia: {$smarty.get.reference|number_format:0:" ":" "}</p>
<p>Montante: {$smarty.get.monto}</p>
{/if}

{if isset($smarty.get.method) && $smarty.get.method=='dds'}
<h1>Obrigado por sua compra!</h1>
<P>Vai ser descontado da a sua conta a quantidade {$smarty.get.monto} € mensual por {$smarty.get.qtt} meses comenzando por hoje.
</P>
{/if}

{if isset($smarty.get.method) && $smarty.get.method=='cc'}
<h1>Obrigado por sua compra!</h1>
<P>Você será direccionado em breve para a Gateway de pagamento Cartão
de Crédito da Easypay.</p>
<a href="{$smarty.get.url|unescape:"htmlall"}"><button class="success">Ir agora</button></a>

<script>
    
    
    function redirect_url(){
        window.location.replace("{$smarty.get.url|unescape:'htmlall'}");
    }
    
   
        setTimeout(redirect_url,15000)
    
</script>
{/if}

{if isset($smarty.get.method) && $smarty.get.method=='bb'}
<h1>Obrigado por sua compra!</h1>
<P>Você será redirecionado em breve para efetuar o pagamento no easypay BOLETO</p>
<a href="{$smarty.get.url|unescape:"htmlall"}"><button class="success">Ir agora</button></a>

<script>
    
    
    function redirect_url(){
        window.location.replace("{$smarty.get.url|unescape:'htmlall'}");
    }
    
   
        setTimeout(redirect_url,15000)
    
</script>
{/if}

{if isset($smarty.get.method) && $smarty.get.method=='mbw'}
<h1>Obrigado por sua compra!</h1>
<P>Deve fazer o pagamento por MBWAY desde seu telemovel.</P>

{/if}
