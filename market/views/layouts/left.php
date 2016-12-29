<?php
use yii\helpers\Html;
$count_products_from_mp = \common\models\CatalogBaseGoods::find()->where(['market_place'=>1])->count();
$left_menu_categorys = \common\models\MpCategory::find()->select('id,name,parent')->where(['parent'=>NULL])->asArray()->all();
$left_menu_categorys_sub = \common\models\MpCategory::find()->select('id,name,parent,')->where('parent is not null')->asArray()->all();
?>
<div class="row">
    <div class="col-md-12">
        <h3>Каталог <span class="badge pull-right"><?=$count_products_from_mp;?> товаров</span></h3>  
    </div>
</div>
<div class="row">
    <div class="col-md-12">
      <div class="category">
        <ul class="list-unstyled">
<?php
foreach($left_menu_categorys as $row){
    echo '<div class="dropdown">
              <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown">'.$row['name'].' 
                <!--span class="badge"></span-->
                <span class="caret pull-right"></span>
              </button>
              <div class="dropdown-menu" aria-labelledby="dropdownMenu2">
                <div class="col-sm-12 no-padding">
                  <div class="sub-cat">
                    <ul class="list-unstyled">';
    foreach($left_menu_categorys_sub as $row2){
        if($row['id'] == $row2['parent']){
            echo     '<li><a href="?r=site/category&id=' . $row2['id'] . '" title="">'.$row2['name'].' '
                    . '<span class="badge">'
                    .// \common\models\MpCategory::getCountProduct($row2['id']).
                    '</span></a></li>';
        }
    }
            echo   '</ul>
                  </div>
                </div>
              </div>
          </div>';}?>
        </ul>
      </div>
    </div>
</div>