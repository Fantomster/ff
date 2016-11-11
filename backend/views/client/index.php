<?php

use yii\helpers\Html;
//use yii\grid\GridView;
use kartik\grid\GridView;
use kartik\export\ExportMenu;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Users';
$this->params['breadcrumbs'][] = $this->title;

$gridColumns = [
            'id',
            [
                'attribute' => 'full_name',
                'value' => 'profile.full_name',
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
                'attribute' => 'organization_id',
                'value' => 'organization_id',
                'label' => 'Орг ID'
            ],
            [
                'attribute' => 'org_name',
                'value' => 'organization.name',
                'label' => 'Название организации',
            ],
            [
                'attribute' => 'org_type_id',
                'value' => 'organization.type_id',
                'label' => 'Тип',
            ],
            [
                'attribute' => 'role',
                'value' => 'role.name',
                'label' => 'Роль',
            ],
            'created_at',
            'logged_in_at',
        ];
?>
<div class="user-index">

    <h1><?= Html::encode($this->title) ?></h1>
<?php 
echo ExportMenu::widget([
    'dataProvider' => $dataProvider,
    'columns' => $gridColumns,
    'target' => ExportMenu::TARGET_SELF,
    'exportConfig' => [
        ExportMenu::FORMAT_PDF => false,
        ExportMenu::FORMAT_EXCEL_X => false,
    ],
]);
?>
    <?php Pjax::begin(['enablePushState' => false, 'id' => 'userList', 'timeout' => 3000]); ?>    
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => $gridColumns,
    ]);
    ?>
<?php Pjax::end(); ?></div>
