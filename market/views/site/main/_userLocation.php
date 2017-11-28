<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\checkbox\CheckboxX;
kartik\checkbox\KrajeeFlatBlueThemeAsset::register($this);
?>
<style>
.loc-block{padding:15px;position: relative; margin: 0 auto;z-index:99999}
.loc-h-city{font-family: sans-serif;text-transform: uppercase;color: #77a267;border-bottom: 1px dotted;}
.loc-list-cityes{text-align: center;margin-top: 20px}
.loc-submit{margin-top:20px}
.pac-container {
    z-index: 1051 !important;
}
</style>
<?php
$this->registerJs('
    function stopRKey(evt) { 
        var evt = (evt) ? evt : ((event) ? event : null); 
        var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null); 
        if ((evt.keyCode == 13) && (node.type=="text")) {return false;} 
    } 
    document.onkeypress = stopRKey;',
yii\web\View::POS_READY);
?>
<div id="data-modal" class="modal fade data-modal">
    <div class="modal-dialog" style="margin-top: 25%;">
        <button type="button" data-dismiss="modal" class="close hidden"></button>
        <div class="modal-content">
            <div class="loc-block">
                <?php
                $form = ActiveForm::begin([
                            'id' => 'user-location',
                            'action' => Url::to('/site/location-user'),
                ]);
                ?>
                  <?php if (Yii::$app->session->hasFlash('warning')): ?>
                <div class="alert alert-danger">
                <?= Yii::t('error', 'market.views.site.main.city_error', ['ru'=>'<strong>Ошибка!</strong> Извините, данный город не поддерживается системой MixCart!<br> Обратитесь в службу поддержки, и мы обязательно вам поможем!']) ?>
                </div>
                  <?php endif; ?>
                    <h3><i class="fa fa-location-arrow"></i> <?= Yii::t('message', 'market.views.site.main.your_city', ['ru'=>'ВАШ ГОРОД']) ?> <span id="setLocality" class="loc-h-city"><?=Yii::$app->request->cookies->get('locality')?></span>?</h3>
                    <h5><?= Yii::t('message', 'market.views.site.main.wrong_city', ['ru'=>'Если мы неверно определили Ваш город, пожалуйста, найдите его самостоятельно']) ?></h5>
                    <?php
//                    echo CheckboxX::widget([
//                        'name'=>'s_11',
//                        'readonly'=>false, 
//                        'options'=>['id'=>'s_11'], 
//                        'pluginOptions'=>[
//                                'threeState'=>false,
//                                'enclosedLabel' => false,
//                                'size'=>'lg',
//                        ]
//                    ]);
//                    echo '<label class="cbx-label" for="s_11" class="text-muted"> '
//                    . 'Включая <span id="viewRegion" class="loc-h-city">' . 
//                      Yii::$app->request->cookies->get('region') 
//                    . '</span>?</label>';
                    ?>
                    <input type="text" class="form-control autocomplete" id="search_out" name="search_out" placeholder="<?= Yii::t('message', 'market.views.site.main.search', ['ru'=>'Поиск']) ?>">
                    <input type="hidden" id="country" name="country" value="<?=Yii::$app->request->cookies->get('country')?>">
                    <input type="hidden" id="administrative_area_level_1" name="administrative_area_level_1" value="<?=Yii::$app->request->cookies->get('region')?>">
                    <input type="hidden" id="locality" name="locality" value="<?=Yii::$app->request->cookies->get('locality')?>">
                    <input type="hidden" id="currentUrl" name="currentUrl" value="<?=Yii::$app->getRequest()->getUrl()?>">
                    <button type="submit" class="btn btn-md btn-success loc-submit"><?= Yii::t('message', 'market.views.site.main.confirm', ['ru'=>'Подтвердить']) ?></button>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

