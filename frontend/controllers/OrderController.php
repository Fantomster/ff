<?php

namespace frontend\controllers;

use Yii;

class OrderController extends DefaultController {
    /*
     *  index
     */
    public function actionIndex() {
        $session = Yii::$app->session;
        $session->remove('categories');
        $session->remove('vendors');
        $client = $this->currentUser->organization;
        
        if (!$session->has('categories')) {
            $categories = $client->getRestaurantCategories();
            for ($i = 0; $i < count($categories); $i++) {
                $categories[$i]['selected'] = 1;
            }
            $session['categories'] = $categories;
        } else {
            $categories = $session['categories'];
        }
        if (!$session->has('vendors')) {
            $vendors = $client->getSuppliers($categories);
            for ($i = 0; $i < count($vendors); $i++) {
                $vendors[$i]['selected'] = 1;
            }
        } else {
            $vendors = $session['vendors'];
        }
        $searchModel = new OrderCatalogSearch();
        $searchModel->vendors = $vendors;
        $params = Yii::$app->request->getQueryParams();
        $dataProvider = 1;//$searchModel->search($params);

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('_products', compact('searchModel', 'dataProvider'));
        } else {
            return $this->render('index', compact('categories', 'vendors', 'searchModel', 'dataProvider'));
        }
    }
}
