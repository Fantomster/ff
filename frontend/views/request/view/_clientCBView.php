<?php

use yii\helpers\Html;
use yii\helpers\Url;

?>

<?php

if ($model->request->responsible_supp_org_id == $model->supp_org_id) {
    $n = [
        'value' => 'Убрать исполнителя',
        'class' => 'btn btn-danger',
        'event' => 'exclude'
    ];

    $where = [
        'supp_org_id' => $model->supp_org_id,
        'rest_org_id' => $model->request->rest_org_id,
        'deleted' => false
    ];

    if (!common\models\RelationSuppRest::find()->where($where)->exists()) {
        $n_1 = [
            'value' => 'Добавить поставщика',
            'class' => 'btn btn-success add-supplier',
            'event' => 'add-supplier'
        ];
    } else {
        $n_1 = [
            'value' => 'Поставщик добавлен',
            'class' => 'btn btn-gray disabled',
            'event' => ''
        ];
    }
} else {
    $n = [
        'value' => 'Назначить исполнителем',
        'class' => 'btn btn-success',
        'event' => 'appoint'
    ];
}

?>

<div class="row" style="padding:15px;border-radius:3px;background:#f7f7f7;margin-bottom:15px;" >
    <div class="col-md-12 col-xs-12">
        <div class="media">
            <div class="media-left">
                <img src="<?= $model->organization->pictureUrl ?>" class="media-object" style="width:160px">
            </div>
            <div class="media-body">

                <div class="col-md-4 col-sm-4 col-xs-4">
                    <h4 class="text-success">
                        <?= $model->organization->name ?>
                    </h4>
                </div>

                <div class="col-md-8 col-sm-8 col-xs-8 text-right">
                        <?php
                        echo Html::button($n['value'], [
                            'class' => 'change ' . $n['class'],
                            'style' => 'font-size:16px;margin-right:3px',
                            'data-supp-id' => $model->supp_org_id,
                            'data-req-id' => $model->request_id,
                            'data-event' => $n['event']
                        ]);
                        ?>
                        <?php

                        if (isset($n_1)) {
                            echo Html::button($n_1['value'], [
                                'class' => $n_1['class'],
                                'style' => 'font-size:16px;margin-right:3px',
                                'data-supp-id' => $model->supp_org_id,
                                'data-req-id' => $model->request_id,
                                'data-event' => $n_1['event']
                            ]);
                        }

                        ?>
                </div>

                <div class="col-md-12 col-sm-12 col-xs-12">
                    <!--a href="#" class="btn btn-gray pull-right disabled" style="font-size:16px;margin-top:-10px;margin-right:10px"><i class="fa fa-comment"></i></a-->

                    <h5>Стоимость услуги: <span class="text-bold"><?= $model->price ?> руб.</span></h5>
                    <p><b>Комментарий поставщика:</b> <?= $model->comment ?></p>
                </div>
            </div>
        </div>
    </div>
</div>