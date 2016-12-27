<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$addAction = Url::to(["site/ajax-add-to-cart"]);

$this->title = 'F-MARKET главная';

$js = <<<JS
        $(document).on("click", ".add-to-cart", function(e) {
            e.preventDefault();
            //alert($(this).data("product-id"));
            $.post(
                "$addAction",
                {product_id: $(this).data("product-id")}
            ).done(function (result) {
                if (result) {
                    alert("Yes, we can!");
                } else {
                    alert("Fail!");
                }
            });
        });
JS;
$this->registerJs($js, \yii\web\View::POS_READY);

?>

<div class="row">
  <div class="col-md-12 min-padding">
    <h3>Популярные товары</h3>  
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="row">
      <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 min-padding">
        <div class="mp-product-block">
          <img class="product-image" src="http://vsovetah.ru/wp-content/uploads/2014/10/%D0%9A%D0%B0%D0%BA-%D0%BF%D1%80%D0%B0%D0%B2%D0%B8%D0%BB%D1%8C%D0%BD%D0%BE-%D0%B6%D0%B0%D1%80%D0%B8%D1%82%D1%8C-%D0%BC%D1%8F%D1%81%D0%BE.jpg">
          <div class="row">
            <div class="col-md-12">
              <div class="product-title">
                <h3>Рагу из свинины</h3>
              </div>
              <div class="product-category">
                <h5>Мясо/Говядина</h5>
              </div>
              <div class="product-company">
                <h5>ООО РикиТикиТави</h5>
              </div>
            </div>
            <div class="col-md-12">
              <div class="product-price pull-left">
                <h4>142968 руб.</h4>
              </div>
              <div class="product-button pull-right">
                <a href="#" class="btn btn-sm btn-cart add-to-cart" data-product-id="1"><isc class="icon-shopping-cart" aria-hidden="true"></isc></a>
              </div>
            </div>
          </div>
        </div>  
      </div>
      <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 min-padding">
        <div class="mp-product-block">
          <img class="product-image" src="http://vsovetah.ru/wp-content/uploads/2014/10/%D0%9A%D0%B0%D0%BA-%D0%BF%D1%80%D0%B0%D0%B2%D0%B8%D0%BB%D1%8C%D0%BD%D0%BE-%D0%B6%D0%B0%D1%80%D0%B8%D1%82%D1%8C-%D0%BC%D1%8F%D1%81%D0%BE.jpg">
          <div class="row">
            <div class="col-md-12">
              <div class="product-title">
                <h3>Рагу из свинины</h3>
              </div>
              <div class="product-category">
                <h5>Мясо/Говядина</h5>
              </div>
              <div class="product-company">
                <h5>ООО РикиТикиТави</h5>
              </div>
            </div>
            <div class="col-md-12">
              <div class="product-price pull-left">
                <h4>142968 руб.</h4>
              </div>
              <div class="product-button pull-right">
                <a href="#" class="btn btn-sm btn-cart"><isc class="icon-shopping-cart" aria-hidden="true"></isc></a>
              </div>
            </div>
          </div>
        </div>  
      </div>
      <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 min-padding">
        <div class="mp-product-block">
          <img class="product-image" src="http://vsovetah.ru/wp-content/uploads/2014/10/%D0%9A%D0%B0%D0%BA-%D0%BF%D1%80%D0%B0%D0%B2%D0%B8%D0%BB%D1%8C%D0%BD%D0%BE-%D0%B6%D0%B0%D1%80%D0%B8%D1%82%D1%8C-%D0%BC%D1%8F%D1%81%D0%BE.jpg">
          <div class="row">
            <div class="col-md-12">
              <div class="product-title">
                <h3>Рагу из свинины</h3>
              </div>
              <div class="product-category">
                <h5>Мясо/Говядина</h5>
              </div>
              <div class="product-company">
                <h5>ООО РикиТикиТави</h5>
              </div>
            </div>
            <div class="col-md-12">
              <div class="product-price pull-left">
                <h4>142968 руб.</h4>
              </div>
              <div class="product-button pull-right">
                <a href="#" class="btn btn-sm btn-cart"><isc class="icon-shopping-cart" aria-hidden="true"></isc></a>
              </div>
            </div>
          </div>
        </div>  
      </div>
      <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 min-padding">
        <div class="mp-product-block">
          <img class="product-image" src="http://vsovetah.ru/wp-content/uploads/2014/10/%D0%9A%D0%B0%D0%BA-%D0%BF%D1%80%D0%B0%D0%B2%D0%B8%D0%BB%D1%8C%D0%BD%D0%BE-%D0%B6%D0%B0%D1%80%D0%B8%D1%82%D1%8C-%D0%BC%D1%8F%D1%81%D0%BE.jpg">
          <div class="row">
            <div class="col-md-12">
              <div class="product-title">
                <h3>Рагу из свинины</h3>
              </div>
              <div class="product-category">
                <h5>Мясо/Говядина</h5>
              </div>
              <div class="product-company">
                <h5>ООО РикиТикиТави</h5>
              </div>
            </div>
            <div class="col-md-12">
              <div class="product-price pull-left">
                <h4>142968 руб.</h4>
              </div>
              <div class="product-button pull-right">
                <a href="#" class="btn btn-sm btn-cart"><isc class="icon-shopping-cart" aria-hidden="true"></isc></a>
              </div>
            </div>
          </div>
        </div>  
      </div>
      <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 min-padding">
        <div class="mp-product-block">
          <img class="product-image" src="http://vsovetah.ru/wp-content/uploads/2014/10/%D0%9A%D0%B0%D0%BA-%D0%BF%D1%80%D0%B0%D0%B2%D0%B8%D0%BB%D1%8C%D0%BD%D0%BE-%D0%B6%D0%B0%D1%80%D0%B8%D1%82%D1%8C-%D0%BC%D1%8F%D1%81%D0%BE.jpg">
          <div class="row">
            <div class="col-md-12">
              <div class="product-title">
                <h3>Рагу из свинины</h3>
              </div>
              <div class="product-category">
                <h5>Мясо/Говядина</h5>
              </div>
              <div class="product-company">
                <h5>ООО РикиТикиТави</h5>
              </div>
            </div>
            <div class="col-md-12">
              <div class="product-price pull-left">
                <h4>142968 руб.</h4>
              </div>
              <div class="product-button pull-right">
                <a href="#" class="btn btn-sm btn-cart"><isc class="icon-shopping-cart" aria-hidden="true"></isc></a>
              </div>
            </div>
          </div>
        </div>  
      </div>
      <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 min-padding">
        <div class="mp-product-block">
          <img class="product-image" src="http://vsovetah.ru/wp-content/uploads/2014/10/%D0%9A%D0%B0%D0%BA-%D0%BF%D1%80%D0%B0%D0%B2%D0%B8%D0%BB%D1%8C%D0%BD%D0%BE-%D0%B6%D0%B0%D1%80%D0%B8%D1%82%D1%8C-%D0%BC%D1%8F%D1%81%D0%BE.jpg">
          <div class="row">
            <div class="col-md-12">
              <div class="product-title">
                <h3>Рагу из свинины</h3>
              </div>
              <div class="product-category">
                <h5>Мясо/Говядина</h5>
              </div>
              <div class="product-company">
                <h5>ООО РикиТикиТави</h5>
              </div>
            </div>
            <div class="col-md-12">
              <div class="product-price pull-left">
                <h4>142968 руб.</h4>
              </div>
              <div class="product-button pull-right">
                <a href="#" class="btn btn-sm btn-cart"><isc class="icon-shopping-cart" aria-hidden="true"></isc></a>
              </div>
            </div>
          </div>
        </div>  
      </div>
    </div>
    <div class="row">
      <div class="col-md-12 min-padding" style="margin-bottom: 10px">
        <a href="#" class="btn btn-outline-ajax">Показать еще</a>  
      </div>   
    </div>
    <div class="row">
      <div class="col-md-12 min-padding">
        <h3>Поставщики</h3>  
      </div>
    </div>
    <div class="row">
      <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 min-padding">
        <div class="mp-suppiler-block">
          <img class="supplier-image" src="http://www.logodesigner.ru/files/covers/%D0%BF%D1%80%D0%B5%D0%B2%D1%8C%D1%8E%20%D0%93%D0%BB%D0%BE%D0%B1%D0%B0%D0%BB%D0%92%D0%B8%D1%82_1.jpg">
          <div class="row">
            <div class="col-md-12">
              <div class="supplier-title">
                <h3>ООО "Шератон"</h3>
              </div>
              <div class="supplier-category">
                <h5>Россия, Москва</h5>
              </div>
              <div class="supplier-company">
                <h5>Овощи, фрукты</h5>
              </div>
            </div>
            <div class="col-md-12">
              <div class="supplier-button">
                <a href="#" class="btn btn-success" style="width: 100%">Заказать</a>
              </div>
            </div>
          </div>
        </div>  
      </div>
      <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 min-padding">
        <div class="mp-suppiler-block">
          <img class="supplier-image" src="http://www.logodesigner.ru/files/covers/%D0%BF%D1%80%D0%B5%D0%B2%D1%8C%D1%8E%20%D0%93%D0%BB%D0%BE%D0%B1%D0%B0%D0%BB%D0%92%D0%B8%D1%82_1.jpg">
          <div class="row">
            <div class="col-md-12">
              <div class="supplier-title">
                <h3>ООО "Шератон"</h3>
              </div>
              <div class="supplier-category">
                <h5>Россия, Москва</h5>
              </div>
              <div class="supplier-company">
                <h5>Овощи, фрукты</h5>
              </div>
            </div>
            <div class="col-md-12">
              <div class="supplier-button">
                <a href="#" class="btn btn-success" style="width: 100%">Заказать</a>
              </div>
            </div>
          </div>
        </div>  
      </div>
      <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 min-padding">
        <div class="mp-suppiler-block">
          <img class="supplier-image" src="http://www.logodesigner.ru/files/covers/%D0%BF%D1%80%D0%B5%D0%B2%D1%8C%D1%8E%20%D0%93%D0%BB%D0%BE%D0%B1%D0%B0%D0%BB%D0%92%D0%B8%D1%82_1.jpg">
          <div class="row">
            <div class="col-md-12">
              <div class="supplier-title">
                <h3>ООО "Шератон"</h3>
              </div>
              <div class="supplier-category">
                <h5>Россия, Москва</h5>
              </div>
              <div class="supplier-company">
                <h5>Овощи, фрукты</h5>
              </div>
            </div>
            <div class="col-md-12">
              <div class="supplier-button">
                <a href="#" class="btn btn-success" style="width: 100%">Заказать</a>
              </div>
            </div>
          </div>
        </div>  
      </div>
      <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 min-padding">
        <div class="mp-suppiler-block">
          <img class="supplier-image" src="http://www.logodesigner.ru/files/covers/%D0%BF%D1%80%D0%B5%D0%B2%D1%8C%D1%8E%20%D0%93%D0%BB%D0%BE%D0%B1%D0%B0%D0%BB%D0%92%D0%B8%D1%82_1.jpg">
          <div class="row">
            <div class="col-md-12">
              <div class="supplier-title">
                <h3>ООО "Шератон"</h3>
              </div>
              <div class="supplier-category">
                <h5>Россия, Москва</h5>
              </div>
              <div class="supplier-company">
                <h5>Овощи, фрукты</h5>
              </div>
            </div>
            <div class="col-md-12">
              <div class="supplier-button">
                <a href="#" class="btn btn-success" style="width: 100%">Заказать</a>
              </div>
            </div>
          </div>
        </div>  
      </div>
      <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 min-padding">
        <div class="mp-suppiler-block">
          <img class="supplier-image" src="http://www.logodesigner.ru/files/covers/%D0%BF%D1%80%D0%B5%D0%B2%D1%8C%D1%8E%20%D0%93%D0%BB%D0%BE%D0%B1%D0%B0%D0%BB%D0%92%D0%B8%D1%82_1.jpg">
          <div class="row">
            <div class="col-md-12">
              <div class="supplier-title">
                <h3>ООО "Шератон"</h3>
              </div>
              <div class="supplier-category">
                <h5>Россия, Москва</h5>
              </div>
              <div class="supplier-company">
                <h5>Овощи, фрукты</h5>
              </div>
            </div>
            <div class="col-md-12">
              <div class="supplier-button">
                <a href="#" class="btn btn-success" style="width: 100%">Заказать</a>
              </div>
            </div>
          </div>
        </div>  
      </div>
      <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 min-padding">
        <div class="mp-suppiler-block">
          <img class="supplier-image" src="http://www.logodesigner.ru/files/covers/%D0%BF%D1%80%D0%B5%D0%B2%D1%8C%D1%8E%20%D0%93%D0%BB%D0%BE%D0%B1%D0%B0%D0%BB%D0%92%D0%B8%D1%82_1.jpg">
          <div class="row">
            <div class="col-md-12">
              <div class="supplier-title">
                <h3>ООО "Шератон"</h3>
              </div>
              <div class="supplier-category">
                <h5>Россия, Москва</h5>
              </div>
              <div class="supplier-company">
                <h5>Овощи, фрукты</h5>
              </div>
            </div>
            <div class="col-md-12">
              <div class="supplier-button">
                <a href="#" class="btn btn-success" style="width: 100%">Заказать</a>
              </div>
            </div>
          </div>
        </div>  
      </div>
    </div>
    <div class="row">
      <div class="col-md-12 min-padding">
        <a href="#" class="btn btn-outline-ajax">Показать еще</a>  
      </div>   
    </div>
  </div> 
</div> 

<?php
$customJs = <<< JS

JS;
$this->registerJs($customJs, View::POS_READY);
?>