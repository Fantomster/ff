<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\depdrop\DepDrop;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;
use kartik\checkbox\CheckboxX;

kartik\checkbox\KrajeeFlatBlueThemeAsset::register($this);
?>
<style>
    .form-control:focus {
        border-color: #84bf76;
        outline: 0;
        box-shadow: inset 0px 3px 5px 0px rgba(91, 137, 81, 0.3);
    }
    .bl-img{
        width: 240px;
        height:135px;
        border:1px dashed #65a157;
        display: inline-block;
    }
    .input-group{width: 100%;}
    input,textarea{
        font-size: 12px !important;	
    }


    .input-group .input-group-addon {
        border-top-left-radius: 0px !important;
        border-bottom-left-radius: 0px !important;
        border-top-right-radius: 3px !important;
        border-bottom-right-radius: 3px !important;
    }
    .input-group-addon{background: #fff}
    .h3-text{
        font-weight: 300;
        font-size: 24px;	
        letter-spacing: 0.02;
        line-height: 1;
    }
    .sub-h3-text{
        font-weight: 400;
        font-size: 1em;
        color: rgba(63,62,62,0.6) !important;
        line-height: 0.9;
        margin-bottom: 24px
    }
    .dp-text{
        font-weight: 400;
        font-size: 1em;
        color: rgba(63,62,62,0.6) !important;
        line-height: 0.9;	
    }
    a{
        color: #84bf76;	
    }
    a:focus{
        color: #006c1e	
    }
    a:hover{
        color: #006c1e		
    } 
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 28px; 
    }
    .select2-container--default .select2-selection--single {
        background-color: #fff;
        border-color: #d2d6de;
        border-radius: 3px;
    }
    .select2-container .select2-selection--single {
        height: 34px;}
    .select2-container--default .select2-selection--single .select2-selection__arrow b {
        margin-top: 2px;
    }
    .select2-container .select2-selection--single .select2-selection__rendered {
        padding-left: 0px;
        padding-right: 10px;
    }
    label {
        margin-top:0px
    }
    .uploadButton {
        position: absolute;
        display: block;
        width: 240px;
        height: 135px;
        border-radius: 0%;
        top: 0;
        margin: 0 auto;
        left: 0;
        right: 0;
        opacity:0;
        cursor:pointer;
        padding-top: 88px;
        font-style: italic;
        font-weight: bold;
        transition: all .6s;
    }
    .uploadButton:hover {
        background:#000;
        color:#fff;
        opacity:0.5;
    }
    .upload-demo .upload-demo-wrap,
    .upload-demo .upload-result,
    .upload-demo.ready .upload-msg {
        display: none;
    }
    .upload-demo.ready .upload-demo-wrap {
        display: block;
    }
    .upload-demo.ready .upload-result {
        display: inline-block;    
    }
    .upload-demo-wrap {
        position:absolute;
        width: 100%;
        height: 150px;
        border-radius: 0%;
        top: 0;
        margin: 0 auto;
        left: 0;
        right: 0;
        opacity:0;
    }
    .cr-boundary{border-radius:0%}
    .upload-msg {
        text-align: center;
        padding: 50px;
        font-size: 22px;
        color: #aaa;
        width: 260px;
        margin: 50px auto;
        border: 1px solid #aaa;
    }
    .croppie-container .cr-slider-wrap {
        margin: 20px auto;
    }
    #upload-avatar{border-radius:0%}
    .cr-viewport{border-radius:0%}
</style>
<?php
$form = ActiveForm::begin([
            'id' => 'marketplace-product-form',
            'enableClientValidation' => true,
            'action' => $catalogBaseGoods->isNewRecord ?
            Url::toRoute('vendor/ajax-create-product-market-place') :
            Url::toRoute(['vendor/ajax-update-product-market-place', 'id' => $catalogBaseGoods->id]),
        ]);
