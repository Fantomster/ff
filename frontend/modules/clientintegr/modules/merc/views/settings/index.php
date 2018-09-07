<?php

use yii\widgets\Breadcrumbs;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\widgets\Pjax;
use yii\web\View;

?>

<?=
Modal::widget([
    'id' => 'settings-edit-form',
    'size' => 'modal-md',
    'clientOptions' => false,
])
?>

<section class="content-header">
    <h1>
        <img src="<?= Yii::$app->request->baseUrl ?>/img/mercuriy_icon.png" style="width: 32px;">
        <?= Yii::t('message', 'frontend.client.integration.mercury', ['ru'=>'Интеграция с системой ВЕТИС "Меркурий"']) ?>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
                'label' => Yii::t('message', 'frontend.views.layouts.client.integration', ['ru'=>'Интеграция']),
                'url' => ['/clientintegr'],
            ],
            Yii::t('message', 'frontend.client.integration.mercury', ['ru'=>'Интеграция с системой ВЕТИС "Меркурий"']),
        ],
    ])
    ?>
</section>
<?=
$this->render('/default/_license_no_active.php', ['lic' => $lic]);
?>
<section class="content-header">
    <?= $this->render('/default/_menu.php', ['lic' => $lic]); ?>
</section>
<section class="content-header">
    <h4><?= Yii::t('message', 'frontend.client.integration.mercury.settings', ['ru'=>'Настройки']) ?>:</h4>
</section>
<section class="content">
    <div class="catalog-index">
        <div class="box box-info">
            <div class="box-header with-border">
                <div class="panel-body">
                    <div class="box-body table-responsive no-padding">
                        <?php Pjax::begin(['formSelector' => 'form', 'enablePushState' => false, 'timeout' => 10000, 'id' => 'st-list']) ?>
                        <?=
                        GridView::widget([
                            'dataProvider' => $dataProvider,
                            'pjax' => true, // pjax is set to always true for this demo
                            'filterPosition' => false,
                            'columns' => [
                                [
                                    'label' => Yii::t('message', 'frontend.client.integration.mercury.attibute', ['ru'=>'Свойство']),
                                    'attribute' => 'denom'
                                ],
                                [
                                    'label' =>  Yii::t('message', 'frontend.client.integration.mercury.comment', ['ru'=>'Комментарий']),
                                    'attribute' => 'comment'
                                ],
                                [
                                    'value' => function ($data) {
                                        $model = \api\common\models\merc\mercDicconst::findOne(['id' => $data->id]);
                                        $res = $model->getPconstValue();

                                        if($model->type == \api\common\models\merc\mercDicconst::TYPE_PASSWORD) {
                                            return str_pad('', strlen($res), '*');
                                        }

                                        // VAT храним в единицах * 100, нужно облагородить перед выводом.
                                        if($model->denom == 'taxVat') {
                                            return $res / 100;
                                        }

                                        if(is_numeric($res)) {
                                            return (($res == 1) ? "Включено" : "Выключено");
                                        }



                                        return $res;
                                    },
                                    'label' => Yii::t('message', 'frontend.client.integration.mercury.current_value', ['ru'=>'Текущее значение']),
                                    'contentOptions' => ['style' => 'font-weight:bold;'],
                                ],
                                [
                                    'class' => 'yii\grid\ActionColumn',
                                    'contentOptions' => ['style' => 'width: 6%;'],
                                    'template' => '{clear}&nbsp;',
                                    'visibleButtons' => [
                                        'clear' => function ($model, $key, $index) {
                                            return true;
                                        },
                                    ],
                                    'buttons' => [
                                        'clear' => function ($url, $model) {
                                            $customurl = Yii::$app->getUrlManager()->createUrl(['clientintegr\merc\settings\change-const', 'id' => $model->id]);
                                            return \yii\helpers\Html::a('<i class="fa fa-wrench" aria-hidden="true"></i>', $customurl,
                                                ['title' => Yii::t('message', 'frontend.client.integration.mercury.edit_value', ['ru'=>'Изменить значение']),
                                                    'data' => [
                                                    'target' => '#settings-edit-form',
                                                    'toggle' => 'modal',
                                                    'backdrop' => 'static',
                                                    ]
                                                ]);
                                        },
                                    ]
                                ],

                            ],
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
                        ]);
                        ?>
                        <?php Pjax::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
$customJs = <<< JS
$(".modal").removeAttr("tabindex");

$("#settings-edit-form").on("click", ".save-form", function() {
    var form = $("#settings-form");
    $.ajax({
    url: form.attr("action"),
    type: "POST",
    data: form.serialize(),
    cache: false,
    success: function(response) {
        $.pjax.reload({container: "#st-list",timeout:30000});
        if(response != true) 
            form.replaceWith(response);
        else
                $("#settings-edit-form").modal('hide');
                  
        },
        failure: function(errMsg) {
        console.log(errMsg);
    }
    });
});

$("body").on("hidden.bs.modal", "#settings-edit-form", function() {
$(this).data("bs.modal", null);
})
JS;
$this->registerJs($customJs, View::POS_READY);
?>



