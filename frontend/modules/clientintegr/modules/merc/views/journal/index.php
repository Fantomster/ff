<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\JournalSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Журнал ВЕТИС Меркурий';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php

use yii\widgets\Breadcrumbs;

?>
<section class="content-header">
    <h1>
        <i class="fa fa-upload"></i> Журнал ВЕТИС Меркурий
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
                'label' => 'Интеграции',
                'url' => ['/clientintegr/default'],
            ],
            $this->title,
        ],
    ])
    ?>
</section>
<section class="content">
    <?= $this->render('/default/_menu.php', ['lic' => $lic]); ?>
    <h4><?=$this->title?>:</h4>
    <div class="box box-info">
        <div class="box-header with-border">
            <div class="panel-body">
                <div class="box-body table-responsive no-padding grid-category">
                    <?php yii\widgets\Pjax::begin(['id' => 'table_journal', 'timeout' => 10000]) ?>
                    <?php echo $this->render('_search', ['model' => $searchModel, 'user' => $user]); ?>
                    <?= \kartik\grid\GridView::widget([
                        'dataProvider' => $dataProvider,
                        'rowOptions' => function ($model) {
                            if ($model->type == 'error') {
                                return ['class' => 'danger'];
                            }
                        },
                        'columns' => [
                            'id',
                            'operation.denom',
                            'operation.comment',
                            'user.profile.full_name',
                            'organization.name',
                            'type',
                            [
                                'header' => 'Дата операции',
                                'value' => function ($data) {
                                    return Yii::$app->formatter->asDatetime($data['created_at'], "php:j M Y  H:i:s");
                                }
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => '{view}'
                            ],
                        ],
                    ]); ?>
                    <?php yii\widgets\Pjax::end(); ?>
                </div>
            </div>
        </div>
    </div>
</section>