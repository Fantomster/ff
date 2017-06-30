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
        <small>Загружайте каталоги и цены из Вашей системы учета</small>
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
<section class="content">
<div class="catalog-index">
    	<div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Партнеры по интеграции</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">

                <div class="hpanel">
                    <div class="panel-body">
                        <div class="col-md-6 text-left">
                            <?= Html::a('<h4 class="m-b-xs text-info">1С</h4>', ['odinc/default']) ?>
                            <p class="small">Интеграция с 1С платформой версий 8.3 (8.2) конфигураций "УТ", "КА", "ERP"</p>
                        </div>
                        <div class="col-md-6 text-right">
                            <?= Html::a('<i class="fa fa-pencil" aria-hidden="true"></i> Документация', ['#'],['class'=>'btn btn-default btn-sm m-t']) ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.box-body -->
            <!--div class="box-footer clearfix">
              <span class="pull-right">5 каталогов</span>
            </div-->
            <!-- /.box-footer -->
          </div>

</div>
</section>

