<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;
use yii\web\View;
use yii\widgets\Pjax;

?>
<style>
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
    margin-top: 0;
}
.select2-container .select2-selection--single .select2-selection__rendered {
    padding-left: 0px;
}
.select2-selection{margin-top:5px;}
.select2-container--default .select2-selection--single .select2-selection__arrow b {
    margin-top: 6px;
}
</style>
<?php
$mpCat = ArrayHelper::map(\common\models\MpCategory::find()->where('parent IS NULL')->asArray()->all(), 'id', 'name');
foreach ($mpCat as &$item){
    $item['name'] = Yii::t('app', $item);
}
echo Select2::widget([
    'theme' => Select2::THEME_DEFAULT,
    'name'=>'catag-ids',
    'value'=>0,
    'data' => array_merge([0 => Yii::t('app', 'franchise.views.catalog.catalogs.choose_four', ['ru'=>"Выберите..."])],
        $mpCat),
    'options' => ['placeholder' => Yii::t('app', 'franchise.views.catalog.catalogs.choose_five', ['ru'=>'Выберите...']), 'id' => 'catag-ids','style'=>'margin-top:10px'],
    'hideSearch' => true,
    'pluginOptions' => [
        'allowClear' => false,
    ],
]);
?>