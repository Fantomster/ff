<?php
use yii\helpers\Html;
$count_products_from_mp = \common\models\CatalogBaseGoods::find()->where(['market_place'=>1,'deleted'=>0])->count();
$left_menu_categorys = \common\models\MpCategory::find()->select('id,name,parent')->where(['parent'=>NULL])->asArray()->all();
$left_menu_categorys_sub = \common\models\MpCategory::find()->select('id,name,parent,')->where('parent is not null')->asArray()->all();
?>
<style>
.panel-group {margin-bottom: 0px;overflow: hidden;}  
.panel-group .panel{border-radius:0;border:0;border-bottom:1px solid #ddd}
.panel-body { padding:0px; }
.panel-body table tr td span{ width:100%;display: block;padding-left: 25px;padding-top:10px;padding-bottom:7px;}
.panel-body table tr td {padding:0}
.panel-body .table {margin-bottom: 0px;}
.panel-group .panel+.panel {margin-top: 0px;}
.panel-default>.panel-heading {color: #333;background-color: #fff;}
#accordion {box-shadow: 0px 1px 3px rgba(9, 12, 17, 0.2);border-radius:3px;}
.panel-default>.panel-heading {padding-top:0;padding-bottom:0;}
.panel-collapse{background: #f3f3f3;}
.panel-default > .panel-heading + .panel-collapse > .panel-body {border-top: none; }
.panel-default>.panel-heading h4{padding-top:15px;padding-bottom:10px;}
#accordion .caret{margin-top: -21px;}
.panel-default>.panel-heading a{text-decoration: none;color:#3f3e3e;}
.panel-default>.panel-heading a:hover{text-decoration: none;color:#84bf76;}
.panel-body table tr td a{ text-decoration: none;color:#7b7b7b;}
.panel-body table tr td a:hover{ text-decoration: none;color:#84bf76;}
</style>
<div class="row">
    <div class="col-md-12">
        <h3>Каталог <span class="badge pull-right"><?=$count_products_from_mp;?> товаров</span></h3>  
    </div>
</div>
<div class="row">
    <div class="col-md-12">
      <div class="panel-group" id="accordion">
        <?php 
        $i = 0;
        foreach($left_menu_categorys as $row){
        $i++;
        ?>
        <div class="panel panel-default">
            <div class="panel-heading">
              <a data-toggle="collapse" data-parent="#accordion" href="#coll<?= $i ?>">
                <h4 class="panel-title">
                    <?=$row['name']?>
                </h4>
                <span class="caret pull-right"></span>
              </a>
            </div>
            <div id="coll<?= $i ?>" class="panel-collapse collapse">
                <?php
                foreach($left_menu_categorys_sub as $row2){
                    if($row['id'] == $row2['parent']){
                ?>
                <div class="panel-body">
                    <table class="table">
                        <tr>
                            <td>
                              <a href="?r=site/category&id=<?=$row2['id']?>" title="<?=$row2['name']?>">
                                <span><?=$row2['name']?></span>
                              </a>
                            </td>
                        </tr>
                    </table>
                </div>
                <?php 
                    }
                }
                ?>
            </div>
        </div>
        <?php
        } 
        ?>
      </div>
      
    </div>
</div>