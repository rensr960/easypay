<style>
	.module-categ{
		margin-left: 10px;
	}
</style>

<div class="categorias-tri">




</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

<script>

	test = {$categorias|@json_encode nofilter};


	for(x in test){



		for(y in test[x]){



			if($('#subde'+test[x][y].id_parent).length > 0){


				$('#subde'+test[x][y].id_parent).html($('#subde'+test[x][y].id_parent).html()+'<div class="module-categ" id="subde'+test[x][y].id_category+'"><div class="col-md-12 contan codule-categ subde'+test[x][y].id_category+'" style="padding-top: 3px; padding-bottom: 3px"><input class="checkbox-triPesquisa" type="checkbox" value=".id-category-'+test[x][y].id_category+'" id="id-category-'+test[x][y].id_category+'" name="id-category-'+test[x][y].id_category+'"><label id="label-id-category-'+test[x][y].id_category+'" class="filter-text" for="id-category-'+test[x][y].id_category+'"><span class="checkmark"></span> '+test[x][y].name+'</label></div></div>');

			}
			else{
				$('.categorias-tri').html($('.categorias-tri').html()+'<div class="module-categ" id="subde'+test[x][y].id_category+'"><div class="col-md-12 contan codule-categ subde'+test[x][y].id_category+'" style="padding-top: 3px; padding-bottom: 3px"><input class="checkbox-triPesquisa" type="checkbox" value=".id-category-'+test[x][y].id_category+'" id="id-category-'+test[x][y].id_category+'" name="id-category-'+test[x][y].id_category+'"><label id="label-id-category-'+test[x][y].id_category+'" class="filter-text" for="id-category-'+test[x][y].id_category+'"><span class="checkmark"></span> '+test[x][y].name+'</label></div></div>');
			}

		}

	}

</script>