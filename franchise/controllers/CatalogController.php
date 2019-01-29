<?php

namespace franchise\controllers;

use common\models\Catalog;
use common\models\CatalogGoods;
use common\models\RelationSuppRest;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use yii\filters\AccessControl;
use common\components\AccessRule;
use common\models\Role;
use common\models\User;
use common\models\Organization;
use common\models\CatalogBaseGoods;
use yii\helpers\Json;
use yii\web\HttpException;
use yii\web\Response;

/**
 * Description of AppController
 *
 * @author sharaf
 */
class CatalogController extends DefaultController
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class'      => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules'      => [
                    [
                        'actions' => [
                            'ajax-delete-product',
                            'ajax-create-product-market-place',
                            'ajax-update-product-market-place',
                            'get-sub-cat',
                            'index',
                            'change-catalog-prop',
                            'change-catalog-status',
                            'change-set-catalog',
                            'my-catalog-del-catalog',
                            'step-1',
                            'step-1-clone',
                            'step-1-update',
                            'step-2',
                            'step-2-add-product',
                            'step-3',
                            'step-3-copy',
                            'step-3-update-product',
                            'step-4',
                            'base-catalog',
                        ],
                        'allow'   => true,
                        // Allow suppliers managers
                        'roles'   => [
                            Role::ROLE_FRANCHISEE_OWNER,
                            Role::ROLE_FRANCHISEE_OPERATOR,
                            Role::ROLE_FRANCHISEE_ACCOUNTANT,
                            Role::ROLE_FRANCHISEE_MANAGER,
                            Role::ROLE_FRANCHISEE_LEADER,
                            Role::ROLE_ADMIN,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function actionBaseCatalog($vendor_id, $cat_id = null)
    {
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $currentUser->organization_id = $vendor_id;

        $searchString = "";
        $baseCatalog = ($cat_id) ? Catalog::findOne(['supp_org_id' => $vendor_id, 'id' => $cat_id]) : Catalog::findOne(['supp_org_id' => $vendor_id, 'type' => Catalog::BASE_CATALOG]);
        $currentCatalog = $baseCatalog;
        if (!empty(trim(\Yii::$app->request->get('searchString')))) {
            $searchString = "%" . trim(\Yii::$app->request->get('searchString')) . "%";
            if (!$cat_id) {
                $query = (new Query())
                    ->select([
                        "id",
                        "article",
                        "product",
                        "units",
                        "category_id",
                        "price",
                        "ed",
                        "note",
                        "status",
                        "market_place"
                    ])
                    ->from(CatalogBaseGoods::tableName())
                    ->where([
                        "cat_id"  => $baseCatalog->id,
                        "deleted" => 0
                    ])
                    ->andFilterWhere(["LIKE", "product", $searchString])
                    ->orFilterWhere(["LIKE", "article", $searchString]);
            } else {
                $query = (new Query())
                    ->select([
                        "id"            => "cat.id",
                        "article",
                        "product"       => "cbg.product",
                        "base_goods_id" => "cbg.id",
                        "goods_id"      => "cg.id",
                        "units",
                        "ed",
                        "base_price"    => "cbg.price",
                        "price"         => "cg.price",
                        "cbg.status"
                    ])
                    ->from(["cat" => Catalog::tableName()])
                    ->leftJoin(["cg" => CatalogGoods::tableName()], "cat.id = cg.cat_id")
                    ->leftJoin(["cbg" => CatalogBaseGoods::tableName()], "cg.base_goods_id = cbg.id")
                    ->where("cat.id = :catalogId AND cbg.deleted <> :deleted", [
                        ":catalogId" => $cat_id,
                        ":deleted"   => 1
                    ])
                    ->andFilterWhere(["LIKE", "cbg.product", $searchString])
                    ->orFilterWhere(["LIKE", "cbg.article", $searchString]);
            }
        } else {
            if (!$cat_id) {
                $query = (new Query())
                    ->select([
                        "id",
                        "article",
                        "product",
                        "units",
                        "category_id",
                        "price",
                        "ed",
                        "note",
                        "status",
                        "market_place"
                    ])
                    ->from(CatalogBaseGoods::tableName())
                    ->where("cat_id = :catalogId AND deleted <> :deleted", [
                        ":catalogId" => $baseCatalog->id,
                        ":deleted"   => 1
                    ]);
            } else {
                $query = (new Query())
                    ->select([
                        "id"            => "cat.id",
                        "article",
                        "product"       => "cbg.product",
                        "base_goods_id" => "cbg.id",
                        "goods_id"      => "cg.id",
                        "units",
                        "ed",
                        "base_price"    => "cbg.price",
                        "price"         => "cg.price",
                        "cbg.status"
                    ])
                    ->from(["cat" => Catalog::tableName()])
                    ->leftJoin(["cg" => CatalogGoods::tableName()], "cat.id = cg.cat_id")
                    ->leftJoin(["cbg" => CatalogBaseGoods::tableName()], "cg.base_goods_id = cbg.id")
                    ->where("cat.id = :catalogId AND cbg.deleted <> :deleted", [
                        ":catalogId" => $cat_id,
                        ":deleted"   => 1
                    ]);
            }
        }
        $dataProvider = new \yii\data\SqlDataProvider([
            'sql'        => $query->createCommand()->getRawSql(),
            'totalCount' => $query->count(),
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort'       => [
                'attributes' => [
                    'article',
                    'product',
                    'units',
                    'category_id',
                    'price',
                    'ed',
                    'note',
                    'status',
                ],
            ],
        ]);
        $currentUser->setAttribute('organization_id', $vendor_id);
        $catalog = $currentCatalog;
        $searchModel2 = new RelationSuppRest;
        $dataProvider2 = $searchModel2->search(Yii::$app->request->queryParams, $currentUser, RelationSuppRest::PAGE_CATALOG);
        return $this->renderPartial('basecatalog_new', compact('searchString', 'dataProvider', 'searchModel2', 'dataProvider2', 'currentCatalog', 'vendor_id', 'catalog', 'cat_id'));
    }

    private function getCatalogData($vendor_id, $cat_id = null)
    {
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $currentUser->organization_id = $vendor_id;

        if (!empty($cat_id)) {
            $catalog = Catalog::findOne([
                'supp_org_id' => $vendor_id,
                'id'          => $cat_id
            ]);
        } else {
            $catalog = Catalog::findOne([
                'supp_org_id' => $vendor_id,
                'type'        => Catalog::BASE_CATALOG
            ]);
        }

        $catalogId = $catalog->id;
        $searchString = trim(\Yii::$app->request->get('searchString'));
        if ($cat_id != null) {
            $query = (new Query())
                ->select([
                    "id"            => "cat.id",
                    "article",
                    "product"       => "cbg.product",
                    "base_goods_id" => "cbg.id",
                    "goods_id"      => "cg.id",
                    "units",
                    "ed",
                    "base_price"    => "cbg.price",
                    "price"         => "cg.price",
                    "cbg.status"
                ])
                ->from(["cat" => Catalog::tableName()])
                ->leftJoin(["cg" => CatalogGoods::tableName()], "cat.id = cg.cat_id")
                ->leftJoin(["cbg" => CatalogBaseGoods::tableName()], "cg.base_goods_id = cbg.id")
                ->where("cat.id = :catalogId AND cbg.deleted <> :deleted", [
                    ":catalogId" => $cat_id,
                    ":deleted"   => 1
                ]);
        } else {
            $query = (new Query())
                ->select([
                    "id",
                    "article",
                    "product",
                    "units",
                    "category_id",
                    "price",
                    "ed",
                    "note",
                    "status",
                    "market_place"
                ])
                ->from(CatalogBaseGoods::tableName())
                ->where("cat_id = :catalogId AND deleted <> :deleted", [
                    ":catalogId" => $catalogId,
                    ":deleted"   => 1
                ]);
        }
        $dataProvider = new \yii\data\SqlDataProvider([
            'sql'        => $query->createCommand()->getRawSql(),
            'totalCount' => $query->count(),
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort'       => [
                'attributes' => [
                    'article',
                    'product',
                    'units',
                    'category_id',
                    'price',
                    'ed',
                    'note',
                    'status',
                ],
            ],
        ]);
        $currentUser->setAttribute('organization_id', $vendor_id);
        $searchModel2 = new RelationSuppRest;
        $dataProvider2 = $searchModel2->search(Yii::$app->request->queryParams, $currentUser, RelationSuppRest::PAGE_CATALOG);
        return $this->renderPartial('basecatalog_new', compact('searchString', 'dataProvider', 'searchModel2', 'dataProvider2', 'catalog', 'vendor_id', 'cat_id'));
    }

    public function actionIndex($id, $cat_id = null)
    {
        $vendor_id = $id;
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $currentOrganization = Organization::findOne($vendor_id);
        if ($currentOrganization->franchisee->id != $currentUser->franchiseeUser->franchisee_id) {
            throw new HttpException(403, Yii::t('app', 'franchise.controllers.catalog.no_access', ['ru' => 'Доступ запрещен']));
        }
        if (!Catalog::find()->where(['supp_org_id' => $vendor_id, 'type' => Catalog::BASE_CATALOG])->exists()) {
            $step = $currentUser->organization->step;
            return $this->render("catalogs/createBaseCatalog", compact("Catalog", "step"));
        } else {
            if ($currentOrganization->step == Organization::STEP_ADD_CATALOG) {
                $currentOrganization->step = Organization::STEP_OK;
                $currentOrganization->save();
            }
            $arrBaseCatalog = Catalog::GetCatalogs(\common\models\Catalog::BASE_CATALOG, $vendor_id);
            $searchString = "";
            $restaurant = "";
            $type = "";
            $relation_supp_rest = new RelationSuppRest();
            $relation = yii\helpers\ArrayHelper::map(\common\models\Organization::find()->
            where(['in', 'id', \common\models\RelationSuppRest::find()->
            select('rest_org_id')->
            where(['supp_org_id' => $vendor_id, 'invite' => '1'])])->all(), 'id', 'name');
            $arrCatalog = Catalog::find()->select(['id', 'status', 'name', 'created_at'])->
            where(['supp_org_id' => $vendor_id, 'type' => 2])->all();

            if (Yii::$app->request->isPost) {
                $searchString = htmlspecialchars(trim(\Yii::$app->request->post('searchString')));
                $restaurant = htmlspecialchars(trim(\Yii::$app->request->post('restaurant')));
                //echo $restaurant;
                if (!empty($restaurant)) {
                    $arrCatalog = Catalog::find()->select(['id', 'status', 'name', 'created_at', 'type', 'id'])->
                    where(['supp_org_id' => $vendor_id])->
                    andFilterWhere(['id' => \common\models\RelationSuppRest::find()->
                    select(['cat_id'])->
                    where(['supp_org_id' => $vendor_id,
                           'rest_org_id' => $restaurant])])->one();
                    if (empty($arrCatalog)) {
                        $arrCatalog == "";
                    } else {
                        if ($arrCatalog->type == 1) {
                            $type = 1;  //ресторан подключен к главному каталогу
                        } else {
                            $catalog_id = $arrCatalog->id;
                            $arrCatalog = Catalog::find()->select(['id', 'status', 'name', 'created_at'])->
                            where(['supp_org_id' => $vendor_id, 'id' => $catalog_id])->all();
                        }
                    }
                } else {
                    $arrCatalog = Catalog::find()->select(['id', 'status', 'name', 'created_at'])->
                    where(['supp_org_id' => $vendor_id, 'type' => 2])->
                    andFilterWhere(['LIKE', 'name', $searchString])->all();
                }
            }
            $catalog = Catalog::findOne($cat_id);
            if ($catalog && $catalog->type == \common\models\Catalog::BASE_CATALOG) {
                $cat_id = null;
            }
            $arrCatalog = array_merge($arrBaseCatalog, $arrCatalog);
            $table = $this->getCatalogData($vendor_id, $cat_id);
            return $this->render("index_new", compact("relation_supp_rest", "currentUser", "relation", "searchString", "restaurant", 'type', 'arrCatalog', 'currentOrganization', 'table', 'cat_id'));
        }
    }

    public function actionStep1($vendor_id)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $catalog = new Catalog();
            $post = Yii::$app->request->post();
            if ($catalog->load($post)) {
                $catalog->supp_org_id = $vendor_id;
                $catalog->type = Catalog::CATALOG;
                $catalog->status = 1;
                if ($catalog->validate()) {
                    $catalog->save();
                    return (['success' => true, 'cat_id' => $catalog->id]);
                } else {
                    $result = ['success' => false, 'type' => 1, 'alert' => ['class' => 'danger-fk', 'title' => Yii::t('error', 'franchise.controllers.oops', ['ru' => 'УПС! Ошибка']), 'body' => Yii::t('app', 'franchise.controllers.catalog_name', ['ru' => 'Укажите корректное  <strong>Имя</strong> каталога'])]];
                    return $result;
                    exit;
                }
            } else {
                return (['success' => false, 'type' => 2, Yii::t('app', 'franchise.controllers.post_undefined', ['ru' => 'POST не определен'])]);
                exit;
            }
        }
        $catalog = new Catalog();
        $cat_id = $catalog->id;
        return $this->render('newcatalog/step-1', compact('catalog', 'cat_id', 'vendor_id'));
    }

    public function actionStep1Update($vendor_id, $id)
    {
        $cat_id = $id;
        if (!Catalog::find()->where(['id' => $id, 'supp_org_id' => $vendor_id])->exists()) {
            return $this->redirect(['vendor/index']);
        }
        $catalog = Catalog::find()->where(['id' => $cat_id])->one();
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $post = Yii::$app->request->post();
            if ($catalog->load($post)) {
                if ($catalog->validate()) {
                    $catalog->save();
                    return (['success' => true, 'cat_id' => $catalog->id]);
                } else {
                    $result = ['success' => false, 'type' => 1, 'alert' => ['class' => 'danger-fk', 'title' => Yii::t('app', 'franchise.controllers.oops_two', ['ru' => 'УПС! Ошибка']), 'body' => Yii::t('app', 'franchise.controllers.catalog_name_two', ['ru' => 'Укажите корректное  <strong>Имя</strong> каталога'])]];
                    return $result;
                    exit;
                }
            }
        }
        return $this->render('newcatalog/step-1', compact('catalog', 'cat_id', 'searchModel', 'dataProvider', 'vendor_id'));
    }

    /**
     * @param $vendor_id
     * @param $id
     * @return Response
     * @throws HttpException
     * @throws \yii\db\Exception
     */
    public function actionStep1Clone($vendor_id, $id)
    {
        $cat_id_old = $id; //id исходного каталога
        $model = Catalog::findOne(['id' => $id, 'supp_org_id' => $vendor_id]);
        if (empty($model)) {
            throw new \yii\web\HttpException(404, Yii::t('error', 'franchise.controllers.get_out', ['ru' => 'Нет здесь ничего такого, проходите, гражданин']));
        }
        $model->id = null;
        $model->name = $model->name . ' ' . date('H:i:s');
        $cat_type = $model->type;   //текущий тип каталога(исходный)
        $model->type = Catalog::CATALOG; //переопределяем тип на 2
        $model->status = 1;
        $model->isNewRecord = true;
        $model->save();

        $cat_id = $model->id; //новый каталог id
        $cgTable = CatalogGoods::tableName();
        $cbgTable = CatalogBaseGoods::tableName();
        $now = new Expression("NOW()");
        if ($cat_type == Catalog::BASE_CATALOG) {
            Yii::$app->db->createCommand(
                "INSERT INTO {$cgTable} (cat_id, base_goods_id, price, created_at) "
                . "SELECT {$cat_id}, id, price, {$now} FROM {$cbgTable} "
                . "WHERE cat_id = {$cat_id_old} AND deleted <> 1"
            )->execute();
        }
        if ($cat_type == Catalog::CATALOG) {
            Yii::$app->db->createCommand(
                "INSERT INTO {$cgTable} (cat_id, base_goods_id, price, created_at) "
                . "SELECT {$cat_id}, base_goods_id, price, {$now} FROM {$cgTable} "
                . "WHERE cat_id = {$cat_id_old}"
            )->execute();
        }

        return $this->redirect(['catalog/step-1-update', 'vendor_id' => $vendor_id, 'id' => $cat_id]);
    }

    public function actionStep2AddProduct()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if (Yii::$app->request->post('state') == 'true') {
                $product_id = Yii::$app->request->post('baseProductId');
                $catalogGoods = new CatalogGoods();
                $catalogGoods->base_goods_id = $product_id;
                $catalogGoods->cat_id = Yii::$app->request->post('cat_id');

                $catalogGoods->price = CatalogBaseGoods::findOne(['id' => $product_id])->price;
                $catalogGoods->save();
                return (['success' => true, Yii::t('app', 'franchise.controllers.added', ['ru' => 'Добавлен'])]);
                exit;
            } else {
                $product_id = Yii::$app->request->post('baseProductId');
                $catalog_id = Yii::$app->request->post('cat_id');
                if ($product_id && $catalog_id) {
                    CatalogGoods::deleteAll(['base_goods_id' => $product_id, 'cat_id' => $catalog_id]);
                    return (['success' => true, Yii::t('app', 'franchise.controllers.deleted', ['ru' => 'Удален'])]);
                    exit;
                }
                return (['success' => false, Yii::t('error', 'franchise.controllers.error', ['ru' => 'Ошибка'])]);
            }
        }
    }

    public function actionStep2($vendor_id, $id)
    {
        $cat_id = $id;
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if (Yii::$app->request->post('check')) {
                if (CatalogGoods::find()->where(['cat_id' => $cat_id])->exists()) {
                    return (['success' => true, 'cat_id' => $cat_id]);
                } else {
                    return (['success' => false, 'type' => 1, 'message' => Yii::t('app', 'franchise.controllers.empty_catalog', ['ru' => 'Пустой каталог'])]);
                    exit;
                }
            }
        }

        $baseCatalog = Catalog::findOne(['supp_org_id' => $vendor_id, 'type' => Catalog::BASE_CATALOG]);
        if (empty($baseCatalog)) {
            throw new \yii\web\HttpException(404, Yii::t('error', 'franchise.controllers.get_out_two', ['ru' => 'Нет здесь ничего такого, проходите, гражданин']));
        }
        $query = (new Query())
            ->select([
                "id",
                "article",
                "product",
                "units",
                "category_id",
                "price",
                "ed",
                "status",
            ])
            ->from(CatalogBaseGoods::tableName())
            ->where("cat_id = :catalogId AND deleted <> :deleted", [
                ":catalogId" => $baseCatalog->id,
                ":deleted"   => 1
            ]);

        if (!empty(trim(\Yii::$app->request->get('searchString')))) {
            $searchString = trim(\Yii::$app->request->get('searchString'));
            $query->andFilterWhere(["LIKE", "product", $searchString]);
        }

        $dataProvider = new \yii\data\SqlDataProvider([
            'sql'        => $query->createCommand()->getRawSql(),
            'totalCount' => $query->count(),
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort'       => [
                'attributes'   => [
                    'id',
                    'article',
                    'product',
                    'units',
                    'category_id',
                    'price',
                    'ed',
                    'status',
                ],
                'defaultOrder' => [
                    'product' => SORT_ASC
                ]
            ],
        ]);
        return $this->render('newcatalog/step-2', compact('searchModel', 'dataProvider', 'cat_id', 'vendor_id'));
    }

    public function actionStep3Copy($vendor_id, $id)
    {
        $cat_id = $id;
        $model = Catalog::findOne(['id' => $id, 'supp_org_id' => $vendor_id]);
        if (empty($model)) {
            throw new \yii\web\HttpException(404, Yii::t('error', 'franchise.controllers.get_out_three', ['ru' => 'Нет здесь ничего такого, проходите, гражданин']));
        }
        $productList = (new Query())
            ->select([
                "id"            => "cat.id",
                "article",
                "product"       => "cbg.product",
                "base_goods_id" => "cbg.id",
                "goods_id"      => "cg.id",
                "units",
                "ed",
                "base_price"    => "cbg.price",
                "price"         => "cg.price",
                "cbg.status"
            ])
            ->from(["cat" => Catalog::tableName()])
            ->leftJoin(["cg" => CatalogGoods::tableName()], "cat.id = cg.cat_id")
            ->leftJoin(["cbg" => CatalogBaseGoods::tableName()], "cg.base_goods_id = cbg.id")
            ->where("cat.id = :catalogId AND cbg.deleted <> :deleted", [
                ":catalogId" => $id,
                ":deleted"   => 1
            ])
            ->all();

        $array = [];
        foreach ($productList as $product) {
            array_push($array, [
                'article'       => $product['article'],
                'product'       => html_entity_decode($product['product']),
                'base_goods_id' => $product['base_goods_id'],
                'goods_id'      => $product['goods_id'],
                'base_price'    => $product['base_price'],
                'price'         => $product['price'],
                'ed'            => $product['ed'],
                'total_price'   => $product['price']]);
        }
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $post = Yii::$app->request->post();
            $arrCatalog = json_decode(Yii::$app->request->post('catalog'), JSON_UNESCAPED_UNICODE);
            $numberPattern = '/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/';
            foreach ($arrCatalog as $arrCatalogs) {
                $goods_id = htmlspecialchars(trim($arrCatalogs['dataItem']['goods_id']));
                $price = htmlspecialchars(trim($arrCatalogs['dataItem']['total_price']));

                if (!CatalogGoods::find()->where(['id' => $goods_id])->exists()) {
                    $result = ['success' => false, 'alert' => ['class' => 'danger-fk', 'title' => Yii::t('error', 'franchise.controllers.oops_two', ['ru' => 'УПС! Ошибка']), 'body' => Yii::t('app', 'franchise.controllers.wrong_good', ['ru' => 'Неверный товар'])]];
                    return $result;
                    exit;
                }

                $price = str_replace(',', '.', $price);

                if (!preg_match($numberPattern, $price)) {
                    $result = ['success' => false, 'alert' => ['class' => 'danger-fk', 'title' => Yii::t('error', 'franchise.controllers.oops_three', ['ru' => 'УПС! Ошибка']), 'body' => Yii::t('app', 'franchise.controllers.wrong_format', ['ru' => 'Неверный формат <strong>Цены</strong><br><small>только число в формате 0,00</small>'])]];
                    return $result;
                    exit;
                }
            }
            foreach ($arrCatalog as $arrCatalogs) {
                $goods_id = htmlspecialchars(trim($arrCatalogs['dataItem']['goods_id']));
                $price = htmlspecialchars(trim($arrCatalogs['dataItem']['total_price']));

                $price = str_replace(',', '.', $price);

                $catalogGoods = CatalogGoods::findOne(['id' => $goods_id]);
                $catalogGoods->price = $price;
                $catalogGoods->update();
            }
            $result = ['success' => true, 'alert' => ['class' => 'success-fk', 'title' => Yii::t('app', 'franchise.controllers.saved', ['ru' => 'Сохранено']), 'body' => Yii::t('app', 'franchise.controllers.data_updated', ['ru' => 'Данные успешно обновлены'])]];
            return $result;
            exit;
        }
        return $this->renderPartial('newcatalog/step-3-copy', compact('array', 'cat_id', 'vendor_id'));
    }

    public function actionStep3($id)
    {
        $cat_id = $id;
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $model = Catalog::findOne(['id' => $id, 'supp_org_id' => $currentUser->organization_id]);
        if (empty($model)) {
            throw new \yii\web\HttpException(404, Yii::t('error', 'franchise.controllers.get_out_four', ['ru' => 'Нет здесь ничего такого, проходите, гражданин']));
        }
        $searchModel = new CatalogGoods();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $cat_id);
        return $this->render('newcatalog/step-3', compact('searchModel', 'dataProvider', 'exportModel'));
    }

    public function actionStep3UpdateProduct($id)
    {
        $catalogGoods = CatalogGoods::find()->where(['id' => $id])->one();
        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if ($catalogGoods->load($post)) {
                if ($catalogGoods->validate()) {

                    $catalogGoods->save();

                    $message = Yii::t('app', 'franchise.controllers.product_updated', ['ru' => 'Продукт обновлен!']);
                    return $this->renderAjax('catalogs/_success', ['message' => $message]);
                }
            }
        }
        return $this->renderAjax('catalogs/_productForm', compact('catalogGoods'));
    }

    public function actionStep4($vendor_id, $id)
    {
        $cat_id = $id;
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $currentUser->organization_id = $vendor_id;
        $model = Catalog::findOne(['id' => $id, 'supp_org_id' => $vendor_id]);
        if (empty($model)) {
            throw new \yii\web\HttpException(404, Yii::t('app', 'franchise.controllers.get_out_six', ['ru' => 'Нет здесь ничего такого, проходите, гражданин']));
        }
        $searchModel = new RelationSuppRest;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $currentUser, RelationSuppRest::PAGE_CATALOG);
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if (Yii::$app->request->post('add-client')) {
                if (Yii::$app->request->post('state') == 'true') {
                    $rest_org_id = Yii::$app->request->post('rest_org_id');
                    $relation_supp_rest = RelationSuppRest::findOne(['rest_org_id' => $rest_org_id, 'supp_org_id' => $vendor_id]);
                    $relation_supp_rest->cat_id = $cat_id;
                    $relation_supp_rest->status = 1;
                    $relation_supp_rest->update();
                    $rows = User::find()->where(['organization_id' => $rest_org_id])->all();
                    foreach ($rows as $row) {
                        if ($row->profile->phone && $row->profile->sms_allow) {
                            $text = Yii::$app->sms->prepareText('sms.designated_catalog', [
                                'vendor_name' => $currentUser->organization->name
                            ]);
                            Yii::$app->sms->send($text, $row->profile->phone);
                        }
                    }
                    return (['success' => true, Yii::t('app', 'franchise.controllers.subscribed', ['ru' => 'Подписан'])]);
                    exit;
                } else {
                    $rest_org_id = Yii::$app->request->post('rest_org_id');
                    $relation_supp_rest = RelationSuppRest::findOne(['rest_org_id' => $rest_org_id, 'supp_org_id' => $vendor_id]);
                    $relation_supp_rest->cat_id = Catalog::NON_CATALOG;
                    $relation_supp_rest->status = 0;
                    $relation_supp_rest->update();
                    return (['success' => true, Yii::t('app', 'franchise.controllers.not_subscribed', ['ru' => 'Не подписан'])]);
                    exit;
                }
            }
        }
        return $this->render('newcatalog/step-4', compact('searchModel', 'dataProvider', 'currentCatalog', 'cat_id', 'vendor_id'));
    }

    public function actionChangecatalogprop()
    {
        if (Yii::$app->request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;
            // $CatalogBaseGoods = new CatalogBaseGoods;
            $id = \Yii::$app->request->post('id');
            $elem = \Yii::$app->request->post('elem');

            if ($elem == 'market') {
                $CatalogBaseGoods = CatalogBaseGoods::findOne(['id' => $id]);
                if ($CatalogBaseGoods->market_place == 0) {
                    $set = 1;
                } else {
                    $set = 0;
                }
                $CatalogBaseGoods->market_place = $set;
                $CatalogBaseGoods->update();

                $result = ['success' => true, 'status' => 'update market'];
                return $result;
            }
            if ($elem == 'status') {
                $CatalogBaseGoods = CatalogBaseGoods::findOne(['id' => $id]);
                if (empty($CatalogBaseGoods->status)) {
                    $set = CatalogBaseGoods::STATUS_ON;
                } else {
                    $set = CatalogBaseGoods::STATUS_OFF;
                }
                //CatalogBaseGoods::updateAll(['status' =>$set], ['id' => $id]);
                $CatalogBaseGoods->status = $set;
                $CatalogBaseGoods->update();

                $result = ['success' => true, 'status' => $set];
                return $result;
            }
        }
    }

    public function actionChangesetcatalog($vendor_id)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $currentUser = User::findIdentity(Yii::$app->user->id);
            //$relation_supp_rest = new RelationSuppRest;
            $curCat = \Yii::$app->request->post('curCat'); //catalog
            $id = \Yii::$app->request->post('id'); //rest_org_id
            $state = Yii::$app->request->post('state');

            if ($state == 'true') {
                $rest_org_id = $id;
                $relation_supp_rest = RelationSuppRest::findOne(['rest_org_id' => $rest_org_id, 'supp_org_id' => $vendor_id]);
                $relation_supp_rest->cat_id = $curCat;
                $relation_supp_rest->status = 1;
                $relation_supp_rest->update();
                $rows = User::find()->where(['organization_id' => $rest_org_id])->all();
                foreach ($rows as $row) {
                    if ($row->profile->phone && $row->profile->sms_allow) {
                        $text = Yii::$app->sms->prepareText('sms.designated_catalog', [
                            'vendor_name' => $currentUser->organization->name
                        ]);
                        Yii::$app->sms->send($text, $row->profile->phone);
                    }
                }
                return (['success' => true, Yii::t('app', 'franchise.controllers.subscribed_two', ['ru' => 'Подписан'])]);
                exit;
            } else {
                $rest_org_id = $id;
                $relation_supp_rest = RelationSuppRest::findOne(['rest_org_id' => $rest_org_id, 'supp_org_id' => $vendor_id]);
                $relation_supp_rest->cat_id = Catalog::NON_CATALOG;
                $relation_supp_rest->status = 0;
                $relation_supp_rest->update();
                return (['success' => true, Yii::t('app', 'franchise.controllers.not_subscribed_two', ['ru' => 'Не подписан'])]);
                exit;
            }
        }
    }

    public function actionChangecatalogstatus()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $id = \Yii::$app->request->post('id');
            $catalog = Catalog::findOne(['id' => $id, 'type' => Catalog::CATALOG]);
            if (isset($catalog)) {
                $catalog->status = \Yii::$app->request->post('state') == 'true' ? 1 : 0;
                $catalog->update();
            }
            $result = ['success' => true, 'status' => 'update status'];
            return $result;
        }
    }

    public function actionCreateCatalog()
    {
        $relation_supp_rest = new RelationSuppRest;
        if (Yii::$app->request->isAjax) {

        }
        return $this->renderAjax('catalogs/_create', compact('relation_supp_rest'));
    }

    public function actionMycatalogdelcatalog()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $cat_id = \Yii::$app->request->post('id');

            $Catalog = Catalog::find()->where(['id' => $cat_id, 'type' => 2])->one();
            $Catalog->delete();

            CatalogGoods::deleteAll(['cat_id' => $cat_id]);

            RelationSuppRest::updateAll(['cat_id' => null], ['cat_id' => $cat_id]);

            $result = ['success' => true];
            return $result;
            exit;
        }
    }

    public function actionAjaxDeleteProduct()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $product_id = \Yii::$app->request->post('id');
            CatalogBaseGoods::updateAll(['deleted' => 1, 'es_status' => 2], ['id' => $product_id]);

            $result = ['success' => true];
            return $result;
            exit;
        }
    }

    public function actionAjaxUpdateProductMarketPlace($id, $supp_org_id = null, $catalog_id = null)
    {
        if ($id) {
            $catalogBaseGoods = CatalogBaseGoods::find()->where(['id' => $id])->one();
        } else {
            $catalogBaseGoods = new CatalogBaseGoods();
        }
        $catalogBaseGoods->scenario = 'marketPlace';
        $countrys = (new Query())
            ->select([
                "id",
                "name"
            ])
            ->from([MpCountry::tableName()])
            ->where(["name" => "Россия"])
            ->union((new Query())
                ->select([
                    "id",
                    "name"
                ])
                ->from([MpCountry::tableName()])
                ->where("name <> :name", [":name" => "Россия"]))
            ->createCommand()
            ->queryAll();

        foreach ($countrys as &$country) {
            $country['name'] = Yii::t('app', $country['name']);
        }
        if (!empty($catalogBaseGoods->category_id)) {
            $catalogBaseGoods->sub1 = \common\models\MpCategory::find()->select(['parent'])->where(['id' => $catalogBaseGoods->category_id])->one()->parent;
            $catalogBaseGoods->sub2 = $catalogBaseGoods->category_id;
        }
        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if ($catalogBaseGoods->load($post)) {
                $catalogBaseGoods->price = preg_replace("/[^-0-9\.]/", "", str_replace(',', '.', $catalogBaseGoods->price));
                if ($supp_org_id) {
                    $catalogBaseGoods->supp_org_id = $supp_org_id;
                    $catalogBaseGoods->cat_id = $catalog_id;
                }
                if ($catalogBaseGoods->market_place == 1) {
                    if ($post && $catalogBaseGoods->validate()) {
                        $catalogBaseGoods->category_id = $catalogBaseGoods->sub2;
                        $catalogBaseGoods->es_status = 1;
                        $catalogBaseGoods->save();
                        $message = Yii::t('app', 'franchise.controllers.product_updated_two', ['ru' => 'Продукт обновлен!']);
                        return $this->renderAjax('_success', ['message' => $message]);
                    }
                } else {
                    if ($post && $catalogBaseGoods->validate()) {
                        $catalogBaseGoods->category_id = $catalogBaseGoods->sub1 ? $catalogBaseGoods->sub2 : null;
                        $catalogBaseGoods->es_status = 2;
                        $catalogBaseGoods->save();
                        $message = Yii::t('app', 'franchise.controllers.product_updated_three', ['ru' => 'Продукт обновлен!']);
                        return $this->renderAjax('_success', ['message' => $message]);
                    }
                }
            }
        }
        return $this->renderAjax('_form', compact('catalogBaseGoods', 'countrys', 'supp_org_id', 'catalog_id'));
    }

    public function actionGetSubCat()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $id = end($_POST['depdrop_parents']);
            $list = \common\models\MpCategory::find()->select(['id', 'name'])->
            andWhere(['parent' => $id])->
            asArray()->
            all();
            $selected = null;
            if ($id != null && count($list) > 0) {
                $selected = '';
                if (!empty($_POST['depdrop_params'])) {
                    $params = $_POST['depdrop_params'];
                    $id1 = $params[0]; // get the value of 1
                    $id2 = $params[1]; // get the value of 2
                    foreach ($list as $i => $cat) {
                        $out[] = ['id' => $cat['id'], 'name' => Yii::t('app', $cat['name'])];
                        if ($cat['id'] == $id1) {
                            $selected = $cat['id'];
                        }
                        if ($cat['id'] == $id2) {
                            $selected = $id2;
                        }
                    }
                }
                echo Json::encode(['output' => $out, 'selected' => $selected]);
                return;
            }
        }
        echo Json::encode(['output' => '', 'selected' => '']);
    }

}
