<?php
use yii\helpers\Html;
use common\models\Organization;
use common\models\CatalogBaseGoods;
$locationWhere = [];
        if(Yii::$app->session->get('locality')){
            $locationWhere = ['country'=>Yii::$app->session->get('country'),'locality'=>Yii::$app->session->get('locality')];
        }
$count_products_from_mp = CatalogBaseGoods::find()
                ->joinWith('vendor')
                ->where([
                    'organization.white_list'=>  Organization::WHITE_LIST_ON,
                    'market_place'=>CatalogBaseGoods::MARKETPLACE_ON,
                    'status' => CatalogBaseGoods::STATUS_ON,
                    'deleted'=>CatalogBaseGoods::DELETED_OFF])
                ->andWhere($locationWhere)
                ->andWhere('category_id is not null')
                ->count();
$left_menu_categorys     = \common\models\MpCategory::getDb()->cache(function ($db) {
    return \common\models\MpCategory::find()->select('id,name,parent,slug')->where(['parent'=>NULL])->asArray()->all();
});
//$left_menu_categorys_sub = \common\models\MpCategory::getDb()->cache(function ($db) {
//    return \common\models\MpCategory::find()->where('parent is not null')->all();
//});
$left_menu_categorys_sub = \common\models\MpCategory::find()->where('parent is not null')->all();
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
        <h3><?= Yii::t('message', 'market.views.layouts.left.catalog', ['ru'=>'Каталог']) ?> <span class="badge pull-right"><?=$count_products_from_mp;?> <?= Yii::t('message', 'market.views.layouts.left.goods', ['ru'=>'товаров']) ?></span></h3>
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
              <a data-toggle="collapse" data-parent="#accordion" href="#coll<?= $i ?>" title="<?=Yii::t('app', $row["name"]) ?>">
                  <h4 class="panel-title">
                      <span class="parent-category" data-url="<?= \yii\helpers\Url::to(['site/category', 'slug' => $row["slug"] ]) ?>"><?=Yii::t('app', $row['name'])?></span>
                </h4>
                <span class="caret pull-right"></span>
              </a>
            </div>
            <div id="coll<?= $i ?>" class="panel-collapse collapse">
                <?php
                foreach($left_menu_categorys_sub as $row2){
                    if($row['id'] == $row2->parent){
                ?>
                <div class="panel-body">
                    <table class="table">
                        <tr>
                            <td>
                                <a href="<?= \yii\helpers\Url::to(['site/category', 'slug' => $row2->slug ]) ?>" title="<?=Yii::t('app', $row2->name) ?>">
                                <span><?=Yii::t('app', $row2->name) ?></span>
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
