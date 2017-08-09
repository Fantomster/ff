<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
?>
<style>
.loc-block{padding:15px;text-align: center; position: relative; margin: 0 auto;z-index:99999}
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
                    <h3><i class="fa fa-location-arrow"></i> ВАШ ГОРОД <span id="setLocality" class="loc-h-city"><?=Yii::$app->session->get('locality')?></span>?</h3>
                    <h5>Если мы определили не верно Ваш город, пожалуйста, найдите его самостоятельно</h5>
                    <input type="text" class="form-control autocomplete" id="search_out" name="search_out" placeholder="Поиск">
                    <input type="hidden" id="country" name="country" value="<?=Yii::$app->session->get('country')?>">
                    <input type="hidden" id="administrative_area_level_1" name="administrative_area_level_1" value="<?=Yii::$app->session->get('region')?>">
                    <input type="hidden" id="locality" name="locality" value="<?=Yii::$app->session->get('locality')?>">
                    <input type="hidden" id="currentUrl" name="currentUrl" value="<?=Yii::$app->getRequest()->getUrl()?>">
                    <button type="submit" class="btn btn-md btn-success loc-submit">Подтвердить</button>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

