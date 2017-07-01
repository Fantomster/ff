<?php

/*

use yii\helpers\Html;
//use yii\grid\GridView;
use kartik\grid\GridView;
use kartik\export\ExportMenu;
use yii\widgets\Pjax;
 * 
 * 
 */

/* @var $this yii\web\View */
/* @var $searchModel backend\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'API test';
$this->params['breadcrumbs'][] = $this->title;

echo "This is API index view";

/*

$gridColumns = [
    'id',
    [
        'format' => 'raw',
        'attribute' => 'full_name',
//                'value' => 'profile.full_name',
        'value' => function ($data) {
            return Html::a($data['profile']['full_name'], ['client/view', 'id' => $data['id']]);
        },
        'label' => 'Полное имя',
    ],
    [
        'attribute' => 'phone',
        'value' => 'profile.phone',
        'label' => 'Телефон',
    ],
    'status',
    'email',
    [
        'format' => 'raw',
        'attribute' => 'org_name',
        'value' => function ($data) {
            return Html::a($data['organization']['name'], ['organization/view', 'id' => $data['organization_id']]);
        },
        'label' => 'Название организации',
    ],
    [
        'attribute' => 'org_type_id',
        'value' => 'organization.type.name',
        'label' => 'Тип',
        'filter' => common\models\OrganizationType::getList(),
    ],
    [
        'attribute' => 'role',
        'value' => 'role.name',
        'label' => 'Роль',
    ],
//            'created_at',
//            'logged_in_at',
];
        */
?>

