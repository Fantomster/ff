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
                'value' => 'profile.full_name'
            ],
            [
                'attribute' => 'phone',
                'value' => 'profile.phone',
            ],
            'status',
            'email',
            'organization_id',
            [
                'attribute' => 'organization',
                'value' => 'organization.name'
            ],
            [
                'attribute' => 'role',
                'value' => 'role.name'
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
