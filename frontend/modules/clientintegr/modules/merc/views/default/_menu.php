<?php

use yii\helpers\Html;
use yii\helpers\Url;

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
                        <?php $currentUrl = Yii::$app->request->url ?>
                        <?= ($currentUrl != Url::to(['/clientintegr/merc/default'])) ? Html::a('Список ВСД', ['/clientintegr/merc/default'], ['class' => 'btn btn-md fk-button']) : ''; ?>
                        <?php
                        $user = Yii::$app->user->identity;
                        if (($lic->code == \api\common\models\merc\mercService::EXTENDED_LICENSE_CODE) && ($currentUrl != Url::to(['/clientintegr/merc/stock-entry']))) {
                            echo Html::a(Yii::t('message', 'frontend.views.layouts.client.left.store_entry', ['ru' => 'Журнал продукции']), ['/clientintegr/merc/stock-entry'], ['class' => 'btn btn-md fk-button']);
                        }
                        ?>
                    </div>
                    <div class="col-md-4 text-right">
                        <?= ($currentUrl != Url::to(['/clientintegr/merc/journal'])) ? Html::a('Журнал', ['/clientintegr/merc/journal'], ['class' => 'btn btn-md fk-button']) : ''; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
