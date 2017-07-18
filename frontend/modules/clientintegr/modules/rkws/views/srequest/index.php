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
                'url' => ['clientintegr/default'],
            ],
            'Интеграция с R-keeper White Server',
        ],
    ])
    ?>
</section>
<section class="content-header">
    <?= $this->render('/default/_menu.php'); ?>

</section>
<section class="content">
    <div class="catalog-index">
            <div class="box-header with-border">
              <div class="box-title pull-left">
                 <?= Html::a('<i class="fa fa-sign-in"></i> Отправить запрос', ['check'],['class'=>'btn btn-md fk-button']) ?>
              </div>
            </div>
    </div>   
    
	<div class="box box-info">            
            <div class="box-header with-border">
                            <div class="panel-body">
                                <div class="box-body table-responsive no-padding">
                            
                            <?php 
                            
                            if ($res['resp']) {
                                
                                echo '<p><table class="table table-condensed">';
                                    foreach ($res['resp'] as $key => $value) {
                                    echo "<tr>";    
                                    echo '<td>'.$key."</td>";    
                                    echo '<td>'.$value."</td>";    
                                    echo "</tr>";
                                }
                                echo '</table></p>';
                            } else {
                                echo 'Данных нет';
                            }
                            
                            ?>        
                                </div>
                            </div>
                                   <div class="panel-body">
                                <div class="box-body table-responsive no-padding">         
                            <?php
                            
                            if ($res['respcode']) {
                              
                                echo '<p><table class="table table-condensed">';
                                    foreach ($res['respcode'] as $key => $value) {
                                    echo "<tr>";    
                                    if ($key === 'Text') { $key = 'Текст';}
                                    if ($key === 'code') { $key = 'Код статуса';}
                                      
                                        echo '<td>'.$key."</td>";   
                                        echo '<td>'.$value;
                                             if ($key === 'Код статуса') { 
                                                echo " (".$errd.")"; }
                                        echo "</td>";
                                    
                                    echo "</tr>";
                                }
                                echo '</table></p>';
                            }
                            ?>
                                </div>
                            </div>
            </div>
        </div>    
                                
</section>

