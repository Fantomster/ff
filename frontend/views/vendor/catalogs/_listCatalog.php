<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\db\Query;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Modal;
use common\models\CatalogBaseGoods;
use common\models\RelationSuppRest;
use common\models\Catalog;
use yii\widgets\Pjax;
use common\models\Organization;
use common\models\User;
use dosamigos\switchinput\SwitchBox;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;
?>
<?php //Pjax::begin(['enablePushState' => false, 'id' => 'catalog-list']); ?>
<?php  
$search_result = trim($search);
$restaurant_result = trim($restaurant);
if(!empty($restaurant_result)){
$arrCatalog = Catalog::find()->select(['id','status','name','created_at','type','id'])->
        where(['supp_org_id'=>$currentUser->organization_id])->
        andFilterWhere(['id'=>common\models\RelationSuppRest::find()->
                select(['cat_id'])->
                where(['supp_org_id'=>$currentUser->organization_id,
                       'rest_org_id'=>$restaurant_result])])->one();
    if($arrCatalog->type==1){
    ?>
    <div class="hpanel">
            <div class="panel-body">
                <h4>Ресторан подключен к <strong>Главному каталогу</strong></h4> 
            </div>
    </div>
    <?php
    exit;
    }else{
    $catalog_id = $arrCatalog->id;  
    $arrCatalog = Catalog::find()->select(['id','status','name','created_at'])->
        where(['supp_org_id'=>$currentUser->organization_id,'id'=>$catalog_id])->all();
    }
}else{
$arrCatalog = Catalog::find()->select(['id','status','name','created_at'])->
        where(['supp_org_id'=>$currentUser->organization_id,'type'=>2])->
        andFilterWhere(['LIKE', 'name', $search_result])->all();
}
if(empty($arrCatalog)){ ?>   
    <h4>Каталоги не найдены</h4>
<?php }
foreach($arrCatalog as $arrCatalogs){
    ?>
    <div class="hpanel" style="margin-bottom:15px;">
        <div class="panel-body">
            <div class="col-md-4 text-left">
            <?= Html::a('<h4 class="text-info"> '.$arrCatalogs->name.
                    '</h4>', ['vendor/step-3-copy', 'id' => $arrCatalogs->id]) ?>
            <p class="small m-b-none">Создан: <?=$arrCatalogs->created_at ?></p>
            </div>
            <div class="col-md-8 text-right">
                    <?php echo $link = SwitchBox::widget([
                    'name' => 'status_'.$arrCatalogs->id,
                    'checked' => $arrCatalogs->status==\common\models\Catalog::STATUS_OFF ? false : true,
                    'clientOptions' => [
                        'onColor' => 'success',
                        'offColor' => 'default',
                        'onText'=>'Вкл',
                        'offText'=>'Выкл',
                        'baseClass'=>'bootstrap-switch',
                        'wrapperClass'=>'wrapper m-t bootstrap-switch-small',
                    ],
                    'class'=>'m-t'
                ]);
                ?>
                <?= Html::a('<i class="fa fa-pencil" aria-hidden="true"></i>', ['vendor/step-3-copy', 'id' => $arrCatalogs->id],['class'=>'btn btn-default m-t btn-sm']) ?>
                <?= Html::a('<i class="fa fa-fw fa-clone"></i>', ['vendor/step-1-clone', 'id' => $arrCatalogs->id],['class'=>'btn btn-default m-t clone-catalog btn-sm ']) ?>
                <?= Html::button('<i class="fa fa-fw fa-trash-o"></i>', ['class' => 'btn btn-danger m-t del btn-sm','name'=>'del_'.$arrCatalogs->id,'id'=>'del_'.$arrCatalogs->id]) ?>
            </div>
        </div>
    </div>
<?php } 
?> 