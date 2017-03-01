<?php

namespace franchise\controllers;

use Yii;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Json;
use common\components\AccessRule;
use common\models\Role;
use common\models\User;
use common\models\Profile;
use common\models\Organization;
use common\models\Order;
use common\models\CatalogBaseGoods;
use yii\web\Response;

/**
 * Description of AppController
 *
 * @author sharaf
 */
class AppController extends DefaultController {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                // We will override the default rule config with the new AccessRule class
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'only' => ['index', 'settings', 'promotion', 'users'],
                'rules' => [
                    [
                        'actions' => ['index', 'settings', 'promotion', 'users'],
                        'allow' => true,
                        'roles' => [
                            Role::ROLE_FRANCHISEE_OWNER,
                            Role::ROLE_FRANCHISEE_OPERATOR,
                            Role::ROLE_FRANCHISEE_ACCOUNTANT,
                            Role::ROLE_ADMIN,
                        ],
                    ],
                ],
            /* 'denyCallback' => function($rule, $action) {
              throw new HttpException(404 ,'Нет здесь ничего такого, проходите, гражданин');
              } */
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Displays desktop.
     *
     * @return mixed
     */
    public function actionIndex() {
                
        //---graph start
        $query = "SELECT truncate(sum(total_price),1) as spent, year(created_at) as year, month(created_at) as month, day(created_at) as day "
                . "FROM `order` LEFT JOIN `franchisee_associate` ON `order`.vendor_id = `franchisee_associate`.organization_id "
                . "where status in (".Order::STATUS_PROCESSING.",".Order::STATUS_DONE.",".Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT.",".Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR.") "
                . "and created_at BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE() + INTERVAL 1 DAY AND `franchisee_associate`.franchisee_id = " . $this->currentFranchisee->id . " "
                . "group by year(created_at), month(created_at), day(created_at)";
        $command = Yii::$app->db->createCommand($query);
        $ordersByDay = $command->queryAll();
        $dayLabels = [];
        $dayTurnover = [];
        $total = 0;
        foreach ($ordersByDay as $order) {
            $dayLabels[] = $order["day"] . " " . date('M', strtotime("2000-$order[month]-01")) . " " . $order["year"];
            $dayTurnover[] = $order["spent"];
            $total += $order["spent"];
        }
        //---graph end

        $params = Yii::$app->request->getQueryParams();
        $searchModel = new \franchise\models\OrderSearch();
        $dataProvider = $searchModel->search($params, $this->currentFranchisee->id, true);

        return $this->render('index', compact('dataProvider', 'dayLabels', 'dayTurnover'));
    }

