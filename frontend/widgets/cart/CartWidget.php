<?php

namespace frontend\widgets\cart;

use yii\base\Widget;

/**
 * Description of CartWidget
 *
 * @author fenris
 */
class CartWidget extends Widget {

    public $orders;

    public function init() {
        parent::init();
        $cartUrl = \yii\helpers\Url::to(['order/pjax-cart']);
        $this->view->registerJs('
    $(".basket_a").on("click", function (e) {
        var p = $(".block_right_basket").css("right");
        if (p == "0px") {
            e.preventDefault();
            $(".maska1").fadeOut(300);
            $(".block_right_basket").animate({"right": "-100%"}, 400);
        }
        else {
            e.preventDefault();
            $.pjax.reload("#side-cart", {url:"'.$cartUrl.'", replace: false,timeout:30000});
            $(".block_right_basket").animate({"right": "0"}, 400);
            $(".maska1").fadeIn(300);
        }
    });
    $(document).on("click", ".maska1, .hide_basket", function () {
        $(".maska1").fadeOut(300);
        $(".block_right_basket").animate({"right": "-100%"}, 400);
    });
    $(document).on("click", ".cart-delete-position", function(e) {
        e.preventDefault();
        clicked = $(this);
        $.post(
            clicked.data("url")
        )
        .done(function (result) {
            $(\'a[data-id="\'+result+\'"]\').parent().parent().removeClass("success");
            $.pjax.reload("#side-cart", {url:"'.$cartUrl.'", replace: false,timeout:30000});
        });
        return false;
    });
                 ');
    }

    public function run() {
        $asset = CartWidgetAsset::register($this->getView());
        $baseUrl = $asset->baseUrl;
        return $this->render('_cart', ['orders' => $this->orders, 'baseUrl' => $baseUrl]);
    }

}
