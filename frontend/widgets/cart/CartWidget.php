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
    }

    public function run() {
        $asset = CartWidgetAsset::register($this->getView());
        $baseUrl = $asset->baseUrl;
        return $this->render('_cart', ['orders' => $this->orders, 'baseUrl' => $baseUrl]);
    }

}
