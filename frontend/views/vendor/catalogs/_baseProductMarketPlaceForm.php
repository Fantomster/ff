<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
?>
<?php $form = ActiveForm::begin([
    'id' => 'marketplace-product-form',
    'action' => $catalogBaseGoods->isNewRecord? 
        Url::toRoute('vendor/ajax-create-product') : 
        Url::toRoute(['vendor/ajax-update-product-market-place', 'id' => $catalogBaseGoods->id]),           
    'fieldConfig' => [
         'options' => [
             'tag' => 'span'
             ],
    ],
]); 
?>
<style>
small{font-size:13px}    
</style>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title">Форма товара в MarketPlace</h4>
</div>
<div class="modal-body" style="background:#f7f8f9 !important">
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-8">
                    <h4 class="text-bolt" style="margin-top: 0px;margin-bottom:20px;">
                        <?php 
                        $field = $form->field($catalogBaseGoods, 'product');
                        echo $field->begin();
                        echo Html::activeTextInput($catalogBaseGoods, 'product', [
                            'placeholder'=>'Наименование', 
                            'style'=>'width:100%;
                                      background: #fff;
                                      border: 1px solid #d6d6d6;border-radius:ds3px;border: 1px solid #d6d6d6;border-radius:3px;padding:0 3px']);
                        echo Html::error($catalogBaseGoods,'product', ['class' => 'help-block h6']);
                        echo $field->end();
                        ?>
                    </h4>
                </div>
                <div class="col-md-4 text-center">
                    <h5 style="color:#ababac;font-style:italic;margin-top: 0px;margin-bottom:0;"><?= $currentOrgName ?></h5>
                </div>
            </div>
            <div class="row">
                <div class="col-md-8 no-padding">
                    <div class="col-md-12">
                        <!--hr-->
                        <small>Артикул: 
                            <b>
                            <?php 
                            $field = $form->field($catalogBaseGoods, 'article');
                            echo $field->begin();
                            echo Html::activeTextInput($catalogBaseGoods, 'article', ['placeholder'=>'Артикул', 
                                'style'=>'width:40%;
                                          background: #fff;
                                          border: 1px solid #d6d6d6;border-radius:ds3px;border: 1px solid #d6d6d6;border-radius:3px;padding:0 3px']);
                            echo Html::error($catalogBaseGoods,'article', ['class' => 'help-block h6']);
                            echo $field->end();
                            ?>
                            </b>
                        </small>
                        <h3  style="margin-top: 20px;margin-bottom:0px;">
                            <?php 
                            $field = $form->field($catalogBaseGoods, 'price');
                            echo $field->begin();
                            echo Html::activeTextInput($catalogBaseGoods, 'price', ['placeholder'=>'Цена', 'style'=>'width:120px;
                                      background: #fff;
                                      border: 1px solid #d6d6d6;border-radius:ds3px;border: 1px solid #d6d6d6;border-radius:3px;padding:0 3px']);
                            echo '<i class="fa fa-fw fa-rub" style="font-size:20px"></i> за ';
                            echo $catalogBaseGoods['units']. ' ' .$catalogBaseGoods['ed'];
                            echo Html::error($catalogBaseGoods,'price', ['class' => 'help-block h6']);
                            echo $field->end();
                            ?>
                        </h3>
                    </div>
                    <div class="col-md-12" style="margin-top: 10px;margin-bottom:20px;">
                        <small class="pull-left text-success" style="margin-right:15px">ПОКАЗАТЬ ТЕЛЕФОН</small>
                        <small class="pull-left text-success" style="margin-right:15px">ПОКАЗАТЬ EMAIL</small>
                        <small class="pull-left" style="color:#ababac;">САМОВЫВОЗ/КУРЬЕРОМ</small>
                    </div>
                    <div class="col-md-12 no-padding">
                        <div class="col-md-4">
                            <a href="#" class="btn btn-success btn-lg disabled" disabled="" style="width:100%">В карзину</a>
                        </div>
                        <div class="col-md-8">
                            <a href="#" class="btn btn-outline-success btn-lg disabled" disabled="" style="width:100%">Добавить поставщика</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <style>
                    .uploadButton {
                        position: absolute;
                        display: block;
                        width: 200px;
                        height: 200px;
                        border-radius: 50%;
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
                        width: 200px;
                        height: 200px;
                        border-radius: 50%;
                        top: 0;
                        margin: 0 auto;
                        left: 0;
                        right: 0;
                        opacity:0;
                    }
                    .cr-boundary{border-radius:50%}
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
                    #upload-avatar{border-radius:50%}
                    .cr-viewport{border-radius:50%}
                    </style>
                    <?php
                    $this->registerJs("
                        var uploadCrop = $('#upload-avatar').croppie({
                                viewport: {
                                        width: 210,
                                        height: 210,
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
                    <img id="newAvatar" class="img-circle" width="200" height="200" style="background-color:#ccc" src="<?= !empty($catalogBaseGoods['image'])?
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
                    <div style="position:absolute;bottom:15px;width:100%;left: 0;right:0;z-index:99">
                        <div style="color:#fff;background:#f39c12;width:50%;margin: 0 auto;border-radius:5px;">
                            <small><b>Кратность: за <?=$catalogBaseGoods['units']. ' ' .$catalogBaseGoods['ed']?></b></small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 no-padding">
                    <div class="col-md-6">
                        <h5>КОРОТКО О ТОВАРЕ</h5>
                        <small>Производитель: 
                            <b>
                            <?php 
                            $field = $form->field($catalogBaseGoods, 'brand');
                            echo $field->begin();
                            echo Html::activeTextInput($catalogBaseGoods, 'brand', ['placeholder'=>'Производитель', 'style'=>'width:40%;
                                      background: #fff;
                                      border: 1px solid #d6d6d6;border-radius:ds3px;border: 1px solid #d6d6d6;border-radius:3px;padding:0 3px']);
                            //echo Html::error($catalogBaseGoods,'price', ['class' => 'help-block h6']);
                            echo $field->end();
                            ?>
                            </b>
                        </small><br>
                        <small>Страна: 
                            <b>
                            <?php 
                            $field = $form->field($catalogBaseGoods, 'region');
                            echo $field->begin();
                            echo Html::activeTextInput($catalogBaseGoods, 'region', ['placeholder'=>'Страна', 'style'=>'width:40%;
                                      background: #fff;
                                      border: 1px solid #d6d6d6;border-radius:ds3px;border: 1px solid #d6d6d6;border-radius:3px;padding:0 3px']);
                            //echo Html::error($catalogBaseGoods,'price', ['class' => 'help-block h6']);
                            echo $field->end();
                            ?>
                            </b>
                        </small><br>
                        <small>Единица измерения: 
                            <b>
                            <?php 
                            $field = $form->field($catalogBaseGoods, 'ed');
                            echo $field->begin();
                            echo Html::activeTextInput($catalogBaseGoods, 'ed', ['placeholder'=>'Единица измерения', 'style'=>'width:40%;
                                      background: #fff;
                                      border: 1px solid #d6d6d6;border-radius:ds3px;border: 1px solid #d6d6d6;border-radius:3px;padding:0 3px']);
                            //echo Html::error($catalogBaseGoods,'ed', ['class' => 'help-block h6']);
                            echo $field->end();
                            ?>
                            </b>
                        </small><br>
                        <small>Вес: 
                            <b>
                            <?php 
                            $field = $form->field($catalogBaseGoods, 'weight');
                            echo $field->begin();
                            echo Html::activeTextInput($catalogBaseGoods, 'weight', ['placeholder'=>'Вес', 'style'=>'width:40%;
                                      background: #fff;
                                      border: 1px solid #d6d6d6;border-radius:ds3px;border: 1px solid #d6d6d6;border-radius:3px;padding:0 3px']);
                            //echo Html::error($catalogBaseGoods,'ed', ['class' => 'help-block h6']);
                            echo $field->end();
                            ?>
                            </b>
                        </small><br>
                        <small>Кратность: 
                            <b>
                            <?php 
                            $field = $form->field($catalogBaseGoods, 'units');
                            echo $field->begin();
                            echo Html::activeTextInput($catalogBaseGoods, 'units', ['placeholder'=>'Кратность', 'style'=>'width:40%;
                                      background: #fff;
                                      border: 1px solid #d6d6d6;border-radius:ds3px;border: 1px solid #d6d6d6;border-radius:3px;padding:0 3px']);
                            //echo Html::error($catalogBaseGoods,'units', ['class' => 'help-block h6']);
                            echo $field->end();
                            ?>
                            </b>
                        </small>
                    </div>   
                    <div class="col-md-6">
                        <h5>УСЛОВИЯ ДОСТАВКИ</h5>
                        <small>Стоимость доставки: 
                            <b>
                                <?= empty($delivery['delivery_charge'])?'(Не указано)':$delivery['delivery_charge'] ?>
                            </b>
                        </small><br>
                        <small>Стоимость заказа для бесплатной доставки у поставщика: 
                            <b>
                                <?= empty($delivery['min_free_delivery_charge'])?'(Не указано)':$delivery['min_free_delivery_charge'] ?>
                            </b>
                        </small><br>
                        <small>Минимальная стоимость заказа:
                            <b>
                                <?= empty($delivery['min_order_price'])?'(Не указано)':$delivery['min_order_price'] ?>
                            </b>
                        </small><br>
                        <small>Дни доставки: 
                            <b>
                                <?php 
                                $days = [];
                                empty($delivery['mon'])?'':array_push($days, 'Пн'); 
                                empty($delivery['tue'])?'':array_push($days, 'Вт'); 
                                empty($delivery['wed'])?'':array_push($days, 'Ср'); 
                                empty($delivery['thu'])?'':array_push($days, 'Чт'); 
                                empty($delivery['fri'])?'':array_push($days, 'Пт'); 
                                empty($delivery['sat'])?'':array_push($days, 'Сб'); 
                                empty($delivery['sun'])?'':array_push($days, 'Вс'); 
                                $i = 0;
                                $i_max = count($days);
                                if(!empty($i_max)){
                                    foreach ($days as $day) {
                                        $i++;
                                        if($i >= $i_max){
                                          echo $day;  
                                        }else{
                                          echo $day . ', ';  
                                        }   
                                    }
                                }else{
                                    echo '(Не указано)';
                                }
                                ?>
                            </b>
                        </small>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12" >
                    <h5 style="margin-top: 20px;">КОММЕНТАРИЙ</h5>
                    <small>
                            <?php 
                            $field = $form->field($catalogBaseGoods, 'note');
                            echo $field->begin();
                            echo Html::activeTextArea($catalogBaseGoods, 'note', ['placeholder'=>'Комментарий', 'style'=>'width:100%;
                                      background: #fff;
                                      border: 1px solid #d6d6d6;border-radius:ds3px;border: 1px solid #d6d6d6;border-radius:3px;padding:0 3px','rows'=>4]);
                            //echo Html::error($catalogBaseGoods,'note', ['class' => 'help-block h6']);
                            echo $field->end();
                            ?>  
                    </small>
                </div>
            </div>
        </div>
    </div> 
</div>
<div class="modal-footer" style="background:#f7f8f9 !important">
    <div class="input-group  pull-left" style="width:150px">
        <button class="form-control btn btn-outline-success market-place"><i class="icon fa fa-check"></i></button>                    
        <span class="input-group-addon" style="background:#6ea262;border-color:#6ea262;color:#fff;border-radius:0px 3px 3px 0px;">
            Разместить в F-MARKET?
        </span>
    </div>
                            <?php //= Html::button('<i class="icon fa fa-check"></i>', ['class' => 'btn btn-outline-success market-place pull-left']) ?>
    <?= Html::button($catalogBaseGoods->isNewRecord ? '<i class="icon fa fa-plus-circle"></i> Создать' : '<i class="icon fa fa-save"></i> Сохранить', ['class' => $catalogBaseGoods->isNewRecord ? 'btn btn-success edit' : 'btn btn-success edit']) ?>
    <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-ban"></i> Отмена</a>
</div>
<?php ActiveForm::end(); ?>