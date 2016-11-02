<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Users';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

    <h1><?= Html::encode($this->title) ?></h1>
<?php // echo $this->render('_search', ['model' => $searchModel]);  ?>
    <?php Pjax::begin(); ?>    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            [
                'attribute' => 'profile',
                'value' => 'profile.full_name'
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
            // 'password',
            // 'auth_key',
            // 'access_token',
            // 'logged_in_ip',
            // 'logged_in_at',
            // 'created_ip',
            // 'created_at',
            // 'updated_at',
            // 'banned_at',
            // 'banned_reason',
            // 'organization_id',
        ],
    ]);
    ?>
<?php Pjax::end(); ?></div>
