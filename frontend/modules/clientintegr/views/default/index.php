<?php

use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use yii\web\View;
use yii\widgets\ListView;
use kartik\grid\GridView;
use kartik\editable\Editable;
use api\common\models\RkAccess;


?>


<style>
.bg-default{background:#555} p{margin: 0;} #map{width:100%;height:200px;}
</style>
<section class="content-header">
    <h1>
        <i class="fa fa-upload"></i> Интеграция с внешними системами 
        <small>Обменивайтесь номенклатурой и приходными документами с Вашей учетной системой автоматически</small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            'Интеграция',
        ],
    ])
    ?>
</section>
<?php
$user = Yii::$app->user->identity;
$licenses = $user->organization->getLicenseList();
?>
<section class="content">
<div class="catalog-index">
    	<div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Партнеры по интеграции</h3>
            </div>
            <?php if(isset($licenses['rkws'])): ?>
            <div class="box-body">
                <div class="hpanel">
                    <div class="panel-body">
                        <div class="col-md-6 text-left">
                            <?= Html::a('<h4 class="m-b-xs text-info">R-Keeper</h4>', ['rkws/default']) ?>
                            <p class="small">Интеграция с R-keeper STORE HOUSE через White Server (облачная версия)</p>
                        </div>
                        <div class="col-md-6 text-right">
                            <?= Html::a('<i class="fa fa-pencil" aria-hidden="true"></i> Документация', ['#'],['class'=>'btn btn-default btn-sm m-t']) ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php if(isset($licenses['iiko'])): ?>
            <div class="box-body">
                <div class="hpanel">
                    <div class="panel-body">
                        <div class="col-md-6 text-left">
                            <?= Html::a('<h4 class="m-b-xs text-info">iiko Office</h4>', ['iiko/default']) ?>
                            <p class="small">Интеграция с iiko Office</p>
                        </div>
                        <div class="col-md-6 text-right">
                            <?= Html::a('<i class="fa fa-pencil" aria-hidden="true"></i> Документация', ['#'],['class'=>'btn btn-default btn-sm m-t']) ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php if(isset($licenses['email'])): ?>
            <div class="box-body">
                <div class="hpanel" >
                    <div class="panel-body">
                        <div class="col-md-6 text-left">
                            <?= Html::a('<h4 class="m-b-xs text-info">Накладные поставщика</h4>', ['email/default']) ?>
                            <p class="small">Загрузка накладных из 1С с помощью EMAIL</p>
                        </div>
                        <div class="col-md-6 text-right">
                            <?= Html::a('<i class="fa fa-pencil" aria-hidden="true"></i> Документация', ['#'],['class'=>'btn btn-default btn-sm m-t']) ?>
                        </div>
                    </div>
                </div>
             </div>
            <?php endif; ?>
            <?php if(isset($licenses['mercury'])): ?>
             <div class="box-body">
                <div class="hpanel" >
                    <div class="panel-body">
                        <div class="col-md-6 text-left">
                            <?= Html::a('<h4 class="m-b-xs text-info">ВЕТИС "Меркурий"</h4>', ['merc/settings']) ?>
                            <p class="small">Интеграция с системой ВЕТИС "Меркурий"</p>
                        </div>
                        <div class="col-md-6 text-right">
                            <?= Html::a('<i class="fa fa-pencil" aria-hidden="true"></i> Документация', ['#'],['class'=>'btn btn-default btn-sm m-t']) ?>
                        </div>
                    </div>
                </div>
          </div>
          <?php endif; ?>
</div>
</section>

