<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
if(!isset($search)){$search="";}
if(!isset($search_category_count)){$search_category_count="";}
if(!isset($search_products_count)){$search_products_count="";}
if(!isset($search_suppliers_count)){$search_suppliers_count="";}
if(!isset($search_category)){$search_category="";}
if(!isset($search_products)){$search_products="";}
if(!isset($search_suppliers)){$search_suppliers="";}
?>
<div class="res-block shadow-bottom-light">
    <h5>по запросу <span class="badge"><?=$search ?></span></h5>
    <hr>
    <div class="row">
        <div class="search-block">
            <div class="col-md-4">
                <div class="search-block-1">
                <h5 class="title-search-result">Категорий <span class="badge"><?=$search_category_count;?></span></h5>
                <?php 
                if(!empty($search_category)){
                foreach ($search_category as $arr) {
                ?>
                  <div class="media media-block" >
                    <div class="media-left media-middle">
                      <a href="#">
                        <img alt="64x64" class="search-result-image" data-holder-rendered="true" style="width: 114px; height: 64px;" class="media-object" src="<?=$arr['_source']['category_name']?>">
                      </a>
                    </div>
                    <div class="media-body">
                      <h5 class="media-heading"><?=$arr['_source']['category_name']?></h5>                   
                    </div>
                  </div>
                <?php
                }
                if($search_category_count>4){
                ?>
                  <div class="row">
                    <div class="col-md-12" style="margin-top: 10px">
                      <a href="#" class="btn btn-outline-ajax">Показать еще</a>  
                    </div>   
                  </div>
                <?php
                }
                }
                ?>
                </div>
            </div>
            <div class="col-md-4">
                <div class="search-block-1">
                <h5 class="title-search-result">Товаров <span class="badge"><?=$search_products_count;?></span></h5>
                <?php 
                if(!empty($search_products)){
                foreach ($search_products as $arr) {
                ?>
                  <div class="media media-block" >
                    <div class="media-left media-middle">
                      <a href="#">
                        <img alt="64x64" class="search-result-image" data-holder-rendered="true" style="width: 114px; height: 64px;" class="media-object" src="<?=$arr['_source']['product_image']?>">
                      </a>
                    </div>
                    <div class="media-body">
                      <h5 class="media-heading"><?=$arr['_source']['product_name']?></h5>
                      <h5 class="media-price"><?=$arr['_source']['product_price']?></h5>                    
                    </div>
                  </div>
                <?php
                }
                if($search_products_count>4){
                ?>
                  <div class="row">
                    <div class="col-md-12" style="margin-top: 10px">
                      <a href="#" class="btn btn-outline-ajax">Показать еще</a>  
                    </div>   
                  </div>
                <?php
                    }
                }
                ?>
                </div>
            </div>
            <div class="col-md-4">
                <div class="search-block-1">
                <h5 class="title-search-result">Поставщиков <span class="badge"><?=$search_suppliers_count;?></span></h5>
                <?php 
                if(!empty($search_suppliers)){
                foreach ($search_suppliers as $arr) {
                ?>
                  <div class="media media-block" >
                    <div class="media-left media-middle">
                      <a href="#">
                        <img alt="64x64" class="search-result-image" data-holder-rendered="true" style="width: 114px; height: 64px;" class="media-object" src="<?=$arr['_source']['supplier_image']?>">
                      </a>
                    </div>
                    <div class="media-body">
                      <h5 class="media-heading"><?=$arr['_source']['supplier_name']?></h5>                 
                    </div>
                  </div>
                <?php
                }
                if($search_suppliers_count>4){
                ?>
                  <div class="row">
                    <div class="col-md-12" style="margin-top: 10px">
                      <a href="#" class="btn btn-outline-ajax">Показать еще</a>  
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
<!--div class="row">
    <div class="search-block">
        <div class="col-md-4">
            <div class="search-block-1">
            <h5 class=\"text-center\">Категории <span class='label label-success search-val'><?=$search_products_count?></span></h5>
            </div>
        </div>
        <div class="col-md-4">
            <div class="search-block-1">
            <h5>Товары <span class='label label-success search-val'><?=$search_products_count?></span></h5>
            <?php
            if(!empty($search_products)){
            foreach ($search_products as $arr) {
                ?>
                <div class="media" style="border-bottom:1px solid #eee;padding-bottom: 10px;">
                    <div class="media-left media-middle">
                      <a href="#">
                        <img alt="64x64" data-holder-rendered="true" style="width: 64px; height: 64px;" class="media-object" src="<?=$arr['_source']['product_image']?>" alt="...">
                      </a>
                    </div>
                    <div class="media-body">
                      <h5 class="media-heading"><?=$arr['_source']['product_name']?></h5>
                      <?=$arr['_source']['product_price']?>
                    </div>
                  </div>
                <?php
                }
            }
            ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="search-block-1">
            <h5 class=\"text-center\">Поставщики <span class='label label-success search-val'><?=$search_suppliers_count?></span></h5>
            <?php
            if(!empty($search_suppliers)){
            foreach ($search_suppliers as $arr) {
                ?>
                <div class="media" style="border-bottom:1px solid #eee;padding-bottom: 10px;">
                    <div class="media-left media-middle">
                      <a href="#">
                        <img alt="64x64" data-holder-rendered="true" style="width: 64px; height: 64px;" class="media-object" src="<?=$arr['_source']['supplier_image']?>" alt="...">
                      </a>
                    </div>
                    <div class="media-body">
                      <h5 class="media-heading"><?=$arr['_source']['supplier_name']?></h5>
                    </div>
                  </div>
                <?php
                }
            }
            ?>
            </div>
        </div>
    </div>
</div-->
