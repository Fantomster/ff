<?php
use yii\helpers\Html;
use kartik\export\ExportMenu;
use yii\helpers\Url;
use yii\web\View;
?>
<?=moonland\phpexcel\Excel::export([
    'models' => common\models\CatalogBaseGoods::find()
    ->addSelect(['article','product','units','price'])
    ->from ([common\models\CatalogBaseGoods::tableName().' cb'])
    ->leftJoin(common\models\Catalog::tableName().' c','cb.cat_id = c.id')
    ->where([
    'supp_org_id' => \common\models\User::getOrganizationUser(Yii::$app->user->id),
    'type'=>\common\models\Catalog::BASE_CATALOG
    ])
    ->all(),
    'columns' => ['article','product','units','price'],
        'headers' => ['article'=>'Артикул','product'=>'Продукт','units'=>'Кол-во','price'=>'Цена (руб)'],
]);?>

