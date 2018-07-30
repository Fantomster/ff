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
                        <div class="col-md-8 text-left">
                            <?= Html::a('Журнал', ['journal/index'], ['class' => 'btn btn-md fk-button']); ?>
                        </div>
                    </div>
                </div>
            </div>
          </div>

</div>
