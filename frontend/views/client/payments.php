<?php

use yii\widgets\Breadcrumbs;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Платежи');
$listTitle = Yii::t('app', 'Список платежей');

$this->params['breadcrumbs'][] = $this->title;

$types = \yii\helpers\ArrayHelper::map(\common\models\PaymentType::find()->asArray()->all(), 'type_id', 'title');
$types[0] = '---';
ksort($types);
?>
<section class="content-header">
    <h1>
        <i class="fa fa-money"></i> <?= $this->title ?>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb'
        ],
        'links' => [
            $this->title
        ],
    ])
    ?>
</section>
<section class="content">
    <div class="row">
        <div class="col-sm-12 col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= $listTitle ?></h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            <?= \kartik\grid\GridView::widget([
                                'pjax' => true,
                                'dataProvider' => $dataProvider,
                                'filterModel' => $searchModel,
                                'columns' => [
                                    'payment_id',
                                    [
                                        'attribute' => 'date',
                                        'filterType' => \kartik\grid\GridView::FILTER_DATE,
                                        'filterWidgetOptions' => ([
                                            'attribute' => 'date',
                                            'pluginOptions' => [
                                                'autoclose' => true,
                                                'format' => 'dd.mm.yyyy',
                                            ]
                                        ]),
                                        'value' => function ($data) {
                                            return date('d.m.Y', strtotime($data->date));
                                        }
                                    ],
                                    'total',
                                    'receipt_number',
                                    [
                                        'attribute' => 'payment.title',
                                        'filter' => Html::dropDownList(
                                            'PaymentSearch[type_payment]',
                                            Yii::$app->request->get('PaymentSearch')['type_payment'],
                                            $types,
                                            [
                                                'style' => 'width:180px',
                                                'class' => 'form-control'
                                            ]
                                        ),
                                    ]
                                ],
                            ]); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

