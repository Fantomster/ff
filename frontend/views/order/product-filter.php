<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\form\ActiveForm;
use kartik\grid\GridView;
use kartik\widgets\TouchSpin;

$this->title = Yii::t('message', 'frontend.views.order.filter_product', ['ru' => 'Фильтрация товаров']);

yii\jui\JuiAsset::register($this);

$this->registerJs('
    $(document).on("click", ".btnSubmit", function() {
        $($(this).data("target-form")).submit();
    });
', View::POS_READY);
?>
<img id="cart-image" src="/images/cart.png" style="position:absolute;left:-100%;">
<section class="content">
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li><a href="<?= Url::to(['order/create']) ?>"><?= Yii::t('app', 'frontend.views.order.favorites.all', ['ru'=>'Все продукты']) ?></a></li>
            <li>
                <a href="<?= Url::to(['order/guides']) ?>">
                    <?= Yii::t('app', 'frontend.views.order.favorites.templates', ['ru'=>'Шаблоны заказов']) ?> <small class="label bg-yellow">new</small>
                </a>
            </li>
            <li>
                <a href="#">
                    <?= Yii::t('app', 'frontend.views.order.favorites.freq', ['ru'=>'Часто заказываемые товары']) ?> <small class="label bg-yellow">new</small>
                </a>
            </li>
            <li class="active">
                <a href="<?= Url::to(['order/product-filter']) ?>">
                    <?= Yii::t('message', 'frontend.views.order.filter_product', ['ru' => 'Фильтрация товаров']) ?>
                    <small class="label bg-yellow">new</small>
                </a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="row">
                <div class="col-md-12">
                    <div class="guid-header">
                        <?php
                        $form = ActiveForm::begin([
                            'options' => [
                                'id' => 'searchForm',
                                'class' => "navbar-form no-padding no-margin",
                                'role' => 'search',
                            ],
                        ]);
                        ?>
                        <?=
                        $form->field($searchModel, 'searchString', [
                            'addon' => [
                                'append' => [
                                    'content' => '<a class="btn-xs"><i class="fa fa-search"></i></a>',
                                    'options' => [
                                        'class' => 'append',
                                    ],
                                ],
                            ],
                            'options' => [
                                'class' => "margin-right-15 form-group",
                            ],
                        ])
                            ->textInput([
                                'id' => 'searchString',
                                'class' => 'form-control',
                                'placeholder' => Yii::t('message', 'frontend.views.order.search_two', ['ru' => 'Поиск'])
                            ])
                            ->label(false)
                        ?>
                        <?=
                        $form->field($searchModel, 'selectedVendor')
                            ->dropDownList($vendors, ['id' => 'selectedVendor', 'options' => [$selectedVendor => ['selected' => true]]])
                            ->label(false)
                        ?>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <hr>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <?php
                    Pjax::begin(['formSelector' => 'form', 'enablePushState' => false, 'id' => 'createOrder', 'timeout' => 5000]);
                    ?>
                    <div id="products">
                        <?=
                        GridView::widget([
                            'dataProvider' => $dataProvider,
                            'filterModel' => $searchModel,
                            'filterPosition' => false,
                            'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
                            'summary' => '',
                            'tableOptions' => ['class' => 'table table-bordered table-striped dataTable'],
                            'options' => ['class' => 'table-responsive'],
                            'rowOptions' => function ($model) use ($blockedItems) {
                                if (in_array($model['id'], $blockedItems)) {
                                    return ['class' => 'danger'];
                                }
                            },
                            'pager' => [
                                'maxButtonCount' => 5, // Set maximum number of page buttons that can be displayed
                            ],
                            'columns' => [
                                [
                                    'format' => 'raw',
                                    'attribute' => 'product',
                                    'value' => function ($data) {
                                        $note = ""; //empty($data['note']) ? "" : "<div><i>" . $data['note'] . "</i></div>";
                                        $productUrl = Html::a(Html::decode(Html::decode($data['product'])), Url::to(['order/ajax-show-details', 'id' => $data['id'], 'cat_id' => $data['cat_id']]), [
                                            'data' => [
                                                'target' => '#showDetails',
                                                'toggle' => 'modal',
                                                'backdrop' => 'static',
                                            ],
                                            'title' => Yii::t('message', 'frontend.views.order.details', ['ru' => 'Подробности']),
                                        ]);
//                                        $productUrl = "<a title = 'Подробности' data-target='#showDetails' data-toggle='modal' data-backdrop='static' href='".
//                                                Url::to(['order/ajax-show-details', 'id' => $data['id'], 'cat_id' => $data['cat_id']]).
//                                                "'>".$data['product']."</a>";
                                        return "<div class='grid-prod'>" . $productUrl . "</div>$note<div>" . Yii::t('message', 'frontend.views.order.vendor_two', ['ru' => 'Поставщик:']) . "  "
                                            . $data['name'] . "</div><div class='grid-article'>" . Yii::t('message', 'frontend.views.order.art', ['ru' => 'Артикул:']) . "  <span>"
                                            . $data['article'] . "</span></div>";
                                    },
                                    'label' => Yii::t('message', 'frontend.views.order.product_name', ['ru' => 'Название продукта']),
                                ],
                                [
                                    'format' => 'raw',
                                    'attribute' => 'price',
                                    'value' => function ($data) {
                                        $unit = empty($data['ed']) ? '' : " / " . Yii::t('app', $data['ed']);
                                        return '<span data-toggle="tooltip" data-placement="bottom" title="' . Yii::t('message', 'frontend.views.order.price_update', ['ru' => 'Обновлена:']) . ' ' . Yii::$app->formatter->asDatetime($data['updated_at'], "dd-MM-YY") . '"><b>'
                                            . $data['price'] . '</b> ' . $data['symbol'] . $unit . '</span>';
                                    },
                                    'label' => Yii::t('message', 'frontend.views.order.price', ['ru' => 'Цена']),
                                    'contentOptions' => ['class' => 'width150'],
                                    'headerOptions' => ['class' => 'width150']
                                ],
                                [
                                    'attribute' => 'units',
                                    'value' => 'units',
                                    'label' => Yii::t('message', 'frontend.views.order.freq', ['ru' => 'Кратность']),
                                ],
                            ],
                        ])
                        ?>
                    </div>
                    <?php Pjax::end(); ?>
                </div>
            </div>
        </div>
        <!-- /.tab-content -->
    </div>
</section>
