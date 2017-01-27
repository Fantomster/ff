<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
if(!isset($search)){$search="";}
if(!isset($search_categorys_count)){$search_categorys_count="";}
if(!isset($search_products_count)){$search_products_count="";}
if(!isset($search_suppliers_count)){$search_suppliers_count="";}
if(!isset($search_categorys)){$search_categorys="";}
if(!isset($search_products)){$search_products="";}
if(!isset($search_suppliers)){$search_suppliers="";}
$i=0;
if(!empty($search_categorys_count)){ $i++;}
if(!empty($search_products_count)){ $i++;}
if(!empty($search_suppliers_count)){ $i++;}
?>
<div class="search-result-block <?php
if(empty($i)){
    
}else{
    if($i==1){echo "col-md-6";}
    if($i==2){echo "col-md-8";}
    if($i==3){echo "col-md-12";}
}
?>">
  <div id="result">
    <div class="res-block shadow-bottom-light">
        <h5>по запросу <span class="badge"><?=$search ?></span></h5>
        <hr>
        <div class="row">
            <div class="search-block">
                <div class="<?php 
                if(empty($search_categorys_count)){
                    echo "hide";
                }else{
                    if($i==1){echo "col-md-12";}
                    if($i==2){echo "col-md-6";}
                    if($i==3){echo "col-md-4";}
                };?>">
                    <div class="search-block-1">
                    <h5 class="title-search-result">Категорий <span class="badge"><?=$search_categorys_count;?></span></h5>
                    <?php 
                    if(!empty($search_categorys)){
                    foreach ($search_categorys as $arr) {
                    ?>
                      <div class="media media-block animated fadeInUp" >
                        <div class="media-left media-middle">
                          <a href="<?=Url::to(['/site/category', 'id' => $arr['_source']['category_sub_id']]);?>">
                            <img alt="64x64" class="search-result-image" data-holder-rendered="true" style="width: 114px; height: 64px;" class="media-object" 
                                 src="<?=Url::to('@web/fmarket/images/image-category/'.$arr['_source']['category_id'].".jpg", true)?>">
                          </a>
                        </div>
                        <div class="media-body">
                          <a href="<?=Url::to(['/site/category', 'id' => $arr['_source']['category_sub_id']]);?>">
                          <h5 class="media-heading"><?=$arr['_source']['category_name']?></h5>    
                          </a>
                        </div>
                      </div>
                    <?php
                    }
                    }
                    ?>
                    </div>
                </div>
                <div class="<?php 
                if(empty($search_products_count)){
                    echo "hide";
                }else{
                    if($i==1){echo "col-md-12";}
                    if($i==2){echo "col-md-6";}
                    if($i==3){echo "col-md-4";}
                };?>">
                    <div class="search-block-1">
                    <h5 class="title-search-result">Товаров <span class="badge"><?=$search_products_count;?></span></h5>
                    <?php 
                    if(!empty($search_products)){
                    foreach ($search_products as $arr) {
                    ?>
                      <div class="media media-block animated fadeInUp" >
                        <div class="media-left media-middle">
                          <a href="<?=Url::to(['/site/product', 'id' => $arr['_source']['product_id']]);?>">
                            <img alt="64x64" class="search-result-image" data-holder-rendered="true" style="width: 114px; height: 64px;" class="media-object" 
                                 src="<?=empty($arr['_source']['product_image'])?
                    Url::to('@web/fmarket/images/image-category/'.$arr['_source']['product_category_id'].".jpg", true)
                    :$arr['_source']['product_image'] ?>">
                          </a>
                        </div>
                        <div class="media-body">
                          <a href="<?=Url::to(['/site/product', 'id' => $arr['_source']['product_id']]);?>">
                          <h5 class="media-heading"><?=$arr['_source']['product_name']?></h5>
                          </a>
                            <?php if (empty($arr['_source']['product_show_price'])){ ?>
                            <h5 class="media-price" style="color: #dfdfdf">договорная цена</h5>
                            <?php } else {?>
                            <h5 class="media-price"><?=floatval($arr['_source']['product_price']); ?> <small>руб.</small></h5>
                            <?php } ?>                 
                        </div>
                      </div>
                    <?php
                    }
                    if($search_products_count>4){
                    ?>
                      <div class="row">
                        <div class="col-md-12" style="margin-top: 10px">
                            
                          <a href="<?=Url::to(['/site/search-products', 'search' => $search]);?>" class="btn btn-outline-ajax">Показать еще</a>  
                        </div>   
                      </div>
                    <?php
                        }
                    }
                    ?>
                    </div>
                </div>
                <div class="<?php 
                if(empty($search_suppliers_count)){
                    echo "hide";
                }else{
                    if($i==1){echo "col-md-12";}
                    if($i==2){echo "col-md-6";}
                    if($i==3){echo "col-md-4";}
                };?>">
                    <div class="search-block-1">
                    <h5 class="title-search-result">Поставщиков <span class="badge"><?=$search_suppliers_count;?></span></h5>
                    <?php 
                    if(!empty($search_suppliers)){
                    foreach ($search_suppliers as $arr) {
                    ?>
                      <div class="media media-block animated fadeInUp" >
                        <div class="media-left media-middle">
                          <a href="<?=Url::to(['/site/supplier', 'id' => $arr['_source']['supplier_id']]);?>">
                            <img alt="64x64" class="search-result-image" data-holder-rendered="true" style="width: 114px; height: 64px;" class="media-object" 
                                 src="<?=empty($arr['_source']['supplier_image'])?\common\models\ES\Product::putNoImage():$arr['_source']['supplier_image'] ?>">
                          </a>
                        </div>
                        <div class="media-body">
                          <a href="<?=Url::to(['/site/supplier', 'id' => $arr['_source']['supplier_id']]);?>">
                          <h5 class="media-heading"><?=$arr['_source']['supplier_name']?></h5>  
                          </a>
                        </div>
                      </div>
                    <?php
                    }
                    if($search_suppliers_count>4){
                    ?>
                      <div class="row">
                        <div class="col-md-12" style="margin-top: 10px">
                          <a href="<?=Url::to(['/site/search-suppliers', 'search' => $search]);?>" class="btn btn-outline-ajax">Показать еще</a>  
                        </div>   
                      </div>
                    <?php
                    }
                    }
                    ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>
</div>
<?php $customJs = <<< JS
if($('#result .row').height() > $( window ).height()){
   $('.res-block').css('height',$( window ).height()-130) 
   }else{
   $('.res-block').css('height',$('#result .row').height()+130)     
}
JS;
$this->registerJs($customJs, View::POS_READY);
?>