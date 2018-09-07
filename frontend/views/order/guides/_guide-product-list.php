<?php

use kartik\form\ActiveForm;
use common\models\search\BaseProductSearch;
use yii\widgets\ListView;
use yii\data\ActiveDataProvider;

/** @var $form ActiveForm */
/** @var $guideSearchModel BaseProductSearch */
/** @var $guideDataProvider ActiveDataProvider */
/** @var $show_sorting bool */
/** @var $sort string */

$emptyText = Yii::t('message', 'frontend.views.order.guides.empty_list_three', ['ru' => 'Список пуст']);

?>

<div id="alGuideListContainer">
    <?php if ($show_sorting): ?>
        <?=
        $form->field($guideSearchModel, 'sort', [
            'options' => [
                'id' => 'alSortSelect',
                'class' => "form-group",
                'style' => "padding:8px;border-top:1px solid #f4f4f4;"
            ],
        ])
            ->dropDownList([
                '1' => Yii::t('app', 'frontend.views.guides.sort_by', ['ru' => 'Сортировка по']),
                'product 4' => Yii::t('app', 'frontend.views.guides.sort_by_name_asc', ['ru' => 'Наименованию по возрастанию']),
                'product 3' => Yii::t('app', 'frontend.views.guides.sort_by_name_desc', ['ru' => 'Наименованию по убыванию']),
                'id 4' => Yii::t('app', 'frontend.views.guides.sort_by_time_asc', ['ru' => 'Порядку добавления по возрастанию']),
                'id 3' => Yii::t('app', 'frontend.views.guides.sort_by_time_desc', ['ru' => 'Порядку добавления по убыванию']),
            ], [
                'options' => [$sort ?? 1 => ['selected' => true], '1' => ['disabled' => true]]])
            ->label(false)
        ?>
    <?php endif; ?>

    <table class="table table-hover">
        <tbody>
        <?= ListView::widget([
            'dataProvider' => $guideDataProvider,
            'itemView' => function ($model) {
                return $this->render('_guide-product-view', ['model' => $model]);
            },
            'itemOptions' => [
                'tag' => 'tr',
            ],
            'pager' => [
                'maxButtonCount' => 5,
            ],
            'options' => [
                'class' => 'col-lg-12 list-wrapper inline no-padding'
            ],
            'layout' => "{items}<tr><td>{pager}</td></tr>",
            'emptyText' => '<tr><td>' . $emptyText . ' </td></tr>',
        ])
        ?>
        </tbody>
    </table>
</div>