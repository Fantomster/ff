<?php

use yii\helpers\Html;
use yii\helpers\Url;

?>

<?php

if ($model->request->responsible_supp_org_id == $model->supp_org_id) {
    $n = [
        'value' => Yii::t('message', 'frontend.views.request.kill_executor', ['ru'=>'Убрать исполнителя']),
        'class' => 'btn btn-danger btn-md',
        'event' => 'exclude'
    ];

    $where = [
        'supp_org_id' => $model->supp_org_id,
        'rest_org_id' => $model->request->rest_org_id,
        'deleted' => false
    ];

    if (!common\models\RelationSuppRest::find()->where($where)->exists()) {
        $n_1 = [
            'value' => Yii::t('message', 'frontend.views.request.add_two', ['ru'=>'Добавить поставщика']),
            'class' => 'btn btn-success  btn-md add-supplier',
            'event' => 'add-supplier'
        ];
    } else {
        $n_1 = [
            'value' => Yii::t('message', 'frontend.views.request.vendor_added', ['ru'=>'Поставщик добавлен']),
            'class' => 'btn btn-gray  btn-md disabled',
            'event' => ''
        ];
    }
} else {
    $n = [
        'value' => Yii::t('message', 'frontend.views.request.set_by_exec_two', ['ru'=>'Назначить исполнителем']),
        'class' => 'btn btn-success  btn-md ',
        'event' => 'appoint'
    ];
}

?>

<div class="row" style="padding:5px;border-radius:3px;background:#f7f7f7;margin-bottom:15px;" >
    <div class="media">

        <div class="media-left visible-sm visible-md visible-lg col-sm-12 col-md-2 col-lg-2">
            <img src="<?= $model->organization->pictureUrl ?>" class="media-object" style="max-width: 160px;">
        </div>

        <div class="visible-xs text-center">
            <img src="<?= $model->organization->pictureUrl ?>" style="width: 160px;">
        </div>

        <div class="media-body">
            <div class="media-heading">
                <h4 class="text-success" ><?= $model->organization->name ?></h4>
            </div>
            <p><?= Yii::t('message', 'frontend.views.request.service_price', ['ru'=>'Стоимость услуги:']) ?> <span class="text-bold"><?= $model->price ?> <?= Yii::t('message', 'frontend.views.request.rouble', ['ru'=>'руб.']) ?></span></p>
            <p><b><?= Yii::t('message', 'frontend.views.request.vendors_comment', ['ru'=>'Комментарий поставщика:']) ?></b> <?= $model->comment ?></p>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12" >
            <div class="pull-right text-center" >
                <div class="clearfix visible-xs"><br></div>
                <?=Html::button($n['value'], [
                    'class' => 'change ' . $n['class'],
                    'data-supp-id' => $model->supp_org_id,
                    'data-req-id' => $model->request_id,
                    'data-event' => $n['event']
                ]);?>
                <?php
                if (isset($n_1)) {
                    echo '<div class="clearfix visible-xs"><br></div>';
                    echo Html::button($n_1['value'], [
                        'class' => $n_1['class'],
                        'data-supp-id' => $model->supp_org_id,
                        'data-req-id' => $model->request_id,
                        'data-event' => $n_1['event']
                    ]);
                }
                ?>
            </div>
        </div>
    </div>
</div>