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


$script = <<< JS
$("document").ready(function() {
    setInterval(function() {     
       $.pjax.reload({container:"#dics_pjax",timeout: 16000});
    }, 10000); 
});
JS;
$this->registerJs($script);
?>


<style>
.bg-default{background:#555} p{margin: 0;} #map{width:100%;height:200px;}
</style>
<section class="content-header">
    <h1>
        <i class="fa fa-upload"></i> Интеграция с R-keeper STORE HOUSE White Server 
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
                'label' => 'Интеграция',
                'url' => ['/clientintegr/default'],
            ],
            'Интеграция с R-keeper White Server',
        ],
    ])
    ?>
</section>
<section class="content-header">
    <div class="catalog-index">
    	<div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Панель управления</h3>
            </div>
        
            <!-- /.box-header -->
            <div class="box-body">

                <div class="hpanel">
                    <div class="panel-body">
                        <div class="col-md-8 text-left">
                        <span style="color:red;">Лицензия не активна.</span><br> Обратитесь к менеджерам по сопровождению для получения дополнительной информации!
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

