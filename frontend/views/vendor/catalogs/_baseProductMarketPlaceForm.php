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
kartik\checkbox\KrajeeFlatBlueThemeAsset::register($this);

?>
<style>
.form-control:focus {
    border-color: #84bf76;
    outline: 0;
    box-shadow: inset 0px 3px 5px 0px rgba(91, 137, 81, 0.3);
}
.bl-img{
    width: 152px;
    height:102px;
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
    width: 176px;
    height: 119px;
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
    width: 176px;
    height: 119px;
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
    'action' => $catalogBaseGoods->isNewRecord ? 
        Url::toRoute('vendor/ajax-create-product-market-place') : 
        Url::toRoute(['vendor/ajax-update-product-market-place', 'id' => $catalogBaseGoods->id]),  
]); 
?>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title"><?=$catalogBaseGoods->isNewRecord ? 'ДОБАВЛЕНИЕ ТОВАРА':'РЕДАКТИРОВАНИЕ ТОВАРА'?></h4>
</div>
<div class="modal-body" style="background:#fff !important">
	<div class="row">
		<div class="col-md-12 text-center">
			<h5 class="sub-h3-text">Вы можете разместить Ваш товар в Маркете</h5>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12 text-center">
                    <?php
                    $this->registerJs("
                        var uploadCrop = $('#upload-avatar').croppie({
                                viewport: {
                                        width: 178,
                                        height: 121,
                                        //type: 'circle'
                                        type: 'square'
                                },
                                update: function(){
                                    uploadCrop.croppie('result', 'canvas').then(function (resp) {
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
                    <img id="newAvatar" width="176" height="119" style="background-color:#ccc"
                         src="<?= !empty($catalogBaseGoods['image'])?
                            $catalogBaseGoods->imageUrl:
                            common\models\CatalogBaseGoods::DEFAULT_IMAGE ?>" class="avatar"/>
                    <?=
                    Html::a('<i class="fa fa-trash"></i>', '#', [
                            'class' => 'btn btn-outline-danger btn-sm hide',
                            'style'=>'position:absolute;top:10px;left:0;right:0;margin:0 auto;width:37px;z-index:33',
                            'id' => 'deleteAvatar',
                        ]);
                    ?>
                    <label for="upload" class="uploadButton">Загрузить файл</label>
                    <input style="opacity: 0; z-index: -1;" type="file" accept="image/*" id="upload">
                    <?= Html::hiddenInput('CatalogBaseGoods[image]', null, ['id' => 'image-crop-result']) ?>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<div class="col-md-12" id="b-category" style="border: 1px dashed #77c497; padding: 15px;margin-top: 20px;margin-bottom: 10px">
                                <label class="control-label" for="dynamicmodel-sub1">Категория товара</label>
                            <?php
                            echo $form->field($categorys, 'sub1')->widget(Select2::classname(), [
                                //'model'=>$categorys->sub1,
                                'data' => ArrayHelper::map(\common\models\MpCategory::find()->where('parent IS NULL')->asArray()->all(),'id', 'name'),
                                'options' => ['placeholder' => 'Выберите...'],
                                'theme' => "default",
                                //'hideSearch' => true,
                                'pluginOptions' => [
                                    'allowClear' => true
                                ],
                            ])->label(false);
                            echo Html::hiddenInput('catalogBaseGoods_id1', $catalogBaseGoods->category_id, ['id'=>'catalogBaseGoods_id1']);
                            ?>
                            <?php
                            echo $form->field($categorys, 'sub2')->widget(DepDrop::classname(), [
                               'options' => [],
                               'type' => DepDrop::TYPE_SELECT2,
                               'select2Options'=>[
                                    'model'=>$categorys->sub2,
                                    'theme' => "default",
                                    //'hideSearch' => true,
                                    'pluginOptions' => [
                                        'allowClear' => true
                                        ],
                                    ],
                                'pluginOptions'=>[
                                    'depends'=>['dynamicmodel-sub1'],
                                    'placeholder'=>'',
                                    'url' => Url::to(['vendor/get-sub-cat']),
                                    'loadingText' => 'Загрузка...',
                                    'initialize' => true,
                                    //'initDepends'=>['dynamicmodel-sub1'],
                                    'params'=>['catalogBaseGoods_id1'],
                                ]
                            ])->label(false);
                            ?>
                            <?= $catalogBaseGoods->isNewRecord? $form->field($catalogBaseGoods, 'cat_id')->hiddenInput(['value'=> Yii::$app->request->get('id')])->label(false):'' ?>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<div class="col-md-12" style="padding: 0px;">
				<div class="col-md-6">
                                        <?= $form->field($catalogBaseGoods, 'product', 
    ['template'=>' {label}<div class="input-group">{input}</div>{error}'])->
    textInput(['placeholder' => 'НАИМЕНОВАНИЕ ТОВАРА','style'=>'border-radius:3px']) ?>
					<?= $form->field($catalogBaseGoods, 'article', 
    ['template'=>' {label}<div class="input-group">{input}<span class="input-group-addon">
    <span class="glyphicon glyphicon-question-sign" 
    data-toggle="tooltip" 
    title="Артикул товара - это сочетание букв, цифр, символов, которое обозначает данную модель товара">
    </span></span></div>{error}'])->
    textInput(['placeholder' => 'АРТИКУЛ']) ?>
                                    <?= $form->field($catalogBaseGoods, 'price', 
    ['template'=>' {label}<div class="input-group">{input}<span class="input-group-addon">
    <span class="glyphicon glyphicon-question-sign" 
    data-toggle="tooltip" 
    title="Цена за определенную количественную единицу (или за определенное число единиц) товара, указанную в обычно применяемых в торговле данным товаром единицах измерения (веса, длины, площади, объема, штук, комплектов и т. д.)">
    </span></span></div>{error}'])->
    textInput(['placeholder' => 'ЦЕНА ЗА ЕД ИЗМЕРЕНИЯ']) ?>
                                    <label class="control-label" for="">Ед измерения</label>
                                    <?php
                                    echo $form->field($catalogBaseGoods, 'ed')->widget(Select2::classname(), [
                                        'model'=>$catalogBaseGoods->ed,
                                        'data' => ArrayHelper::map(\common\models\MpEd::find()->asArray()->all(),'name', 'name'),
                                        'options' => ['placeholder' => 'Выберите...'],
                                        'theme' => Select2::THEME_DEFAULT,
                                        'pluginOptions' => [
                                            'allowClear' => true,
                                        ],
                                     ])->label(false);?>
                                    <?= $form->field($catalogBaseGoods, 'units', 
    ['template'=>' {label}<div class="input-group">{input}<span class="input-group-addon">
    <span class="glyphicon glyphicon-question-sign"
    data-toggle="tooltip" 
    title="Минимальная партия товара (если кратоность 10, а единица измерения Пакет - это значит, что минимальная партия поставки = 10 пакетов)"></span></span></div>{error}'])->
    textInput(['placeholder' => 'КРАТНОСТЬ ПОСТАВКИ']) ?>
				</div>
				<div class="col-md-6">
                                    <label class="control-label" for="">Страна производитель</label>
                                    <?php
                                    echo $form->field($catalogBaseGoods, 'region')->widget(Select2::classname(), [
                                        'model'=>$catalogBaseGoods->region,
                                        'data' => ArrayHelper::map(\common\models\MpCountry::find()->asArray()->all(),'id', 'name'),
                                        'options' => ['placeholder' => 'Выберите...'],
                                        'theme' => "default",
                                        'pluginOptions' => [
                                            'allowClear' => true
                                        ],
                                    ])->label(false);?>
                                    <?= $form->field($catalogBaseGoods, 'brand', 
    ['template'=>' {label}<div class="input-group">{input}</div>{error}'])->
    textInput(['placeholder' => 'НАЗВАНИЕ ПРОИЗВОДИТЕЛЯ','style'=>'border-radius:3px']) ?>
                                    <?= $form->field($catalogBaseGoods, 'weight', 
    ['template'=>' {label}<div class="input-group">{input}</div>{error}'])->
    textInput(['placeholder' => 'ВЕС УПАКОВКИ','style'=>'border-radius:3px']) ?>
                                    <?= $form->field($catalogBaseGoods, 'note')->textArea(['style' => 'height: 100%;min-height: 113px;']) ?>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12" style="padding: 15px 28px 4px 28px;">
                    <div class="pull-left">
                            <?=$form->field($catalogBaseGoods, 'market_place')->widget(CheckboxX::classname(), [
                            //'initInputType' => CheckboxX::INPUT_CHECKBOX,
                            'autoLabel' => true,
                            'model' => $catalogBaseGoods,
                            'attribute' => 'market_place',
                            'pluginOptions'=>[
                                'threeState'=>false,
                                'theme' => 'krajee-flatblue',
                                'enclosedLabel' => true,
                                'size'=>'lg',
                                ],
                            'labelSettings' => [
                                'label' => 'РАЗМЕСТИТЬ В F-MARKET',
                                'position' => CheckboxX::LABEL_RIGHT
                                ]
                            ])->label(false);?>
                            
                        </div><!--h5 class="dp-text pull-left" style="margin-top: 6px;">ДОБАВИТЬ В F-MARKET</h5-->	
			<div class="pull-right" style="margin-bottom: 20px">
			<?= Html::button($catalogBaseGoods->isNewRecord ? 
                            '<i class="icon fa fa-plus-circle"></i> Создать' : 
                            '<i class="icon fa fa-save"></i> Сохранить', 
                            ['class' => $catalogBaseGoods->isNewRecord ? 
                            'btn btn-success edit' : 'btn btn-success edit']) ?>
                            <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-ban"></i> Отмена</a>
			</div>	
		</div>
	</div>
</div>
<?php ActiveForm::end(); ?>
<?php
$customJs = <<< JS
//$('#dynamicmodel-sub1').val()==''?$('#dynamicmodel-sub1').addClass('hide'):
//        $('#dynamicmodel-sub1').removeClass('hide')
JS;
$this->registerJs($customJs, View::POS_READY);
?>