<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\Organization;

/* @var $this yii\web\View */
/* @var $model common\models\Organization */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Организации', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

switch ($model->type_id) {
    case Organization::TYPE_RESTAURANT:
        $orderListUrl = ['order/index', 'OrderSearch[client_id]' => $model->id];
        $goodsListUrl = null;
        break;
    case Organization::TYPE_SUPPLIER:
        $orderListUrl = ['order/index', 'OrderSearch[vendor_id]' => $model->id];
        $goodsListUrl = ['goods/vendor', 'id' => $model->id];
        break;
}

$buisinessInfo = \common\models\BuisinessInfo::findOne(['organization_id' => $model->id]);
?>
<div class="organization-view">

    <h1><?= Html::encode($this->title) ?></h1>
    <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary', 'style' => 'margin-bottom: 10px;']) ?>

    <?=
    $buisinessInfo ?
            Html::a('Просмотреть реквизиты', ['buisiness-info/view', 'id' => $buisinessInfo->id], ['class' => 'btn btn-success', 'style' => 'margin-bottom: 10px;']) :
            Html::a('Заполнить реквизиты', ['buisiness-info/approve', 'id' => $model->id], ['class' => 'btn btn-default', 'style' => 'margin-bottom: 10px;'])
    ?>

    <?= ($model->type_id == Organization::TYPE_SUPPLIER) ? Html::a('Настройка уведомлений', ['notifications', 'id' => $model->id], ['class' => 'btn btn-danger', 'style' => 'margin-bottom: 10px;']) : '' ?>

    <?= Html::a('Настройки EDI', ['edi-settings', 'id' => $model->id], ['class' => 'btn btn-default', 'style' => 'margin-bottom: 10px;']) ?>

    <?= Html::a('Настройки интеграции', ['integration-settings', 'id' => $model->id], ['class' => 'btn btn-default', 'style' => 'margin-bottom: 10px;']) ?>

    <?=
    DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'label' => 'Тип организации',
                'value' => $model->type->name,
            ],
            'name',
            'white_list',
            'partnership',
            'legal_entity',
            'city',
            'address',
            'zip_code',
            'phone',
            'email:email',
            'website',
            'contact_name',
            'about',
            [
                'format' => 'raw',
                'label' => '<i>Юридическое название франшизы</i>',
                'value' => isset($model->franchisee) ? Html::a($model->franchisee->legal_entity, ['franchisee/view', 'id' => $model->franchisee->id]) : '-',
            ],
            [
                'attribute' => 'franchisee.signed',
                'label' => '<i>Подписант франшизы</i>',
            ],
            [
                'attribute' => 'franchisee.legal_email',
                'label' => '<i>Официальный email франшизы</i>',
            ],
            [
                'attribute' => 'franchisee.phone',
                'label' => '<i>Официальный телефон франшизы</i>',
            ],
            [
                'attribute' => 'created_at',
                'label' => 'Дата создания',
                'value' => function ($data) {
                    return Yii::$app->formatter->asTime($data->created_at, "php:j M Y, H:i:s");
                }
            ],
            [
                'attribute' => 'updated_at',
                'label' => 'Последнее изменение',
                'value' => function ($data) {
                    return Yii::$app->formatter->asTime($data->updated_at, "php:j M Y, H:i:s");
                }
            ],
            [
                'attribute' => 'is_work',
                'label' => 'Поставщик работает в системе',
                'value' => function ($data) use ($model) {
                    if ($data->is_work == 1) {
                        return 'Да';
                    } else {
                        return 'Нет';
                    }
                },
                'visible' => ($model->type_id == Organization::TYPE_SUPPLIER)
            ],
            [
                'attribute' => 'blacklisted',
                'label' => 'Статус',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data->getStatus();
                }
            ],
            'gln_code',
            [
                'format' => 'raw',
                'label' => 'Работники',
                'value' => Html::a('Список', ['client/employees', 'id' => $model->id])
            ],
            [
                'format' => 'raw',
                'label' => 'Заказы',
                'value' => Html::a('Список', $orderListUrl),
            ],
            [
                'format' => 'raw',
                'label' => 'Товары',
                'value' => $goodsListUrl ? Html::a('Список', $goodsListUrl) : '',
            ],
        ],
    ])
    ?>

</div>
