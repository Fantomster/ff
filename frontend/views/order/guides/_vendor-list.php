<table class="table table-hover">
    <tbody>
        <?=
        \yii\widgets\ListView::widget([
            'dataProvider' => $vendorDataProvider,
            'itemView' => function ($model, $key, $index, $widget) use ($selectedVendor) {
                return $this->render('_vendor-view', compact('model', 'selectedVendor'));
            },
            'itemOptions' => [
                'tag' => 'tr',
            ],
            'pager' => [
                'maxButtonCount' => 5,
//        'options' => [
//            'class' => 'pagination col-md-12  no-padding'
//        ],
            ],
            'options' => [
                'class' => 'col-lg-12 list-wrapper inline no-padding'
            ],
            'layout' => "{items}<tr><td>{pager}</td></tr>",
            'emptyText' => '<tr><td>' . Yii::t('message', 'frontend.views.order.guides.empty_list_three', ['ru'=>'Список пуст']) . ' </td></tr>',
        ])
        ?>
    </tbody>
</table>