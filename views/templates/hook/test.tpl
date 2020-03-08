{if isset($tribusqueda)}


<section class="featured-products list-large" id="probandoTest" style="margin-top: {if $page.page_name=='index'}140px{else}30px{/if}">
<div class="container">
  <div class="col-lg-12">
<div class="row">



{************************INICIO FILTROS****************************}
<div class="col-sm-3" style="min-height: 600px; ">
  {hook h='displayLeftColumn' div_personalizado='.trisearch_produtos' personalizar="triSearch"}
  </div>

{************************FIN DE FILTROSSSS*********************}





{*****************INICIO PRODUCTS*********************************}
  <div class="col-sm-9" id="products">
  <div class="row">

    <div class="col-lg-6">
  

<div class="products-sort-order dropdown col mb-1">
<select class="trisorting" id="trisort" onChange="sort_products($('#trisort option:selected').val(), $('#trisort option:selected').data('order'));">
  <option>Relevância</option>
  <option value="nome" data-order="desc">Nome A-Z</option>
  <option value="nome" data-order="asc">Nome Z-A</option>
  <option value="price" data-order="desc">Preço do mais baixo ao mais alto</option>
  <option value="price" data-order="asc">Preço do mais alto ao mais baixo</option>
</select>
</div>

</div>
  </div>
    <div class="row">
    <div class="mb-3 col-md-12">

      <div class="products trisearch_produtos" id="Trisearch_Products_Show">
      
      
            {if !$products}<h3>Não foram encontrados produtos correspondentes. Lamentamos pelo inconveniente.</h3>{else}
            {foreach from=$products item="product"}
                  {include file="catalog/_partials/miniatures/product.tpl" product=$product}
            {/foreach}
            {/if}

      
            <div class="gap"></div>
            <div class="gap"></div>
            <div class="gap"></div>
      </div>
      <div class="col-md-12" style="">
        <div class="controls-pagination">
            <div class="mixitup-page-list col-lg-12"></div>
            <div class="mixitup-page-stats col-lg-12"></div>
        </div>
      </div>
    </div>
    </div>
  </div>
{*****************FIN PRODCUTS****************************************}








</div>
  </div>
  </div>
</section>






{/if}
