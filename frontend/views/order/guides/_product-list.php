<?php
//
?>
<table class="table table-hover">
    <tbody>
        <?=
        \yii\widgets\ListView::widget([
            'dataProvider' => $productDataProvider,
            'itemView' => function ($model, $key, $index, $widget) use ($guideProductList) {
                return $this->render('_product-view', compact('model', 'guideProductList'));
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