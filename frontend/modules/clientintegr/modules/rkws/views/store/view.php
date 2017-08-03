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
use kartik\tree\TreeView;

use api\common\models\RkStoretree;


?>


<style>
.bg-default{background:#555} p{margin: 0;} #map{width:100%;height:200px;}
</style>
<section class="content-header">
    <h1>
        <i class="fa fa-upload"></i> Интеграция с R-keeper SH (White Server) 
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
                'label' => 'Интеграция',
                'url' => ['/vendorintegr'],
            ],
            'Интеграция с R-keeper WS',
        ],
    ])
    ?>
</section>
<section class="content-header">
    <?= $this->render('/default/_menu.php'); ?>
    СКЛАДЫ
  
</section>
<section class="content">
    <div class="catalog-index">

    	<div class="box box-info">            
            <div class="box-header with-border">
                            <div class="panel-body">
                                <div class="box-body table-responsive no-padding">
                                  <?php /*
                                    echo GridView::widget([
                                        'dataProvider' => $dataProvider,
                                        'pjax' => false, // pjax is set to always true for this demo
                                    //    'pjaxSettings' => ['options' => ['id' => 'kv-unique-id-1'], 'loadingCssClass' => false],
                                        'filterPosition' => false,
                                        'columns' => [
                                            'rid',
                                            'denom',
                                            'updated_at',
                                                                                    ],
                                        // 'rowOptions' => function ($data, $key, $index, $grid) {
                                        //  return ['id' => $data['id'], 'onclick' => "console.log($(this).find(a).first())"];
                                        //  },
                                        'options' => ['class' => 'table-responsive'],
                                        'tableOptions' => ['class' => 'table table-bordered table-striped dataTable', 'role' => 'grid'],
                                        'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                                        'bordered' => false,
                                        'striped' => true,
                                        'condensed' => false,
                                        'responsive' => false,
                                        'hover' => true,
                                        'resizableColumns' => false,
                                        'export' => [
                                            'fontAwesome' => true,
                                        ],
                                    ]); */
                                    ?> 

                                </div>
                            </div>    
                </div>
            </div>  
        
            <div class="box box-info">            
                <div class="box-header with-border">
                            <div class="panel-body">
                                    <div class="box-body table-responsive no-padding">
                                    <?=
                                         TreeView::widget([
                                        // single query fetch to render the tree
                                        // use the Product model you have in the previous step
                                        'query' => RkStoretree::find()->addOrderBy('root, lft'), 
                                        'headingOptions' => ['label' => 'Categories'],
                                        'fontAwesome' => false,     // optional
                                        'isAdmin' => false,         // optional (toggle to enable admin mode)
                                        'displayValue' => 1,        // initial display value
                                        'softDelete' => true,       // defaults to true
                                        'cacheSettings' => [        
                                                'enableCache' => true   // defaults to true
                                        ]
                                        ]);
                                     
                                     ?>
                <?= Html::a('Вернуться',
            ['/clientintegr/rkws/default'],
            ['class' => 'btn btn-success btn-export']);
        ?>
                                    </div>
                             </div>    
                 </div>
             </div>    
                                
    </div>            
</section>





