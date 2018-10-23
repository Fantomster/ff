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
                        <!--      <?= Html::a('Доступы', ['access/index'], ['class' => 'btn btn-md fk-button']); ?>
                            <?= Html::a('Контрагенты', ['agent/index'], ['class' => 'btn btn-md fk-button']); ?>
                            <?= Html::a('Склады', ['store/index'], ['class' => 'btn btn-md fk-button']); ?>
                            <?= Html::a('Номенклатура', ['getgoods/index'], ['class' => 'btn btn-md fk-button']); ?>
                            
                            <?= Html::a('Проверка', ['srequest/check'], ['class' => 'btn btn-md fk-button']); ?>
                       -->
                        <?= Html::a('Приходные накладные', ['waybill/index'], ['class' => 'btn btn-md fk-button']); ?>
                        <?= Html::a('Настройки интеграции', ['settings/index'], ['class' => 'btn btn-md fk-button']); ?>
                        <?= Html::a('Журнал', ['journal/index'], ['class' => 'btn btn-md fk-button']); ?>

                        <!--    <?= Html::a('Задачи', ['#'], ['class' => 'btn btn-md fk-button']); ?>
                                    <?= Html::a('История', ['#'], ['class' => 'btn btn-md fk-button']); ?>
                            -->
                        <?= Html::a('Справочники', ['/clientintegr/tillypad/default/main'], ['class' => 'btn btn-md fk-button']); ?>
                        <?php // echo Html::a('Бизнес для сопоставления', ['settings/collations'], ['class' => 'btn btn-md fk-button']); ?>
                    </div>
                    <div class="col-md-4 text-right">
                        <?= Html::a('<i class="fa fa-pencil" aria-hidden="true"></i> Документация', ['#'], ['class' => 'btn btn-default btn-sm m-t']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
