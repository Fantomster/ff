<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>

<div id="alGuideListContainer">
    <?php if ($params['show_sorting']): ?>
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
                'id 4' => Yii::t('app', 'frontend.views.guides.sort_by_time_asc', ['ru' => 'Порядку добавления по возрастанию']),
                'id 3' => Yii::t('app', 'frontend.views.guides.sort_by_time_desc', ['ru' => 'Порядку добавления по убыванию']),
                'product 4' => Yii::t('app', 'frontend.views.guides.sort_by_name_asc', ['ru' => 'Наименованию по возрастанию']),
                'product 3' => Yii::t('app', 'frontend.views.guides.sort_by_name_desc', ['ru' => 'Наименованию по убыванию']),
            ], [
                'options' => [$params['sort'] ?? 1 => ['selected' => true], '1' => ['disabled' => true]]])
            ->label(false)
        ?>
    <?php endif; ?>

    <table class="table table-hover">
        <tbody>
        <?=
        \yii\widgets\ListView::widget([
            'dataProvider' => $guideDataProvider,
            'itemView' => function ($model, $key, $index, $widget) {
                return $this->render('_guide-product-view', compact('model'));
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
            'emptyText' => '<tr><td>' . Yii::t('message', 'frontend.views.order.guides.empty_list', ['ru' => 'Список пуст']) . ' </td></tr>',
        ])
        ?>
        </tbody>
    </table>
</div>