?>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title"><?= $catalogBaseGoods->isNewRecord ? Yii::t('message', 'frontend.views.vendor.add_good', ['ru' => 'ДОБАВЛЕНИЕ ТОВАРА']) : Yii::t('message', 'frontend.views.vendor.edit_good', ['ru' => 'РЕДАКТИРОВАНИЕ ТОВАРА']) ?></h4>
</div>
<div class="modal-body" style="background:#fff !important">
    <div class="row">
        <div class="col-md-12 text-center">
            <h5 class="sub-h3-text"><?= Yii::t('message', 'frontend.views.vendor.market', ['ru' => 'Вы можете разместить Ваш товар в Маркете']) ?>
                <br><small><?= Yii::t('message', 'frontend.views.vendor.good_market', ['ru' => 'Товар будет доступен на площадке MixMarket в течении 2 минут']) ?></small><h5>
                    </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-center upload-block">
                            <?php
                            $this->registerJs("
                        var uploadCrop = $('#upload-avatar').croppie({
                                viewport: {
                                        width: 240,
                                        height: 135,
                                        //type: 'circle'
                                        type: 'square'
                                },
                                update: function(){
                                    uploadCrop.croppie('result', {type:'canvas'}).then(function (resp) {
                                        $('#image-crop-result').val(resp);
                                    });
                                },
                                enableExif: true
                        });
                    ");
                            ?>
                            <div class="upload-demo-wrap">
                                <div id="upload-avatar"></div>
                            </div>
                            <img id="newAvatar" width="240" height="135" style="background-color:#ccc"
                                 src="<?=
                                 (!empty($catalogBaseGoods['image']) && !$catalogBaseGoods->isNewRecord) ?
                                         $catalogBaseGoods->imageUrl :
                                         common\models\CatalogBaseGoods::DEFAULT_IMAGE
                                 ?>" class="avatar"/>
                                 <?=
                                 Html::a('<i class="fa fa-trash"></i>', '#', [
                                     'class' => 'btn btn-outline-danger btn-sm hide',
                                     'style' => 'position:absolute;top:10px;left:0;right:0;margin:0 auto;width:37px;z-index:33',
                                     'id' => 'deleteAvatar',
                                 ]);
                                 ?>
                            <label for="upload" class="uploadButton"><?= Yii::t('message', 'frontend.views.vendor.file_down', ['ru' => 'Загрузить файл']) ?></label>
                            <!--input style="opacity: 0; z-index: -1;" type="file" accept="image/*" id="upload"-->
                            <?=
                                    $form->field($catalogBaseGoods, 'image', ['template' => '<div class="input-group">{input}</div>{error}'])
                                    ->fileInput(['placeholder' => Yii::t('message', 'frontend.views.vendor.good_name', ['ru' => 'НАИМЕНОВАНИЕ ТОВАРА']), 'id' => 'upload', 'accept' => 'image/*', 'style' => 'opacity: 0; z-index: -1;position: absolute;left: -9999px;'])
                            ?>
                            <?= Html::hiddenInput('CatalogBaseGoods[image]', null, ['id' => 'image-crop-result']) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-12" id="b-category" style="border: 1px dashed #77c497; padding: 15px;margin-top: 20px;margin-bottom: 10px">
                                <label class="control-label" for="dynamicmodel-sub1"><?= Yii::t('message', 'frontend.views.vendor.good_category', ['ru' => 'Категория товара']) ?></label>
                                <?php
                                $mp_category = \common\models\MpCategory::find()->where('parent IS NULL')->asArray()->all();
                                foreach ($mp_category as &$item) {
                                    $item['name'] = Yii::t('app', $item['name']);
                                }
                                echo $form->field($catalogBaseGoods, 'sub1')->widget(Select2::classname(), [
                                    //'model'=>$categorys->sub1,
                                    'data' => ArrayHelper::map($mp_category, 'id', 'name'),
                                    'options' => ['placeholder' => Yii::t('message', 'frontend.views.vendor.choose', ['ru' => 'Выберите...'])],
                                    'theme' => "default",
                                    //'hideSearch' => true,
                                    'pluginOptions' => [
                                        'allowClear' => true
                                    ],
                                ])->label(false);
                                echo Html::hiddenInput('catalogBaseGoods_id1', $catalogBaseGoods->category_id, ['id' => 'catalogBaseGoods_id1']);
                                echo Html::hiddenInput('input-type-2', $catalogBaseGoods->sub2, ['id' => 'input-type-2']);
                                echo Html::hiddenInput('CatalogBaseGoods[id]', $catalogBaseGoods->id);
                                ?>
                                <?php
                                echo $form->field($catalogBaseGoods, 'sub2')->widget(DepDrop::classname(), [
                                    'options' => [],
                                    'type' => DepDrop::TYPE_SELECT2,
                                    'select2Options' => [
                                        // 'model'=>$catalogBaseGoods->sub2,
                                        'theme' => "default",
                                        //'hideSearch' => true,
                                        'pluginOptions' => [
                                            'allowClear' => true
                                        ],
                                    ],
                                    'pluginOptions' => [
                                        'depends' => ['catalogbasegoods-sub1'],
                                        'placeholder' => false,
                                        'url' => Url::to(['vendor/get-sub-cat']),
                                        'loadingText' => Yii::t('message', 'frontend.views.vendor.download', ['ru' => 'Загрузка...']),
                                        'initialize' => true,
                                        //'initDepends'=>['dynamicmodel-sub2'],
                                        'params' => ['catalogBaseGoods_id1', 'input-type-2'],
                                    ]
                                ])->label(false);
                                ?>
                                <?= $catalogBaseGoods->isNewRecord ? $form->field($catalogBaseGoods, 'cat_id')->hiddenInput(['value' => Yii::$app->request->get('id')])->label(false) : '' ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-12" style="padding: 0px;">
                                <div class="col-md-6">
                                    <?=
                                            $form->field($catalogBaseGoods, 'product', ['template' => ' {label}<div class="input-group">{input}</div>{error}'])->
                                            textInput(['value' => Html::decode($catalogBaseGoods->product), 'placeholder' => Yii::t('message', 'frontend.views.vendor.good_name', ['ru' => 'НАИМЕНОВАНИЕ ТОВАРА']), 'style' => 'border-radius:3px'])
                                    ?>
                                    <?=
                                            $form->field($catalogBaseGoods, 'article', ['template' => ' {label}<div class="input-group">{input}<span class="input-group-addon">
    <span class="glyphicon glyphicon-question-sign" 
    data-toggle="tooltip" 
    title="' . Yii::t('message', 'frontend.views.vendor.art', ['ru' => 'Артикул товара - это сочетание букв, цифр, символов, которое обозначает данную модель товара']) . ' ">
    </span></span></div>{error}'])->
                                            textInput(['value' => Html::decode($catalogBaseGoods->article), 'placeholder' => Yii::t('message', 'frontend.views.vendor.art_two', ['ru' => 'АРТИКУЛ'])])
                                    ?>
                                    <?=
                                            $form->field($catalogBaseGoods, 'price', ['template' => ' {label}<div class="input-group">{input}<span class="input-group-addon">
    <span class="glyphicon glyphicon-question-sign" 
    data-toggle="tooltip" 
    title="' . Yii::t('message', 'frontend.views.vendor.price_for_one', ['ru' => 'Цена за определенную количественную единицу (или за определенное число единиц) товара, указанную в обычно применяемых в торговле данным товаром единицах измерения (веса, длины, площади, объема, штук, комплектов и т. д.)']) . ' ">
    </span></span></div>{error}'])->
                                            textInput(['placeholder' => Yii::t('message', 'frontend.views.vendor.price_two', ['ru' => 'ЦЕНА ЗА ЕД ИЗМЕРЕНИЯ'])])
                                    ?>
                                    <label class="control-label" for=""><?= Yii::t('message', 'frontend.views.vendor.measure', ['ru' => 'Ед измерения']) ?></label>
                                    <?php
                                    $mp_ed = \common\models\MpEd::find()->asArray()->all();
                                    foreach ($mp_ed as &$item) {
                                        $item['name'] = Yii::t('app', $item['name']);
                                    }
                                    echo $form->field($catalogBaseGoods, 'ed')->widget(Select2::classname(), [
                                        'model' => $catalogBaseGoods->ed,
                                        'data' => ArrayHelper::map($mp_ed, 'name', 'name'),
                                        'options' => ['placeholder' => Yii::t('message', 'frontend.views.vendor.choose_two', ['ru' => 'Выберите...'])],
                                        'theme' => Select2::THEME_DEFAULT,
                                        'pluginOptions' => [
                                            'allowClear' => true,
                                        ],
                                    ])->label(false);
                                    ?>
                                    <?=
                                            $form->field($catalogBaseGoods, 'units', ['template' => ' {label}<div class="input-group">{input}<span class="input-group-addon">
    <span class="glyphicon glyphicon-question-sign"
    data-toggle="tooltip" 
    title="' . Yii::t('message', 'frontend.views.vendor.min_unit', ['ru' => 'Минимальная партия товара (если кратоность 10, а единица измерения Пакет - это значит, что минимальная партия поставки = 10 пакетов)']) . ' "></span></span></div>{error}'])->
                                            textInput(['placeholder' => Yii::t('message', 'frontend.views.vendor.quan', ['ru' => 'КРАТНОСТЬ ПОСТАВКИ'])])
                                    ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="control-label" for=""><?= Yii::t('message', 'frontend.views.vendor.country', ['ru' => 'Страна производитель']) ?></label>
                                    <?php
                                    foreach ($countrys as &$item) {
                                        $item['name'] = Yii::t('app', $item['name']);
                                    }
                                    echo $form->field($catalogBaseGoods, 'region')->widget(Select2::classname(), [
                                        'model' => $catalogBaseGoods->region,
                                        'data' => ArrayHelper::map($countrys, 'id', 'name'),
                                        'options' => ['placeholder' => Yii::t('message', 'frontend.views.vendor.choose_three', ['ru' => 'Выберите...'])],
                                        'theme' => "default",
                                        'pluginOptions' => [
                                            'allowClear' => true,
                                        //'tags' => true,
                                        ],
                                    ])->label(false);
                                    ?>

                                    <?=
                                            $form->field($catalogBaseGoods, 'brand', ['template' => ' {label}<div class="input-group">{input}</div>{error}'])->
                                            textInput(['value' => Html::decode($catalogBaseGoods->brand), 'placeholder' => Yii::t('message', 'frontend.views.vendor.vendor_name_two', ['ru' => 'НАЗВАНИЕ ПРОИЗВОДИТЕЛЯ']), 'style' => 'border-radius:3px'])
                                    ?>
                                    <?=
                                            $form->field($catalogBaseGoods, 'weight', ['template' => ' {label}<div class="input-group">{input}</div>{error}'])->
                                            textInput(['placeholder' => Yii::t('message', 'frontend.views.vendor.wrappers_weight', ['ru' => 'ВЕС УПАКОВКИ']), 'style' => 'border-radius:3px'])
                                    ?>
                                    <?= $form->field($catalogBaseGoods, 'note')->textArea(['value' => Html::decode($catalogBaseGoods->note), 'style' => 'height: 100%;min-height: 104px;']) ?>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12" style="padding: 0 28px;">
                            <h5><?= Yii::t('message', 'frontend.views.vendor.contact_us', ['ru' => 'Для того, чтобы разместиться на площадке F_MARKET, свяжитесь с нами']) ?></h5>
                        </div>
                        <div class="col-md-12" style="padding: 15px 28px 4px 28px;">
                            <div class="pull-left" style="border: 2px dotted #84bf76;padding: 10px 10px 0px 10px;margin-top: 0;border-radius:8px;">
                                <?=
                                $form->field($catalogBaseGoods, 'market_place')->widget(CheckboxX::classname(), [
                                    //'initInputType' => CheckboxX::INPUT_CHECKBOX,
                                    'autoLabel' => true,
                                    'model' => $catalogBaseGoods,
                                    'attribute' => 'market_place',
                                    'pluginOptions' => [
                                        'threeState' => false,
                                        'theme' => 'krajee-flatblue',
                                        'enclosedLabel' => false,
                                        'size' => 'lg',
                                    ],
                                    'labelSettings' => [
                                        'label' => Yii::t('message', 'frontend.views.vendor.place_mix', ['ru' => 'РАЗМЕСТИТЬ В MixMarket']),
                                        'position' => CheckboxX::LABEL_RIGHT,
                                        'options' => ['style' => 'font-weight: 700;']
                                    ]
                                ])->label(false);
                                ?>

                            </div>
                            <div class="pull-left" style="padding: 10px 10px 0px 10px;margin-top: 0;">    
                                <?=
                                $form->field($catalogBaseGoods, 'mp_show_price')->widget(CheckboxX::classname(), [
                                    //'initInputType' => CheckboxX::INPUT_CHECKBOX,
                                    'autoLabel' => true,
                                    'model' => $catalogBaseGoods,
                                    'attribute' => 'mp_show_price',
                                    'pluginOptions' => [
                                        'threeState' => false,
                                        'theme' => 'krajee-flatblue',
                                        'enclosedLabel' => false,
                                        'size' => 'lg',
                                    ],
                                    'labelSettings' => [
                                        'label' => Yii::t('message', 'frontend.views.vendor.show_price', ['ru' => 'Показывать цену в MixMarket']),
                                        'position' => CheckboxX::LABEL_RIGHT,
                                        'options' => ['style' => '']
                                    ]
                                ])->label(false);
                                ?>
                            </div>    
                            <div class="pull-right" style="margin-top: 10px;">
                                <?=
                                $catalogBaseGoods->isNewRecord ?
                                        Html::submitButton('<span><i class="icon fa fa-plus-circle"></i> ' . Yii::t('message', 'frontend.views.vendor.create_two', ['ru' => 'Создать']) . ' </span>', [
                                            'id' => 'btnSave',
                                            'class' => 'btn btn-success edit',
                                            'data-loading-text' => "<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span> " . Yii::t('message', 'frontend.views.vendor.creating', ['ru' => 'Создаем...']),
                                        ]) :
                                        Html::submitButton('<span><i class="icon fa fa-save"></i> ' . Yii::t('message', 'frontend.views.vendor.save_two', ['ru' => 'Сохранить']) . ' </span>', [
                                            'id' => 'btnSave',
                                            'class' => 'btn btn-success edit',
                                            'data-loading-text' => "<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span> " . Yii::t('message', 'frontend.views.vendor.save_three', ['ru' => 'Сохраняем...']),
                                        ])
                                ?>
                                <a id="btnCancel" href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-ban"></i> <?= Yii::t('message', 'frontend.views.vendor.cancel_five', ['ru' => 'Отмена']) ?></a>
                            </div>	
                        </div>
                    </div>
                    </div>
                    <?php ActiveForm::end(); ?>
