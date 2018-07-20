<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii2assets\pdfjs\PdfJs;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;

$this->title = 'Редактирование заказа';
$this->params['breadcrumbs'] = [
    ['label' => 'Заказы с прикрепленными файлами', 'url' => ['with-attachments']],
    $this->title
];

$this->registerCss('
        .container{width:100% !important;}
        .wrap > .container {padding: 65px 15px 20px;}
        .view-data {height: 30px;}
        td{vertical-align:middle !important;}
        ');

$attachment_id = isset($currentAttachment) ? $currentAttachment->id : null;
$pjaxUrl = Url::to(['order/edit', 'id' => $order->id, 'attachment_id' => $attachment_id]);

$js = <<<JS
        
        var url = window.location.href;
        
        $(document).on('click', '.btnSave', function(e) {
            e.preventDefault();
            $("#cancelChanges").hide();
            var form = $("#editOrder");
            $(".btnSave").button("loading");
            form.submit();
            saving = true;
        });
        $(document).on('click', '#cancelChanges', function (e) {
            $.pjax.reload({container: "#attachment", url: url, timeout:30000});
        });
        $(document).on('click', '.deletePosition', function(e) {
            e.preventDefault();
            target = $(this).data("target");
            $(target).val(0);
            $(target).closest('tr').hide();
            $("#cancelChanges").show();
        });

        $(document).on('pjax:complete', function() {
            $("#cancelChanges").hide();
        })
        $(document).on("change paste keyup", ".quantityAdd", function() {
            var btnAddToCart = $(this).parent().parent().parent().find(".add-to-cart");
            if ($(this).val() > 0) {
                btnAddToCart.removeClass("disabled");
            } else {
                btnAddToCart.addClass("disabled");
            }
        });
        $(document).on("hidden.bs.modal", "#showProducts", function() {
            $(this).data("bs.modal", null);
            $(".modal-header").html("<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span>");
            $(".modal-body").html("");
            $.pjax.reload({container: "#attachment", url: url, timeout:30000});
        });
        $(document).on("change", ".quantity, .price, #order-status", function(e) {
            value = $(this).val();
            $(this).val(value.replace(",", "."));
            $("#cancelChanges").show();
        });
JS;
$this->registerJs($js, \yii\web\View::POS_LOAD);
?>

<div class="row">
    <?php Pjax::begin(['enablePushState' => true, 'timeout' => 10000, 'id' => 'attachment',]); ?>
    <div class="col-md-6" style="height: 760px; overflow-y: auto;">
        <div class="row">
            <div class="col-md-12">
                <?php
                foreach ($order->attachments as $attachment) {
                    echo Html::a($attachment->file, Url::to(['order/edit', 'id' => $order->id, 'attachment_id' => $attachment->id]), ['style' => 'border: 1px solid;padding:5px;margin:2px;float:left;']) . "&nbsp;";
                }
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12" style="margin-top: 20px;">
                <?php
                if (isset($currentAttachment)) {
                    if (substr($currentAttachment->file, -4) === ".pdf") {
                        ?>
                        <?= PdfJs::widget(['url' => Url::to(['order/get-attachment', 'id' => $currentAttachment->id]), 'height' => '710px',]) ?>
                    <?php } else { ?>
                        <img style="max-width: 100%;" src="<?= Url::to(['order/get-attachment', 'id' => $currentAttachment->id]) ?>" />
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-12">
                <?php
                    echo Html::a('<span><i class="icon fa fa-plus"></i> ' . Yii::t('message', 'frontend.views.order.add_to_order', ['ru' => 'Добавить в заказ']) . ' </span>', Url::to(['order/ajax-show-products', 'order_id' => $order->id]), [
                                'class' => 'btn btn-success pull-left btnAdd',
                                'data' => [
                                    'target' => '#showProducts',
                                    'toggle' => 'modal',
                                    'backdrop' => 'static',
                                ],
                                'title' => Yii::t('message', 'frontend.views.order.add_to_order', ['ru' => 'Добавить в заказ']),
                            ]);
                    echo Html::button('<span><i class="icon fa fa-save"></i> ' . Yii::t('message', 'frontend.views.order.save_six', ['ru' => 'Сохранить']) . ' </span>', [
                        'class' => 'btn btn-success pull-right btnSave',
                        'data-loading-text' => "<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span> " . Yii::t('message', 'frontend.views.order.saving_three', ['ru' => 'Сохраняем...']),
                    ]);
                    echo Html::button('<i class="icon fa fa-ban"></i> ' . Yii::t('message', 'frontend.views.client.settings.cancel', ['ru' => 'Отменить изменения']), [
                        'class' => 'btn btn-gray pull-right', 
                        'id' => 'cancelChanges', 
                        'style' => 'margin-right: 7px;display:none;',
                        ]);
                    ?>
            </div>
        </div>
        <div class="row" style="margin-top: 20px; height: 700px; overflow-y: auto;">
            <div class="col-md-12">
                <?php
                echo $this->render('_edit-grid', compact('dataProvider', 'searchModel', 'order'));
                ?>
            </div>
        </div>
    </div>
<?php Pjax::end(); ?>
</div>
<?=
Modal::widget([
    'id' => 'showProducts',
    'clientOptions' => false,
    'size' => Modal::SIZE_LARGE,
    'header' => '<span class=\'glyphicon-left glyphicon glyphicon-refresh spinning\'></span>',
])
?>