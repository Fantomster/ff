<?php

use yii\helpers\Html;

?>


<div class="catalog-index">
    	<div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Панель управления</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">

                <div class="hpanel">
                    <div class="panel-body">
                        <div class="col-md-6 text-left">
                            <?= Html::a('Доступы', ['access/index'], ['class'=>'btn btn-md fk-button']); ?>                            
                            <?= Html::a('Проверка', ['srequest/index'], ['class'=>'btn btn-md fk-button']); ?>    
                            <?= Html::a('Авторизация', ['auth/index'], ['class'=>'btn btn-md fk-button']); ?>   
                            <?= Html::a('Номенклатура', ['getgoods/index'], ['class'=>'btn btn-md fk-button']); ?>    
                            <?= Html::a('Задачи', ['tasks/index'], ['class'=>'btn btn-md fk-button']); ?>    
                            <?= Html::a('История', ['history/index'], ['class'=>'btn btn-md fk-button']); ?>                            
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