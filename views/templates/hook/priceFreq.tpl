

{if ($type=='unit_price' || $hook_origin=='product_sheet') && $product.id_category_default == Configuration::get('EASYPAY_CATEGORY_SUSCP') }(Subscrição  


{foreach from=$product.features item=feature}
    {if $feature.id_feature==Configuration::get('EASYPAY_EXP_TIME')} - {$feature.value}{/if}
{/foreach}


){/if}