    /**
     * Displays general settings
     * 
     * @return mixed
     */
    public function actionOrders() {
        $searchModel = new \franchise\models\OrderSearch();
        $today = new \DateTime();
        $searchModel->date_to = $today->format('d.m.Y');
        $searchModel->date_from = "01.02.2017";

        $params = Yii::$app->request->getQueryParams();

        if (Yii::$app->request->post("OrderSearch")) {
            $params['OrderSearch'] = Yii::$app->request->post("OrderSearch");
        }
        $dataProvider = $searchModel->search($params, $this->currentFranchisee->id);
        
        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('orders', compact('searchModel', 'dataProvider'));
        } else {
            return $this->render('orders', compact('searchModel', 'dataProvider'));
        }
    }

    /**
     * Displays general settings
     * 
     * @return mixed
     */
    public function actionSettings() {
        return $this->render('/site/under-construction');
    }

    /**
     * Displays franchise users list
     * 
     * @return mixed
     */
    public function actionUsers() {
        /** @var \common\models\search\UserSearch $searchModel */
        $searchModel = new \franchise\models\UserSearch();
        //$params = Yii::$app->request->getQueryParams();
        $params['UserSearch'] = Yii::$app->request->post("UserSearch");
        $this->loadCurrentUser();
        $dataProvider = $searchModel->search($params, $this->currentFranchisee->id);

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('employees', compact('searchModel', 'dataProvider'));
        } else {
            return $this->render('employees', compact('searchModel', 'dataProvider'));
        }
    }

    /*
     *  User validate
     */

    public function actionAjaxValidateUser() {
        $user = new User();
        $profile = new Profile();

        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if ($user->load($post)) {
                $profile->load($post);

                if ($user->validate() && $profile->validate()) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return json_encode(ActiveForm::validateMultiple([$user, $profile]));
                }
            }
        }
    }

    /*
     *  User create
     */

    public function actionAjaxCreateUser() {
        $user = new User(['scenario' => 'manageNew']);
        $profile = new Profile();
        $organizationType = Organization::TYPE_FRANCHISEE;

        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if ($user->load($post)) {
                $profile->load($post);

                if ($user->validate() && $profile->validate()) {

                    $user->setRegisterAttributes($user->role_id)->save();
                    $profile->setUser($user->id)->save();
                    $user->setFranchisee($this->currentFranchisee->id);
//                    $this->currentUser->sendEmployeeConfirmation($user);
                    $message = 'Пользователь добавлен!';
                    return $this->renderAjax('settings/_success', ['message' => $message]);
                }
            }
        }

        return $this->renderAjax('settings/_userForm', compact('user', 'profile', 'organizationType'));
    }

    /*
     *  User update
     */

    public function actionAjaxUpdateUser($id) {
        $user = User::find()
                ->joinWith("franchiseeUser")
                ->where([
                    'franchisee_user.franchisee_id' => $this->currentFranchisee->id,
                    'user.id' => $id
                ])
                ->one();
        $user->setScenario("manage");
        $profile = $user->profile;
        $organizationType = Organization::TYPE_FRANCHISEE;

        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if ($user->load($post)) {
                $profile->load($post);

                if ($user->validate() && $profile->validate()) {

                    $user->save();
                    $profile->save();

                    $message = 'Пользователь обновлен!';
                    return $this->renderAjax('settings/_success', ['message' => $message]);
                }
            }
        }

        return $this->renderAjax('settings/_userForm', compact('user', 'profile', 'organizationType'));
    }

    /*
     *  User delete (not actual delete, just remove organization relation)
     */

    public function actionAjaxDeleteUser() {
        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if ($post && isset($post['id'])) {
                $user = $user = User::find()
                        ->joinWith("franchiseeUser")
                        ->where([
                            'franchisee_user.franchisee_id' => $this->currentFranchisee->id,
                            'user.id' => $post["id"],
                        ])
                        ->one();
                $usersCount = count($this->currentFranchisee->franchiseeUsers);
                if ($user->id == $this->currentUser->id) {
                    $message = 'Может воздержимся от удаления себя?';
                    return $this->renderAjax('settings/_success', ['message' => $message]);
                }
                if ($user && ($usersCount > 1)) {
                    $user->role_id = Role::ROLE_USER;
                    $user->organization_id = null;
                    if ($user->save() && $user->franchiseeUser->delete()) {
                        $message = 'Пользователь удален!';
                        return $this->renderAjax('settings/_success', ['message' => $message]);
                    }
                }
            }
        }
        $message = 'Не удалось удалить пользователя!';
        return $this->renderAjax('settings/_success', ['message' => $message]);
    }

    /**
     * Displays promotion
     * 
     * @return mixed
     */
    public function actionPromotion() {
        return $this->render('promotion');
    }
    public function actionCatalog($id) {
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $searchString = "";
        if (!empty(trim(\Yii::$app->request->get('searchString')))) {
            $searchString = "%" . trim(\Yii::$app->request->get('searchString')) . "%";
//            
//            $count = \common\models\CatalogBaseGoods::find()
//            ->where([
//            'cat_id'=>$id, 
//            'deleted'=>\common\models\CatalogBaseGoods::DELETED_OFF
//            ])
//            ->andWhere(['like','product',$searchString])
//            ->count();
//            
            $sql = "SELECT id,cat_id,article,product,units,category_id,price,ed,note,status,market_place FROM catalog_base_goods "
                    . "WHERE cat_id = $id AND "
                    . "deleted=0 AND (product LIKE :product or article LIKE :article)";
            $query = \Yii::$app->db->createCommand($sql);
            $totalCount = Yii::$app->db->createCommand("SELECT count(*) FROM catalog_base_goods "
                            . "WHERE cat_id = $id AND "
                            . "deleted=".CatalogBaseGoods::DELETED_OFF." AND (product LIKE :product or article LIKE :article)", 
                    [':article' => $searchString, ':product' => $searchString])->queryScalar();
        } else {
            $sql = "SELECT id,article,cat_id,product,units,category_id,price,ed,note,status,market_place FROM catalog_base_goods "
                    . "WHERE cat_id = $id AND "
                    . "deleted=".CatalogBaseGoods::DELETED_OFF;
            $query = \Yii::$app->db->createCommand($sql);
            $totalCount = Yii::$app->db->createCommand("SELECT count(*) FROM catalog_base_goods "
                            . "WHERE cat_id = $id AND "
                            . "deleted=".CatalogBaseGoods::DELETED_OFF, [':article' => $searchString, ':product' => $searchString])->queryScalar();
        }
        $dataProvider = new \yii\data\SqlDataProvider([
            'sql' => $query->sql,
            'totalCount' => $totalCount,
            'params' => [':article' => $searchString, ':product' => $searchString],
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'attributes' => [
                    'article',
                    'product',
                    'units',
                    'category_id',
                    'price',
                    'ed',
                    'note',
                    'status',
                    'cat_id'
                ],
            ],
        ]);
        return $this->render('catalog', compact('searchString', 'dataProvider', 'id'));
    }
    public function actionAjaxEditCatalogForm() {
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $catalog = isset(Yii::$app->request->get()['catalog']) ? 
                Yii::$app->request->get()['catalog'] : 
                Yii::$app->request->post()['catalog'];
        $product_id = isset(Yii::$app->request->get()['product_id'])?
            Yii::$app->request->get()['product_id']:
            $product_id = null;
        
        if(!empty(isset($product_id))){
         $catalogBaseGoods = CatalogBaseGoods::find()->where(['id' => $product_id])->one(); 
         $catalogBaseGoods->scenario = 'marketPlace';
         if (!empty($catalogBaseGoods->category_id)) {
            $catalogBaseGoods->sub1 = \common\models\MpCategory::find()->select(['parent'])->where(['id' => $catalogBaseGoods->category_id])->one()->parent;
            $catalogBaseGoods->sub2 = $catalogBaseGoods->category_id;
        }
        }else{
         $catalogBaseGoods = new CatalogBaseGoods(['scenario' => 'marketPlace']);  
        }
        
        $sql = "SELECT id, name FROM mp_country WHERE name = \"Россия\"
	UNION SELECT id, name FROM mp_country WHERE name <> \"Россия\"";
        $countrys = \Yii::$app->db->createCommand($sql)->queryAll();

        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if ($catalogBaseGoods->load($post)) {
                $catalogBaseGoods->status = CatalogBaseGoods::STATUS_ON;
                $catalogBaseGoods->price = preg_replace("/[^-0-9\.]/", "", str_replace(',', '.', $catalogBaseGoods->price));
                if ($post && $catalogBaseGoods->validate()) {
                        $catalogBaseGoods->category_id = $catalogBaseGoods->sub2;
                        $catalogBaseGoods->save();
                        
                        $message = 'Продукт обновлен!';
                        return $this->renderAjax('catalog/_success', ['message' => $message]);
                }
            }
        }
        return $this->renderAjax('catalog/_ajaxEditCatalogForm', compact('catalogBaseGoods', 'countrys', 'catalog'));
    }
    
    public function actionGetSubCat() {
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
                        $out[] = ['id' => $cat['id'], 'name' => $cat['name']];
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
    
    public function actionAjaxDeleteProduct() {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $product_id = \Yii::$app->request->post('id');
            $catalogBaseGoods = CatalogBaseGoods::updateAll([
                'deleted' => CatalogBaseGoods::DELETED_ON,
                'es_status' => CatalogBaseGoods::ES_DELETED
                ], ['id' => $product_id]);
            
            $result = ['success' => true];
            return $result;
            exit;
        }
    }
    
}
