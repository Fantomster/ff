<?php

namespace market\controllers;

use api_web\classes\CartWebApi;
use common\models\DeliveryRegions;
use yii\web\HttpException;
use Yii;
use yii\web\Controller;
use common\models\Order;
use common\models\Organization;
use common\models\RelationSuppRest;
use common\models\CatalogBaseGoods;
use common\models\OrderContent;
use common\models\Catalog;
use yii\helpers\Json;
use yii\web\Response;

//ini_set('xdebug.max_nesting_level', 200);

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    /* public function behaviors()
      {
      return [
      'access' => [
      'class' => AccessControl::className(),
      'rules' => [
      [
      'actions' => ['login', 'error'],
      'allow' => true,
      ],
      [
      'actions' => ['logout', 'index'],
      'allow' => true,
      'roles' => ['@'],
      ],
      ],
      ],
      'verbs' => [
      'class' => VerbFilter::className(),
      'actions' => [
      'logout' => ['post'],
      ],
      ],
      ];
      } */

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function beforeAction($action)
    {
        /* if (!(Yii::$app->request->cookies->get('country') || Yii::$app->request->cookies->get('locality')) && Yii::$app->controller->module->requestedRoute != 'site/index') {
          return $this->redirect(['/site/index']);
          } else {

          } */
        if (!parent::beforeAction($action)) {
            return false;
        }
        return true;
    }

    public function actionLocationUser()
    {
        $request = Yii::$app->request;
        $cookies = Yii::$app->response->cookies;
        $locality = $request->post('locality');
        $region = $request->post('administrative_area_level_1');
        $country = $request->post('country');
        $currentUrl = $request->post('currentUrl');
        if ($locality == '' || $locality == 'undefined') {
            Yii::$app->session->addFlash("warning", "");
            $cookies->add(new \yii\web\Cookie(['name' => 'locality', 'value' => 0,]));
            $cookies->add(new \yii\web\Cookie(['name' => 'region', 'value' => 0,]));
            $cookies->add(new \yii\web\Cookie(['name' => 'country', 'value' => 0,]));
        } else {
            $cookies->add(new \yii\web\Cookie(['name' => 'locality', 'value' => $locality,]));
            $cookies->add(new \yii\web\Cookie(['name' => 'region', 'value' => $region,]));
            $cookies->add(new \yii\web\Cookie(['name' => 'country', 'value' => $country,]));
        }
        return $this->redirect($currentUrl);
    }

    public function actionClearSession()
    {
        var_dump(Yii::$app->request->cookies->get('locality'));
        Yii::$app->session->remove('locality');
        Yii::$app->session->remove('region');
        Yii::$app->session->remove('country');
    }

    /**
     * Главная страница маркета
     * Популярные товары, поставщики
     * @return string
     */
    public function actionIndex()
    {
        $relationSuppliers = [];
        $oWhere = [];
        $cbgWhere = [];

        if (!\Yii::$app->user->isGuest) {
            $currentUser = Yii::$app->user->identity;
            $client = $currentUser->organization;
            if ($client->type_id == Organization::TYPE_RESTAURANT) {
                $result = RelationSuppRest::find()
                    ->select('supp_org_id as id,supp_org_id as supp_org_id')
                    ->where(['rest_org_id' => $client->id, 'invite' => RelationSuppRest::INVITE_ON])
                    ->asArray()
                    ->all();
                foreach ($result as $row) {
                    $relationSuppliers[] = $row['id'];
                }
            }
        }

        if (!empty(Yii::$app->request->cookies->get('locality'))) {
            $supplierRegion = DeliveryRegions::getSuppRegion();

            if (!empty($supplierRegion)) {
                if (!empty($relationSuppliers)) {
                    $supplierRegion = \array_udiff($supplierRegion, $relationSuppliers, function ($a, $b) {
                        return $a - $b;
                    });
                }
                $oWhere = ['in', 'id', $supplierRegion];
                $cbgWhere = ['in', 'supp_org_id', $supplierRegion];
            }
        }
        $topSuppliers = Organization::find()
            ->where([
                'type_id' => Organization::TYPE_SUPPLIER,
                'white_list' => Organization::WHITE_LIST_ON
            ])
            ->andWhere($oWhere)
            ->orderBy(['rating' => SORT_DESC])
            ->limit(6)
            ->all();

        $topSuppliersCount = Organization::find()
            ->where([
                'type_id' => Organization::TYPE_SUPPLIER,
                'white_list' => Organization::WHITE_LIST_ON
            ])
            ->andWhere($oWhere)
            ->count();

        //Популярные товары
        $query = CatalogBaseGoods::find()->select(['catalog_base_goods.*', 'COUNT(o.id) as count'])
            ->innerJoin('order_content', 'order_content.product_id = catalog_base_goods.id')
            ->innerJoin('order o', 'o.id = order_content.order_id')
            ->innerJoin('organization', 'organization.id = o.vendor_id')
            ->where([
                'organization.white_list'         => Organization::WHITE_LIST_ON,
                'catalog_base_goods.market_place' => CatalogBaseGoods::MARKETPLACE_ON,
                'catalog_base_goods.status'       => CatalogBaseGoods::STATUS_ON,
                'catalog_base_goods.deleted'      => CatalogBaseGoods::DELETED_OFF
            ])
            ->andWhere('catalog_base_goods.category_id is not null')
            ->andWhere(['in', 'o.status', [
                Order::STATUS_DONE,
                Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR,
                Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT,
                Order::STATUS_PROCESSING
            ]])
            ->andWhere($cbgWhere)
            ->groupBy(['catalog_base_goods.id'])
            ->orderBy('count DESC');

        $topProductsCount = $query->count();
        $topProducts = $query->limit(6)->all();

        return $this->render('/site/index', compact('topProducts', 'topSuppliers', 'topProductsCount', 'topSuppliersCount'));
    }

    public function actionProduct($id)
    {
        $relationSupplier = [];
        if (\Yii::$app->user->isGuest) {

        } else {
            $currentUser = Yii::$app->user->identity;
            $client = $currentUser->organization;
            if ($client->type_id == Organization::TYPE_RESTAURANT) {
                $relationSupplier = RelationSuppRest::find()
                    ->select('supp_org_id')
                    ->where(['rest_org_id' => $client->id, 'invite' => RelationSuppRest::INVITE_ON])
                    ->asArray()
                    ->all();
            }
        }

        $product = CatalogBaseGoods::find()
            ->where([
                'id' => $id,
                'market_place' => CatalogBaseGoods::MARKETPLACE_ON,
                'status' => CatalogBaseGoods::STATUS_ON,
                'deleted' => CatalogBaseGoods::DELETED_OFF])
            ->andWhere(['not in', 'supp_org_id', $relationSupplier])
            ->one();
        if ($product) {
            return $this->render('/site/product', compact('product'));
        } else {
            throw new HttpException(404, Yii::t('message', 'market.controllers.site.get_out_six', ['ru' => 'Нет здесь ничего такого, проходите, гражданин']));
        }
    }

    public function actionSendService($id)
    {
        return $this->renderAjax('/site/restaurant/_formSendService', compact('id'));
    }

    public function actionSearchProducts($search)
    {
        $search = trim($search, '"');
        $where = [];
        $filterNotIn = [];
        if (!\Yii::$app->user->isGuest) {
            $currentUser = Yii::$app->user->identity;
            $client = $currentUser->organization;
            if ($client->type_id == Organization::TYPE_RESTAURANT) {
                $suppliers = RelationSuppRest::find()
                    ->select('supp_org_id')
                    ->where(['rest_org_id' => $client->id, 'invite' => RelationSuppRest::INVITE_ON])
                    ->all();
                foreach ($suppliers AS $supplier) {
                    $filterNotIn[] = $supplier->supp_org_id;
                }
            }
        }
        if (!empty(Yii::$app->request->cookies->get('locality'))) {
            $regions = DeliveryRegions::getSuppRegion();
            if (!empty($regions) && !empty($filterNotIn)) {
                $r = \array_udiff($regions, $filterNotIn, function ($a, $b) {
                    return $a - $b;
                });
                $where = $r;
            } else {
                $where = $regions;
            }
        }
        $params = [
            'filtered' => [
                'query' => [
                    'match' => [
                        'product_name' => [
                            'query' => $search,
                            'analyzer' => 'ru'
                        ]
                    ]
                ]
            ]
        ];
        $count = \common\models\ES\Product::find()->query($params)
            ->where(['in', 'product_supp_id', $where])
            ->limit(10000)->count();
        if (!empty($count)) {
            $products = \common\models\ES\Product::find()->query($params)
                ->where(['in', 'product_supp_id', $where])
                ->limit(12)->all();
            return $this->render('/site/search-products', compact('count', 'products', 'search'));
        } else {
            throw new HttpException(404, Yii::t('message', 'market.controllers.site.get_out', ['ru' => 'Нет здесь ничего такого, проходите, гражданин']));
        }
    }

    public function actionAjaxEsProductMore($num, $search)
    {
        $where = [];
        $filterNotIn = [];
        if (!\Yii::$app->user->isGuest) {
            $currentUser = Yii::$app->user->identity;
            $client = $currentUser->organization;
            if ($client->type_id == Organization::TYPE_RESTAURANT) {
                $suppliers = RelationSuppRest::find()
                    ->select('supp_org_id')
                    ->where(['rest_org_id' => $client->id, 'invite' => RelationSuppRest::INVITE_ON])
                    ->all();
                foreach ($suppliers AS $supplier) {
                    $filterNotIn[] = $supplier->supp_org_id;
                }
            }
        }
        if (!empty(Yii::$app->request->cookies->get('locality'))) {
            $regions = DeliveryRegions::getSuppRegion();
            if (!empty($regions) && !empty($filterNotIn)) {
                $r = \array_udiff($regions, $filterNotIn, function ($a, $b) {
                    return $a - $b;
                });
                $where = $r;
            } else {
                $where = $regions;
            }
        }
        $params = [
            'filtered' => [
                'query' => [
                    'match' => [
                        'product_name' => [
                            'query' => $search,
                            'analyzer' => "ru",
                        ]
                    ]
                ]
            ]
        ];
        $count = \common\models\ES\Product::find()->query($params)
            ->where(['in', 'product_supp_id', $where])
            ->offset($num)
            ->limit(6)
            ->count();

        if ($count > 0) {
            $pr = \common\models\ES\Product::find()->query($params)
                ->where(['in', 'product_supp_id', $where])
                ->orderBy(['product_rating' => SORT_DESC])
                ->offset($num)
                ->limit(6)
                ->all();
            return $this->renderPartial('/site/main/_ajaxEsProductMore', compact('pr'));
        }
    }

    public function actionSearchSuppliers($search)
    {
        $search = trim($search, '"');
        $where = [];
        $filterNotIn = [];
        if (!\Yii::$app->user->isGuest) {
            $currentUser = Yii::$app->user->identity;
            $client = $currentUser->organization;
            if ($client->type_id == Organization::TYPE_RESTAURANT) {
                $suppliers = RelationSuppRest::find()
                    ->select('supp_org_id')
                    ->where(['rest_org_id' => $client->id, 'invite' => RelationSuppRest::INVITE_ON])
                    ->all();
                foreach ($suppliers AS $supplier) {
                    $filterNotIn[] = $supplier->supp_org_id;
                }
            }
        }
        if (!empty(Yii::$app->request->cookies->get('locality'))) {
            $regions = DeliveryRegions::getSuppRegion();
            if (!empty($regions) && !empty($filterNotIn)) {
                $r = \array_udiff($regions, $filterNotIn, function ($a, $b) {
                    return $a - $b;
                });
                $where = $r;
            } else {
                $where = $regions;
            }
        }
        $params = [
            'filtered' => [
                'query' => [
                    'match' => [
                        'supplier_name' => [
                            'query' => $search,
                            'analyzer' => 'ru',
                        ]
                    ]
                ]
            ]
        ];

        $count = \common\models\ES\Supplier::find()->query($params)
            ->limit(10000)->count();
        if (!empty($count)) {
            $sp = \common\models\ES\Supplier::find()->query($params)->orderBy(['supplier_rating' => SORT_DESC])
                ->andWhere(['in', 'supplier_id', $where])
                ->limit(12)->all();
            return $this->render('/site/search-suppliers', compact('count', 'sp', 'search'));
        } else {
            throw new HttpException(404, Yii::t('message', 'market.controllers.site.get_out', ['ru' => 'Нет здесь ничего такого, проходите, гражданин']));
        }
    }

    public function actionAjaxEsSupplierMore($num, $search)
    {
        $where = [];
        $filterNotIn = [];
        if (!\Yii::$app->user->isGuest) {
            $currentUser = Yii::$app->user->identity;
            $client = $currentUser->organization;

            if ($client->type_id == Organization::TYPE_RESTAURANT) {
                $suppliers = RelationSuppRest::find()
                    ->select('supp_org_id')
                    ->where(['rest_org_id' => $client->id, 'invite' => RelationSuppRest::INVITE_ON])
                    ->all();
                foreach ($suppliers AS $supplier) {
                    $filterNotIn[] = $supplier->supp_org_id;
                }
            }
        }
        if (!empty(Yii::$app->request->cookies->get('locality'))) {
            $regions = DeliveryRegions::getSuppRegion();
            if (!empty($regions) && !empty($filterNotIn)) {
                $r = \array_udiff($regions, $filterNotIn, function ($a, $b) {
                    return $a - $b;
                });
                $where = $r;
            } else {
                $where = $regions;
            }
        }

        $params = [
            'query' => [
                'bool' => [
                    'must' => [
                        'match' => [
                            'supplier_name' => [
                                'query' => $search,
                                'analyzer' => 'ru',
                            ]
                        ]
                    ],
                    'filter' => [
                        'terms' => [
                            'supplier_id' => $where
                        ]
                    ]
                ]
            ]
        ];
        $count = \common\models\ES\Supplier::find()->query($params)
            ->offset($num)
            ->limit(12)
            ->count();

        if ($count > 0) {
            $sp = \common\models\ES\Supplier::find()->query($params)
                ->orderBy(['supplier_rating' => SORT_DESC])
                ->offset($num)
                ->limit(12)
                ->all();
            return $this->renderPartial('/site/main/_ajaxEsSupplierMore', compact('sp'));
        }
    }

    public function actionSupplierProducts($id)
    {
        $relationSupplier = [];
        if (\Yii::$app->user->isGuest) {

        } else {
            $currentUser = Yii::$app->user->identity;
            $client = $currentUser->organization;
            if ($client->type_id == Organization::TYPE_RESTAURANT) {
                $relationSupplier = RelationSuppRest::find()
                    ->select('supp_org_id as id,supp_org_id as supp_org_id')
                    ->where(['rest_org_id' => $client->id, 'invite' => RelationSuppRest::INVITE_ON])
                    ->asArray()
                    ->all();
            }
        }
        $productsCount = CatalogBaseGoods::find()
            ->joinWith('vendor')
            ->where([
                'supp_org_id' => $id,
                'organization.white_list' => Organization::WHITE_LIST_ON,
                'market_place' => CatalogBaseGoods::MARKETPLACE_ON,
                'status' => CatalogBaseGoods::STATUS_ON,
                'deleted' => CatalogBaseGoods::DELETED_OFF])
            ->andWhere('category_id is not null')
            ->andWhere(['not in', 'supp_org_id', $relationSupplier])
            ->count();
        $cbgTable = CatalogBaseGoods::tableName();
        $products = CatalogBaseGoods::find()
            ->joinWith('vendor')
            ->where([
                'supp_org_id' => $id,
                'organization.white_list' => Organization::WHITE_LIST_ON,
                'market_place' => CatalogBaseGoods::MARKETPLACE_ON,
                'status' => CatalogBaseGoods::STATUS_ON,
                'deleted' => CatalogBaseGoods::DELETED_OFF])
            ->andWhere('category_id is not null')
            ->andWhere(['not in', 'supp_org_id', $relationSupplier])
            ->orderBy([$cbgTable . '.rating' => SORT_DESC])
            ->limit(12)
            ->all();
        $vendor = \common\models\Organization::find()->where(['id' => $id])->one();

        if ($products) {
            return $this->render('/site/supplier-products', compact('products', 'id', 'vendor', 'productsCount'));
        } else {
            $breadcrumbs = [
                'options' => [
                    'class' => 'breadcrumb',
                ],
                'homeLink' => false,
                'links' => [
                    [
                        'label' => Yii::t('message', 'market.controllers.site.all_vendors', ['ru' => 'Все поставщики']),
                        'url' => ['/site/suppliers'],
                    ],
                    [
                        'label' => $vendor->name,
                        'url' => ['/site/supplier', 'id' => $vendor->id],
                    ],
                    Yii::t('message', 'market.controllers.site.catalog', ['ru' => 'Каталог']),
                ],
            ];
            $title = Yii::t('message', 'market.controllers.site.products', ['ru' => 'MixCart Продукты поставщика']);
            $message = Yii::t('message', 'market.controllers.site.vendor_empty', ['ru' => 'Поставщик еще не добавил свои товары на торговую площадку MixCart']);
            return $this->render('/site/empty', compact('breadcrumbs', 'title', 'message'));
        }
    }

    public function actionSupplier($id)
    {
        $vendor = Organization::find()
            ->where([
                'organization.id' => $id,
                'type_id' => Organization::TYPE_SUPPLIER,
                'white_list' => Organization::WHITE_LIST_ON
            ])
            ->one();

        if (empty($vendor)) {
            throw new HttpException(404, Yii::t('message', 'market.controllers.site.get_out_two', ['ru' => 'Нет здесь ничего такого, проходите, гражданин']));
        }

        if (\Yii::$app->user->isGuest) {
            $relationSupplier = false;
            $addwhere = [];
        } else {
            $currentUser = Yii::$app->user->identity;
            $client = $currentUser->organization;
            $addwhere = [];
            if ($client->type_id == Organization::TYPE_RESTAURANT) {
                $relationSupplier = RelationSuppRest::find()
                    ->where([
                        'rest_org_id' => $client->id,
                        'supp_org_id' => $vendor->id,
                        'status' => RelationSuppRest::CATALOG_STATUS_ON])
                    ->exists();
            }
            if ($client->type_id == Organization::TYPE_SUPPLIER) {
                $addwhere = [];
                $relationSupplier = false;
            }
        }

        $currency = '';
        $baseCatalog = Catalog::findOne(['supp_org_id' => $id, 'type' => Catalog::BASE_CATALOG]);
        if (!empty($baseCatalog)) {
            $currency = $baseCatalog->currency->symbol;
        }

        if ($vendor && !$relationSupplier) {
            return $this->render('/site/supplier', compact('vendor', 'currency'));
        } else {
            throw new HttpException(404, Yii::t('message', 'market.controllers.site.get_out_three', ['ru' => 'Нет здесь ничего такого, проходите, гражданин']));
        }
    }

    public function actionRestaurant($id)
    {
        $restaurant = Organization::findOne(['id' => $id, 'type_id' => Organization::TYPE_RESTAURANT]);

        if ($restaurant) {
            return $this->render('/site/restaurant', compact('restaurant'));
        } else {
            throw new HttpException(404, Yii::t('message', 'market.controllers.site.get_out_four', ['ru' => 'Нет здесь ничего такого, проходите, гражданин']));
        }
    }

    /**
     * Подгрузить еще популярных товаров
     * @param $num
     * @return string
     * @throws HttpException
     */
    public function actionAjaxProductMore($num)
    {
        if (!Yii::$app->request->isAjax) {
            throw new HttpException(404, 'Нет здесь ничего такого, проходите, гражданин');
        }

        $relationSuppliers = [];
        $cbgWhere = [];

        if (!\Yii::$app->user->isGuest) {
            $currentUser = Yii::$app->user->identity;
            if ($currentUser->organization->type_id == Organization::TYPE_RESTAURANT) {
                $result = RelationSuppRest::find()
                    ->select('supp_org_id as id,supp_org_id as supp_org_id')
                    ->where(['rest_org_id' => $currentUser->organization->id, 'invite' => RelationSuppRest::INVITE_ON])
                    ->asArray()
                    ->all();
                foreach ($result as $row) {
                    $relationSuppliers[] = $row['id'];
                }
            }
        }

        if (!empty(Yii::$app->request->cookies->get('locality'))) {
            $supplierRegion = DeliveryRegions::getSuppRegion();

            if (!empty($supplierRegion)) {
                if (!empty($relationSuppliers)) {
                    $supplierRegion = \array_udiff($supplierRegion, $relationSuppliers, function ($a, $b) {
                        return $a - $b;
                    });
                }
                $cbgWhere = ['in', 'supp_org_id', $supplierRegion];
            }
        }

        $models = CatalogBaseGoods::find()->select(['catalog_base_goods.*', 'COUNT(o.id) as count'])
            ->innerJoin('order_content', 'order_content.product_id = catalog_base_goods.id')
            ->innerJoin('order o', 'o.id = order_content.order_id')
            ->innerJoin('organization', 'organization.id = o.vendor_id')
            ->where([
                'organization.white_list'         => Organization::WHITE_LIST_ON,
                'catalog_base_goods.market_place' => CatalogBaseGoods::MARKETPLACE_ON,
                'catalog_base_goods.status'       => CatalogBaseGoods::STATUS_ON,
                'catalog_base_goods.deleted'      => CatalogBaseGoods::DELETED_OFF
            ])
            ->andWhere('catalog_base_goods.category_id is not null')
            ->andWhere(['in', 'o.status', [
                Order::STATUS_DONE,
                Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR,
                Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT,
                Order::STATUS_PROCESSING
            ]])
            ->andWhere($cbgWhere)
            ->groupBy(['catalog_base_goods.id'])
            ->orderBy('count DESC')->offset($num)->limit(6);

        if ($models->count() > 0) {
            $pr = $models->all();
            return $this->renderPartial('/site/main/_ajaxProductMore', compact('pr'));
        }
    }

    public function actionAjaxSuppProductMore($num, $supp_org_id)
    {
        $session = Yii::$app->session;
        $relationSuppliers = [];
        $supplierRegion = [];
        $oWhere = [];
        $cbgWhere = [];

        if (!\Yii::$app->user->isGuest) {
            $currentUser = Yii::$app->user->identity;
            $client = $currentUser->organization;
            if ($client->type_id == Organization::TYPE_RESTAURANT) {
                $result = RelationSuppRest::find()
                    ->select('supp_org_id as id,supp_org_id as supp_org_id')
                    ->where(['rest_org_id' => $client->id, 'invite' => RelationSuppRest::INVITE_ON])
                    ->asArray()
                    ->all();
                foreach ($result as $row) {
                    $relationSuppliers[] = $row['id'];
                }
            }
        }

        if (!empty(Yii::$app->request->cookies->get('locality'))) {
            $supplierRegion = DeliveryRegions::getSuppRegion();

            if (!empty($supplierRegion)) {
                if (!empty($relationSuppliers)) {
                    $supplierRegion = \array_udiff($supplierRegion, $relationSuppliers, function ($a, $b) {
                        return $a - $b;
                    });
                }
                $cbgWhere = ['in', 'supp_org_id', $supplierRegion];
            }
        }
        $cbgTable = CatalogBaseGoods::tableName();
        $count = CatalogBaseGoods::find()
            ->joinWith('vendor')
            ->where([
                'supp_org_id' => $supp_org_id,
                'organization.white_list' => Organization::WHITE_LIST_ON,
                'market_place' => CatalogBaseGoods::MARKETPLACE_ON,
                'status' => CatalogBaseGoods::STATUS_ON,
                'deleted' => CatalogBaseGoods::DELETED_OFF])
            ->andWhere('category_id is not null')
            ->andWhere($cbgWhere)
            ->offset($num)
            ->limit(6)
            ->count();
        if ($count > 0) {
            $pr = CatalogBaseGoods::find()
                ->joinWith('vendor')
                ->where([
                    'supp_org_id' => $supp_org_id,
                    'organization.white_list' => Organization::WHITE_LIST_ON,
                    'market_place' => CatalogBaseGoods::MARKETPLACE_ON,
                    'status' => CatalogBaseGoods::STATUS_ON,
                    'deleted' => CatalogBaseGoods::DELETED_OFF])
                ->andWhere('category_id is not null')
                ->andWhere($cbgWhere)
                ->orderBy([$cbgTable . '.rating' => SORT_DESC])
                ->offset($num)
                ->limit(6)
                ->all();
            return $this->renderPartial('/site/main/_ajaxProductMore', compact('pr'));
        }
    }

    public function actionRestaurants()
    {
        $locationWhere = [];
        if (Yii::$app->request->cookies->get('locality')) {
            $locationWhere = ['country' => Yii::$app->request->cookies->get('country'), 'locality' => Yii::$app->request->cookies->get('locality')];
        }
        $restaurants = Organization::find()
            ->where([
                'type_id' => Organization::TYPE_RESTAURANT,
                'white_list' => Organization::WHITE_LIST_ON
            ])
            ->andWhere($locationWhere)
            //->orderBy(['rating'=>SORT_DESC])
            ->limit(12)
            ->all();
        $restaurantsCount = Organization::find()
            ->where([
                'type_id' => Organization::TYPE_RESTAURANT,
                'white_list' => Organization::WHITE_LIST_ON
            ])
            ->andWhere($locationWhere)
            ->limit(12)
            ->count();

        return $this->render('restaurants', compact('restaurants', 'restaurantsCount'));
    }

    public function actionAjaxRestaurantsMore($num)
    {
        $locationWhere = [];
        if (Yii::$app->request->cookies->get('locality')) {
            $locationWhere = ['country' => Yii::$app->request->cookies->get('country'), 'locality' => Yii::$app->request->cookies->get('locality')];
        }
        $count = Organization::find()
            ->where([
                'type_id' => Organization::TYPE_RESTAURANT,
                'white_list' => Organization::WHITE_LIST_ON
            ])
            ->andWhere($locationWhere)
            ->limit(6)->offset($num)
            ->count();
        if ($count > 0) {
            $restaurants = Organization::find()
                ->where([
                    'type_id' => Organization::TYPE_RESTAURANT,
                    'white_list' => Organization::WHITE_LIST_ON
                ])
                ->andWhere($locationWhere)
                ->limit(6)->offset($num)
                ->all();
            return $this->renderPartial('/site/main/_ajaxRestaurantMore', compact('restaurants'));
        }
    }

    public function actionAjaxSupplierMore($num)
    {
        $session = Yii::$app->session;
        $relationSuppliers = [];
        $supplierRegion = [];
        $oWhere = [];
        $cbgWhere = [];

        if (!\Yii::$app->user->isGuest) {
            $currentUser = Yii::$app->user->identity;
            $client = $currentUser->organization;
            if ($client->type_id == Organization::TYPE_RESTAURANT) {
                $result = RelationSuppRest::find()
                    ->select('supp_org_id as id,supp_org_id as supp_org_id')
                    ->where(['rest_org_id' => $client->id, 'invite' => RelationSuppRest::INVITE_ON])
                    ->asArray()
                    ->all();
                foreach ($result as $row) {
                    $relationSuppliers[] = $row['id'];
                }
            }
        }

        if (!empty(Yii::$app->request->cookies->get('locality'))) {
            $supplierRegion = DeliveryRegions::getSuppRegion();

            if (!empty($supplierRegion)) {
                if (!empty($relationSuppliers)) {
                    $supplierRegion = \array_udiff($supplierRegion, $relationSuppliers, function ($a, $b) {
                        return $a - $b;
                    });
                }
                $oWhere = ['in', 'id', $supplierRegion];
            }
        }

        $suppliersCount = Organization::find()
            ->where([
                'type_id' => Organization::TYPE_SUPPLIER,
                'white_list' => Organization::WHITE_LIST_ON
            ])
            ->andWhere($oWhere)
            ->orderBy(['rating' => SORT_DESC])
            ->limit(6)->offset($num)
            ->count();
        if ($suppliersCount > 0) {
            $suppliers = Organization::find()
                ->where([
                    'type_id' => Organization::TYPE_SUPPLIER,
                    'white_list' => Organization::WHITE_LIST_ON
                ])
                ->andWhere($oWhere)
                ->orderBy(['rating' => SORT_DESC])
                ->limit(6)->offset($num)
                ->all();
            return $this->renderPartial('/site/main/_ajaxSupplierMore', compact('suppliers'));
        }
    }

    /**
     * Просмотр категории
     * @param $slug
     * @return string
     * @throws HttpException
     */
    public function actionCategory($slug)
    {
        $category = \common\models\MpCategory::find()->where(['slug' => $slug])->one();

        if (empty($category)) {
            throw new HttpException(404, Yii::t('message', 'market.controllers.site.get_out_five', ['ru' => 'Нет здесь ничего такого, проходите, гражданин']));
        }

        if (empty($category->parent)) {
            $id = \yii\helpers\ArrayHelper::getColumn(\common\models\MpCategory::find()->where(['parent' => $category->id])->select('id')->asArray()->all(), 'id');
        } else {
            $id = $category->id;
        }
        $relationSuppliers = [];
        $cbgWhere = [];
        $filter = "rating-up";
        $filterWhere = "rating desc";

        switch (Yii::$app->request->get('filter')) {
            case 'price-up':
                $filter = "price-down";
                $filterWhere = "price ASC";
                break;
            case 'price-down':
                $filter = "price-up";
                $filterWhere = "price DESC";
                break;
            case 'rating-up':
                $filter = "rating-down";
                $filterWhere = "rating ASC";
                break;
            case 'rating-down':
                $filter = "rating-up";
                $filterWhere = "rating DESC";
                break;
        }

        //Записываем в сессию чтобы сортировка учитывалась по аяксу
        Yii::$app->session->set('cat_filter_where', $filterWhere);

        if (!\Yii::$app->user->isGuest) {
            $currentUser = Yii::$app->user->identity;
            $client = $currentUser->organization;
            if ($client->type_id == Organization::TYPE_RESTAURANT) {
                $result = RelationSuppRest::find()
                    ->select('supp_org_id as id')
                    ->where(['rest_org_id' => $client->id, 'invite' => RelationSuppRest::INVITE_ON])
                    ->asArray()
                    ->all();
                foreach ($result as $row) {
                    $relationSuppliers[] = $row['id'];
                }
            }
        }

        if (!empty(Yii::$app->request->cookies->get('locality'))) {

            $supplierRegion = DeliveryRegions::getSuppRegion();

            if (!empty($supplierRegion)) {
                if (!empty($relationSuppliers)) {
                    $supplierRegion = \array_udiff($supplierRegion, $relationSuppliers, function ($a, $b) {
                        return $a - $b;
                    });
                }
                $cbgWhere = ['in', 'supp_org_id', $supplierRegion];
            }
        }

        $models = CatalogBaseGoods::find()
            ->joinWith('vendor')
            ->where([
                'category_id' => $id,
                'organization.white_list' => Organization::WHITE_LIST_ON,
                'market_place' => CatalogBaseGoods::MARKETPLACE_ON,
                'status' => CatalogBaseGoods::STATUS_ON,
                'deleted' => CatalogBaseGoods::DELETED_OFF])
            ->andWhere($cbgWhere)
            ->orderBy($filterWhere)
            ->limit(12);

        $products = $models->all();
        $count = $models->count();

        if ($count > 0) {
            return $this->render('category', compact('products', 'count', 'category', 'filter'));
        } else {
            $breadcrumbs = \yii\widgets\Breadcrumbs::widget([
                'options' => [
                    'class' => 'breadcrumb',
                ],
                'homeLink' => false,
                'links' => empty($category->parent) ? [
                    \common\models\MpCategory::getCategory($category->id),
                ] : [
                    ['label' => \common\models\MpCategory::getCategory($category->parent), 'url' => \yii\helpers\Url::to(['site/category', 'slug' => $category->parentCategory->slug])],
                    \common\models\MpCategory::getCategory($category->id),
                ],
            ]);
            $message = Yii::t('message', 'market.controllers.site.no_goods', ['ru' => 'В данной категории товаров нет']);
            return $this->render('not-found', compact('breadcrumbs', 'message', 'products', 'category'));
        }
    }

    /**
     * Список товаров в категории по 6 штук
     * @param $num сколько пропускаем записей
     * @param $category категория
     * @return string
     */
    public function actionAjaxProductCatLoader($num, $category)
    {
        if (Yii::$app->request->isAjax) {

            $category = \common\models\MpCategory::findOne(['id' => $category]);
            if (empty($category->parent)) {
                $categoryIds = \yii\helpers\ArrayHelper::getColumn(\common\models\MpCategory::find()->where(['parent' => $category])->select('id')->asArray()->all(), 'id');
            } else {
                $categoryIds = $category->id;
            }

            $relationSuppliers = [];
            $cbgWhere = [];

            if (!\Yii::$app->user->isGuest) {
                $currentUser = Yii::$app->user->identity;
                if ($currentUser->organization->type_id == Organization::TYPE_RESTAURANT) {
                    $result = RelationSuppRest::find()
                        ->select('supp_org_id as id,supp_org_id as supp_org_id')
                        ->where([
                            'rest_org_id' => $currentUser->organization->id,
                            'invite' => RelationSuppRest::INVITE_ON
                        ])->asArray()
                        ->all();
                    foreach ($result as $row) {
                        $relationSuppliers[] = $row['id'];
                    }
                }
            }

            if (!empty(Yii::$app->request->cookies->get('locality'))) {
                $supplierRegion = DeliveryRegions::getSuppRegion();

                if (!empty($supplierRegion)) {
                    if (!empty($relationSuppliers)) {
                        $supplierRegion = \array_udiff($supplierRegion, $relationSuppliers, function ($a, $b) {
                            return $a - $b;
                        });
                    }
                    $cbgWhere = ['in', 'supp_org_id', $supplierRegion];
                }
            }

            //Берем сортировку сохраненную в сессии
            $filterWhere = Yii::$app->session->get('cat_filter_where', 'rating desc');

            $query = CatalogBaseGoods::find()
                ->joinWith('vendor')
                ->where([
                    'category_id' => $categoryIds,
                    'organization.white_list' => Organization::WHITE_LIST_ON,
                    'market_place' => CatalogBaseGoods::MARKETPLACE_ON,
                    'status' => CatalogBaseGoods::STATUS_ON,
                    'deleted' => CatalogBaseGoods::DELETED_OFF
                ])
                ->andWhere($cbgWhere)
                ->orderBy($filterWhere)
                ->offset($num)
                ->limit(6);

            if ($query->count() > 0) {
                return $this->renderPartial('/site/main/_ajaxProductMore', ['pr' => $query->all()]);
            }
        }
    }

    public function actionSuppliers()
    {
        $session = Yii::$app->session;
        $relationSuppliers = [];
        $supplierRegion = [];
        $oWhere = [];
        $cbgWhere = [];

        if (!\Yii::$app->user->isGuest) {
            $currentUser = Yii::$app->user->identity;
            $client = $currentUser->organization;
            if ($client->type_id == Organization::TYPE_RESTAURANT) {
                $result = RelationSuppRest::find()
                    ->select('supp_org_id as id,supp_org_id as supp_org_id')
                    ->where(['rest_org_id' => $client->id, 'invite' => RelationSuppRest::INVITE_ON])
                    ->asArray()
                    ->all();
                foreach ($result as $row) {
                    $relationSuppliers[] = $row['id'];
                }
            }
        }

        if (!empty(Yii::$app->request->cookies->get('locality'))) {
            $supplierRegion = DeliveryRegions::getSuppRegion();

            if (!empty($supplierRegion)) {
                if (!empty($relationSuppliers)) {
                    $supplierRegion = \array_udiff($supplierRegion, $relationSuppliers, function ($a, $b) {
                        return $a - $b;
                    });
                }
                $oWhere = ['in', 'id', $supplierRegion];
            }
        }
        $suppliers = Organization::find()
            ->where([
                'type_id' => Organization::TYPE_SUPPLIER,
                'white_list' => Organization::WHITE_LIST_ON
            ])
            ->andWhere($oWhere)
            ->orderBy(['rating' => SORT_DESC]);

        $suppliersCount = $suppliers->count();
        $suppliers = $suppliers->limit(12)->all();

        return $this->render('suppliers', compact('suppliers', 'suppliersCount'));
    }

    public function actionView()
    {
        $where = [];
        $filterNotIn = [];
        if (!\Yii::$app->user->isGuest) {
            $currentUser = Yii::$app->user->identity;
            $client = $currentUser->organization;
            if ($client->type_id == Organization::TYPE_RESTAURANT) {
                $suppliers = RelationSuppRest::find()
                    ->select('supp_org_id')
                    ->where(['rest_org_id' => $client->id, 'status' => RelationSuppRest::INVITE_ON])
                    ->all();
                foreach ($suppliers AS $supplier) {
                    $filterNotIn[] = $supplier->supp_org_id;
                }
            }
        }
        if (!empty(Yii::$app->request->cookies->get('locality'))) {
            $regions = DeliveryRegions::getSuppRegion();

            if (!empty($regions) && !empty($filterNotIn)) {
                $r = \array_udiff($regions, $filterNotIn, function ($a, $b) {
                    return $a - $b;
                });
                $where = $r;
            } else {
                $where = $regions;
            }
        }
        $search = "";
        $search_categorys_count = "";
        $search_products_count = "";
        $search_suppliers_count = "";
        $search_categorys = "";
        $search_products = "";
        $search_suppliers = "";
        if (isset($_POST['searchText']) && strlen($_POST['searchText']) > 2) {
            $search = trim($_POST['searchText'], '"');
            $params_categorys = [
                'filtered' => [
                    'query' => [
                        'match' => [
                            'category_name' => [
                                'query' => $search,
                                'analyzer' => "ru",
                            ]
                        ]
                    ]
                ]
            ];
            $params_products = [
                'filtered' => [
                    'query' => [
                        'match' => [
                            'product_name' => [
                                'query' => $search,
                                'analyzer' => 'ru',
                            ]
                        ]
                    ]
                ]
            ];
            $params_suppliers = [
                'filtered' => [
                    'query' => [
                        'match' => [
                            'supplier_name' => [
                                'query' => $search,
                                'analyzer' => 'ru',
                            ]
                        ]
                    ]
                ]
            ];

            $search_categorys_count = \common\models\ES\Category::find()->query($params_categorys)
                ->limit(10000)->count();
            $search_products_count = \common\models\ES\Product::find()->query($params_products)
                ->andWhere(['in', 'product_supp_id', $where])
                ->limit(10000)->count();
            $search_suppliers_count = \common\models\ES\Supplier::find()->query($params_suppliers)
                ->andWhere(['in', 'supplier_id', $where])
                ->limit(10000)->count();
            $search_categorys = \common\models\ES\Category::find()->query($params_categorys)
                ->limit(200)->asArray()->all();
            $search_products = \common\models\ES\Product::find()->query($params_products)
                ->andWhere(['in', 'product_supp_id', $where])
                ->limit(4)->asArray()->all();
            $search_suppliers = \common\models\ES\Supplier::find()->query($params_suppliers)
                ->andWhere(['in', 'supplier_id', $where])
                ->limit(4)->asArray()->all();
        }

        return $this->renderAjax('main/_search_form', compact('search_categorys_count', 'search_products_count', 'search_suppliers_count', 'search_categorys', 'search_products', 'search_suppliers', 'search'));
    }

    public function actionAjaxAddToCart()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (Yii::$app->user->isGuest) {
            return $this->successNotify(Yii::t('message', 'market.controllers.site.function', ['ru' => "Функция доступна зарегистрированным ресторанам!"]));
        }

        $currentUser = Yii::$app->user->identity;
        $client = $currentUser->organization;

        if ($client->isEmpty()) {
            return $this->successNotify(Yii::t('message', 'market.controllers.site.please_set_info', ['ru' => "Пожалуйста, сначала заполните информацию о себе на mixcart.ru"]));
        }

        if ($client->type_id !== Organization::TYPE_RESTAURANT) {
            return $this->successNotify(Yii::t('message', 'market.controllers.site.you_vendor', ['ru' => "Опомнитесь, вы и есть поставщик!"]));
        }

        $post = Yii::$app->request->post();
        $relation = null;

        if ($post && $post['product_id']) {
            $product = CatalogBaseGoods::findOne(['id' => $post['product_id']]);
            if (empty($product)) {
                return $this->successNotify(Yii::t('message', 'market.controllers.site.no_product', ['ru' => "Продукт не найден!"]));
            }
            $relation = RelationSuppRest::findOne(['supp_org_id' => $product->vendor->id, 'rest_org_id' => $client->id]);
            if ($relation && ($relation->invite === RelationSuppRest::INVITE_ON)) {
                return $this->successNotify(Yii::t('message', 'market.controllers.site.already_have', ['ru' => "Вы уже имеете каталог этого поставщика!"]));
            }
        } else {
            return $this->successNotify(Yii::t('error', 'market.controllers.site.undefined_error', ['ru' => "Неизвестная ошибка!"]));
        }

        $quantity = ($product->units) ? $product->units : 1;

        if ($quantity <= 0) {
            return false;
        }

        $products = ['product_id' => $post['product_id'], 'quantity' => $quantity];

        try {
            (new CartWebApi())->add($products);
        } catch (\Exception $e) {
            return false;
        }

        $cartCount = $client->getCartCount();
        if (!$relation) {
            $client->inviteVendor($product->vendor, RelationSuppRest::INVITE_OFF, RelationSuppRest::CATALOG_STATUS_OFF, true);
            // $this->sendInvite($client,$product->vendor);
        }
        $this->sendCartChange($client, $cartCount);

        return $this->successNotify(Yii::t('message', 'market.controllers.site.product_added', ['ru' => "Продукт добавлен в корзину!"]));
    }

    public function actionAjaxInviteVendor()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (Yii::$app->user->isGuest) {
            return $this->successNotify(Yii::t('message', 'market.controllers.site.function_two', ['ru' => "Функция доступна зарегистрированным ресторанам!"]));
        }

        $currentUser = Yii::$app->user->identity;
        $client = $currentUser->organization;

        if ($client->isEmpty()) {
            return $this->successNotify(Yii::t('message', 'market.controllers.site.please_set_info', ['ru' => "Пожалуйста, сначала заполните информацию о себе на mixcart.ru"]));
        }

        if ($client->type_id !== Organization::TYPE_RESTAURANT) {
            return $this->successNotify(Yii::t('message', 'market.controllers.site.you_vendor_two', ['ru' => "Опомнитесь, вы и есть поставщик!"]));
        }

        $post = Yii::$app->request->post();

        if ($post && $post['vendor_id']) {
            $vendor = Organization::findOne(['id' => $post['vendor_id'], 'type_id' => Organization::TYPE_SUPPLIER]);
            if (empty($vendor)) {
                return $this->successNotify(Yii::t('message', 'market.controllers.site.no_such_vendor', ['ru' => "Поставщик не найден!"]));
            }
            $relation = RelationSuppRest::findOne(['supp_org_id' => $vendor->id, 'rest_org_id' => $client->id]);
            if ($relation) {
                return $this->successNotify(Yii::t('message', 'market.controllers.site.vendor_request', ['ru' => "Запрос поставщику уже направлен!"]));
            }
        } else {
            return $this->successNotify(Yii::t('error', 'market.controllers.site.undefined_error_two', ['ru' => "Неизвестная ошибка!"]));
        }

        $client->inviteVendor($vendor, RelationSuppRest::INVITE_OFF, RelationSuppRest::CATALOG_STATUS_OFF, true);
        $this->sendInvite($client, $vendor);
        return $this->successNotify(Yii::t('message', 'market.controllers.site.sent', ['ru' => "Запрос поставщику отправлен!"]));
    }

    public function actionAjaxCompleteRegistration()
    {
        $user = Yii::$app->user->identity;
        $profile = $user->profile;
        $profile->scenario = "complete";
        $organization = $user->organization;
        $organization->scenario = "complete";

        $post = Yii::$app->request->post();
        if (Yii::$app->request->isAjax && empty($organization->locality) && $profile->load($post) && $organization->load($post)) {
            if ($profile->validate() && $organization->validate()) {
                $profile->save();
                $organization->save();
                $organization->refresh();
            }
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        return \yii\widgets\ActiveForm::validate($profile, $organization);
    }

    private function sendInvite($client, $vendor)
    {
        foreach ($vendor->users as $recipient) {
            if (!empty($recipient->profile->phone)) {
                $text = Yii::$app->sms->prepareText('sms.add_market', [
                    'client_name' => $client->name
                ]);
                Yii::$app->sms->send($text, $recipient->profile->phone);
            }
        }
    }

    private function sendCartChange($client, $cartCount)
    {
        $clientUsers = $client->users;

        foreach ($clientUsers as $user) {
            $channel = 'user' . $user->id;
            Yii::$app->redis->executeCommand('PUBLISH', [
                'channel' => 'chat',
                'message' => Json::encode(['body' => $cartCount, 'channel' => $channel, 'isSystem' => 2])
            ]);
        }

        return true;
    }

    private function successNotify($title)
    {
        return [
            'success' => true,
            'growl' => [
                'options' => [
                ],
                'settings' => [
                    'element' => 'body',
                    'type' => $title,
                    'allow_dismiss' => true,
                    'placement' => [
                        'from' => 'top',
                        'align' => 'center',
                    ],
                    'delay' => 1500,
                    'animate' => [
                        'enter' => 'animated fadeInDown',
                        'exit' => 'animated fadeOutUp',
                    ],
                    'offset' => 75,
                    'template' => '<div data-notify="container" class="modal-dialog" style="width: 340px;">'
                        . '<div class="modal-content">'
                        . '<div class="modal-header">'
                        . '<h4 class="modal-title">{0}</h4></div>'
                        . '<div class="modal-body form-inline" style="text-align: center; font-size: 36px;"> '
                        . '<span class="glyphicon glyphicon-thumbs-up"></span>'
                        . '</div></div></div>',
                ]
            ]
        ];
    }

}
