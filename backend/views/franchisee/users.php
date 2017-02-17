<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\FranchiseeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Пользователи франшизы ' . $franchisee->signed . "[$franchisee->legal_entity]";
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="franchisee-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create user', ['create-user', 'fr_id' => $franchisee->id], ['class' => 'btn btn-success']) ?>
    </p>
<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
    'id',
    [
        'value' => 'profile.full_name',
        'label' => 'Полное имя',
    ],
    [
        'value' => 'profile.phone',
        'label' => 'Телефон',
    ],
    'status',
    'email',
    [
        'attribute' => 'role',
        'value' => 'role.name',
        'label' => 'Роль',
    ],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
