<table class="table table-hover">
    <tbody>
        <?=
        \yii\widgets\ListView::widget([
            'dataProvider' => $vendorDataProvider,
            'itemView' => '_vendor-view',
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
            'emptyText' => 'Список пуст',
        ])
        ?>
    </tbody>
</table>