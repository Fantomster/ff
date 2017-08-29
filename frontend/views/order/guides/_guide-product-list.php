<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>
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
            'emptyText' => '<tr><td>Список пуст</td></tr>',
        ])
        ?>
    </tbody>
</table>