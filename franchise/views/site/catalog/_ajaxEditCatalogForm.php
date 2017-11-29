<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\depdrop\DepDrop;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;
use yii\web\View;
use yii\widgets\Pjax;
use kartik\checkbox\CheckboxX;
use yii\web\JsExpression;
use \yii\helpers\Json;
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
<?php $form = ActiveForm::begin([
    'id' => 'marketplace-product-form',
    'enableClientValidation' => true,
    'action' => $catalogBaseGoods->isNewRecord ? 
        Url::toRoute(['site/ajax-edit-catalog-form', 'catalog' => $catalog]) : 
        Url::toRoute(['site/ajax-edit-catalog-form', 'product_id' => $catalogBaseGoods->id, 'catalog' => $catalogBaseGoods->cat_id]),  
]); 
?>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title"><?=$catalogBaseGoods->isNewRecord ? Yii::t('app', 'franchise.views.site.catalog.adding_good', ['ru'=>'ДОБАВЛЕНИЕ ТОВАРА']):Yii::t('app', 'franchise.views.site.catalog.edit_good', ['ru'=>'РЕДАКТИРОВАНИЕ ТОВАРА'])?></h4>
</div>
<div class="modal-body" style="background:#fff !important">
	<div class="row">
		<div class="col-md-12">
			<div class="col-md-12" id="b-category" style="border: 1px dashed #77c497; padding: 15px;margin-top: 20px;margin-bottom: 10px">
                                <label class="control-label" for="dynamicmodel-sub1"><?= Yii::t('app', 'franchise.views.site.catalog.good_cat', ['ru'=>'Категория товара']) ?></label>
                            <?php
                            echo $form->field($catalogBaseGoods, 'sub1')->widget(Select2::classname(), [
                                'data' => ArrayHelper::map(\common\models\MpCategory::find()->where('parent IS NULL')->asArray()->all(),'id', 'name'),
                                'options' => ['placeholder' => Yii::t('app', 'franchise.views.site.catalog.choose', ['ru'=>'Выберите...'])],
                                'theme' => "default",
                                'pluginOptions' => [
                                    'allowClear' => true
                                ],
                            ])->label(false);
                            echo Html::hiddenInput('catalogBaseGoods_id1', $catalogBaseGoods->category_id, ['id'=>'catalogBaseGoods_id1']);
                            echo Html::hiddenInput('input-type-2', $catalogBaseGoods->sub2, ['id'=>'input-type-2']);
                            ?>
                            <?php
                            echo $form->field($catalogBaseGoods, 'sub2')->widget(DepDrop::classname(), [
                               'options' => [],
                               'type' => DepDrop::TYPE_SELECT2,
                               'select2Options'=>[
                                    'theme' => "default",
                                    'pluginOptions' => [
                                        'allowClear' => true
                                        ],
                                    ],
                                'pluginOptions'=>[
                                    'depends'=>['catalogbasegoods-sub1'],
                                    'placeholder' => false,
                                    'url' => Url::to(['site/get-sub-cat']),
                                    'loadingText' => Yii::t('app', 'franchise.views.site.catalog.downloading', ['ru'=>'Загрузка...']),
                                    'initialize' => true,
                                    'params'=>['catalogBaseGoods_id1','input-type-2'],
                                ]
                            ])->label(false);
                            ?>
                            <?= $catalogBaseGoods->isNewRecord? $form->field($catalogBaseGoods, 'cat_id')->hiddenInput(['value'=> $catalog])->label(false):'' ?>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<div class="col-md-12" style="padding: 0px;">
				<div class="col-md-6">
                                        <?= $form->field($catalogBaseGoods, 'product', 
    ['template'=>' {label}<div class="input-group">{input}</div>{error}'])->
    textInput(['placeholder' => Yii::t('app', 'franchise.views.site.catalog.good_name', ['ru'=>'НАИМЕНОВАНИЕ ТОВАРА']),'style'=>'border-radius:3px']) ?>
					<?= $form->field($catalogBaseGoods, 'article', 
    ['template'=>' {label}<div class="input-group">{input}<span class="input-group-addon">
    <span class="glyphicon glyphicon-question-sign" 
    data-toggle="tooltip" 
    title="'. Yii::t('app', 'franchise.views.site.catalog.art', ['ru'=>'Артикул товара - это сочетание букв, цифр, символов, которое обозначает данную модель товара']) .'">
    </span></span></div>{error}'])->
    textInput(['placeholder' => Yii::t('app', 'franchise.views.site.catalog.art_two', ['ru'=>'АРТИКУЛ'])]) ?>
                                    <?= $form->field($catalogBaseGoods, 'price', 
    ['template'=>' {label}<div class="input-group">{input}<span class="input-group-addon">
    <span class="glyphicon glyphicon-question-sign" 
    data-toggle="tooltip" 
    title="'. Yii::t('app', 'franchise.views.site.price', ['ru'=>'Цена за определенную количественную единицу (или за определенное число единиц) товара, указанную в обычно применяемых в торговле данным товаром единицах измерения (веса, длины, площади, объема, штук, комплектов и т. д.)']) .'">
    </span></span></div>{error}'])->
    textInput(['placeholder' => Yii::t('app', 'franchise.views.site.catalog.price_for_measure', ['ru'=>'ЦЕНА ЗА ЕД ИЗМЕРЕНИЯ'])]) ?>
                                    <label class="control-label" for=""><?= Yii::t('app', 'franchise.views.site.catalog.measure', ['ru'=>'Ед измерения']) ?></label>
                                    <?php
                                    echo $form->field($catalogBaseGoods, 'ed')->widget(Select2::classname(), [
                                        'model'=>$catalogBaseGoods->ed,
                                        'data' => ArrayHelper::map(\common\models\MpEd::find()->asArray()->all(),'name', 'name'),
                                        'options' => ['placeholder' => Yii::t('app', 'franchise.views.site.catalog.choose_two', ['ru'=>'Выберите...'])],
                                        'theme' => Select2::THEME_DEFAULT,
                                        'pluginOptions' => [
                                            'allowClear' => true,
                                        ],
                                     ])->label(false);?>
                                    <?= $form->field($catalogBaseGoods, 'units', 
    ['template'=>' {label}<div class="input-group">{input}<span class="input-group-addon">
    <span class="glyphicon glyphicon-question-sign"
    data-toggle="tooltip" 
    title="'. Yii::t('app', 'franchise.views.site.min', ['ru'=>'Минимальная партия товара (если кратоность 10, а единица измерения Пакет - это значит, что минимальная партия поставки = 10 пакетов)']) .'"></span></span></div>{error}'])->
    textInput(['placeholder' => Yii::t('app', 'franchise.views.site.catalog.multiplicity', ['ru'=>'КРАТНОСТЬ ПОСТАВКИ'])]) ?>
				</div>
				<div class="col-md-6">
                                    <label class="control-label" for=""><?= Yii::t('app', 'franchise.views.site.catalog.vendor_country', ['ru'=>'Страна производитель']) ?></label>
                                    <?php 
                                    echo $form->field($catalogBaseGoods, 'region')->widget(Select2::classname(), [
                                        'model'=>$catalogBaseGoods->region,
                                        'data' => ArrayHelper::map($countrys,'id', 'name'),
                                        'options' => ['placeholder' => Yii::t('app', 'franchise.views.site.catalog.choose_three', ['ru'=>'Выберите...'])],
                                        'theme' => "default",
                                        'pluginOptions' => [
                                            'allowClear' => true,
                                            //'tags' => true,
                                        ],
                                    ])->label(false);
                                    ?>
                                    
                                    <?= $form->field($catalogBaseGoods, 'brand', 
    ['template'=>' {label}<div class="input-group">{input}</div>{error}'])->
    textInput(['placeholder' => Yii::t('app', 'franchise.views.site.catalog.vendors_name', ['ru'=>'НАЗВАНИЕ ПРОИЗВОДИТЕЛЯ']),'style'=>'border-radius:3px']) ?>
                                    <?= $form->field($catalogBaseGoods, 'weight', 
    ['template'=>' {label}<div class="input-group">{input}</div>{error}'])->
    textInput(['placeholder' => Yii::t('app', 'franchise.views.site.catalog.weight', ['ru'=>'ВЕС УПАКОВКИ']),'style'=>'border-radius:3px']) ?>
                                    <?= $form->field($catalogBaseGoods, 'note')->textArea(['style' => 'height: 100%;min-height: 104px;']) ?>
				
                                </div>
			</div>
		</div>
	</div>
        
	<div class="row">
		<div class="col-md-12" style="padding: 15px 28px 4px 28px;">   
                    <div class="pull-right" style="margin-top: 10px;">
                    <?= Html::submitButton($catalogBaseGoods->isNewRecord ? 
                        '<i class="icon fa fa-plus-circle"></i> ' . Yii::t('app', 'franchise.views.site.catalog.create', ['ru'=>'Создать']) . ' ' :
                        '<i class="icon fa fa-save"></i> ' . Yii::t('app', 'franchise.views.site.catalog.save', ['ru'=>'Сохранить']) . ' ',
                        ['class' => $catalogBaseGoods->isNewRecord ? 
                        'btn btn-success edit' : 'btn btn-success edit']) ?>
                        <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-ban"></i> <?= Yii::t('app', 'franchise.views.site.catalog.cancel', ['ru'=>'Отмена']) ?></a>
                    </div>	
		</div>
	</div>
</div>
<?php ActiveForm::end(); ?>