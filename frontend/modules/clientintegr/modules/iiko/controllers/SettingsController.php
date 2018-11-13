<?php

namespace frontend\modules\clientintegr\modules\iiko\controllers;

use api\common\models\iiko\iikoDicconst;
use api\common\models\iiko\iikoPconst;
use api\common\models\iiko\iikoSelectedProduct;
use api\common\models\iiko\iikoSelectedStore;
use api\common\models\iiko\iikoService;
use api\common\models\iiko\search\iikoDicconstSearch;
use common\helpers\ModelsCollection;
use common\models\Role;
use common\models\User;
use frontend\modules\clientintegr\controllers\FullmapController;
use Yii;
use yii\data\ArrayDataProvider;
use yii\web\Response;

class SettingsController extends \frontend\modules\clientintegr\controllers\DefaultController
{
    /** @var integer Количество строк (и чекбоксов) в таблице показа Списка доступных товаров в IIKO */
    const SELECTED_PRODUCTS_PAGE_SIZE = 20;
    /** @var integer Индекс, заведомо больший количества строк в таблице показа Списка доступных товаров, используется для передачи информации о состоянии флажка "Выделить все" */
    const SELECTED_PRODUCTS_ALL_INDEX = 101;

    /**
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new iikoDicconstSearch();
        $dataProvider = $searchModel->search([Yii::$app->request->queryParams]);
        $lic = iikoService::getLicense();
        $vi = $lic ? 'index' : '/default/_nolic';
        if (Yii::$app->request->isPjax) {
            return $this->renderPartial($vi, [
                'searchModel'  => $searchModel,
                'dataProvider' => $dataProvider,
                'lic'          => $lic,
            ]);
        } else {
            return $this->render($vi, [
                'searchModel'  => $searchModel,
                'dataProvider' => $dataProvider,
                'lic'          => $lic,
            ]);
        }
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     */
    public function actionChangeConst($id)
    {
        $org = Yii::$app->user->identity->organization_id;
        $pConst = iikoPconst::findOne(['const_id' => $id, 'org' => $org]);

        if (empty($pConst)) {
            $pConst = new iikoPconst();
            $pConst->org = $org;
            $pConst->const_id = $id;
            if (!$pConst->save()) {
                echo "Can't create P Const model (";
                die();
            }
        }

        $lic = iikoService::getLicense();
        $vi = $lic ? 'update' : '/default/_nolic';

        $post = Yii::$app->request->post();
        if (isset($post['sort'])) {
            $sort = $post['sort'];
        } else {
            $sort = 'denom';
        }
        if (isset($post['page'])) {
            $page = $post['page'];
        } else {
            $page = 1;
        }
        if (isset($post['iikoProductSearch']['product_type'])) {
            $productSearch = $post['iikoProductSearch']['product_type'];
        } else {
            $productSearch = 'all';
        }
        if (isset($post['iikoProductSearch']['cooking_place_type'])) {
            $cookingPlaceSearch = $post['iikoProductSearch']['cooking_place_type'];
        } else {
            $cookingPlaceSearch = 'all';
        }
        if (isset($post['iikoProductSearch']['unit'])) {
            $unitSearch = $post['iikoProductSearch']['unit'];
        } else {
            $unitSearch = 'all';
        }

        if (isset($post['selection']) || isset($post['selected_goods'])) {
            $post['iikoPconst']['value'] = $this->handleSelectedProducts($post, $org);
        }

        if (isset($post['Stores'])) {
            $post['iikoPconst']['value'] = $this->handleSelectedStores($post, $org);
        }

        if ($pConst->load($post) && $pConst->save() && $id != 7) {
            /*if ($pConst->getErrors()) {
                var_dump($pConst->getErrors());
                exit;
            }*/
            return $this->redirect(['index']);
        } else {
            $dicConst = iikoDicconst::findOne(['id' => $pConst->const_id]);
            return $this->render($vi, [
                'model'              => $pConst,
                'dicConst'           => $dicConst,
                'id'                 => $id,
                'sort'               => $sort,
                'productSearch'      => $productSearch,
                'cookingPlaceSearch' => $cookingPlaceSearch,
                'unitSearch'         => $unitSearch,
                'page'               => $page,
            ]);
        }

    }

    private function handleSelectedProducts($post, $org)
    {
        if (isset($post['goods'])) {
            $products = $post['goods'];
            $allSelectedProducts = iikoSelectedProduct::findAll(['organization_id' => $org]);
            foreach ($allSelectedProducts as $product) {
                if (isset($products[$product->product_id]) && $products[$product->product_id] == 0) {
                    $product->delete();
                }
            }
            foreach ($products as $productID => $value) {
                if ($value == 0) {
                    continue;
                }
                $selectedProduct = iikoSelectedProduct::findOne(['product_id' => $productID, 'organization_id' => $org]);
                if (!$selectedProduct) {
                    $selectedProduct = new iikoSelectedProduct();
                    $selectedProduct->product_id = $productID;
                    $selectedProduct->organization_id = $org;
                    $selectedProduct->save();
                }
            }
            $count = iikoSelectedProduct::find()->where(['organization_id' => $org])->count();
        } else {
            if (isset($post['selected_goods'])) {
                $allSelectedProducts = iikoSelectedProduct::findAll(['organization_id' => $org]);
                foreach ($allSelectedProducts as &$product) {
                    $product->delete();
                }
                $count = iikoSelectedProduct::find()->where(['organization_id' => $org])->count();
            }
        }
        return $count;
    }

    private function handleSelectedStores($post, $org)
    {
        $stores = $post['Stores'];
        foreach ($stores as $storeID => $selected) {
            $selectedStore = iikoSelectedStore::findOne(['store_id' => $storeID, 'organization_id' => $org]);
            if ($selected == '1') {
                if (!$selectedStore) {
                    $selectedStore = new iikoSelectedStore();
                    $selectedStore->store_id = $storeID;
                    $selectedStore->organization_id = $org;
                    $selectedStore->save();
                }
            } else {
                if ($selectedStore) {
                    $selectedStore->delete();
                }
            }
        }
        return iikoSelectedStore::find()->where(['organization_id' => $org])->count();
    }

    public function actionAjaxAddProductToSession()
    {
        $productID = Yii::$app->request->post('productID');
        $session = Yii::$app->session;
        $session['SelectedProduct.' . $productID] = $productID;
    }

    /**
     * Render collation table
     *
     * @var iikoPconst->const_id $const_id
     */
    public function actionCollations()
    {
        $obConstModel = iikoDicconst::findOne(['denom' => 'main_org']);
        /** @var $currentUser User */
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $currentUserRole = User::findOne(Yii::$app->user->id);
        /**@var $roles array Available roles ids */
        $roles = [Role::ROLE_RESTAURANT_MANAGER, Role::ROLE_ADMIN, Role::ROLE_SUPPLIER_MANAGER, Role::ROLE_FRANCHISEE_LEADER];
        if (in_array($currentUserRole->role_id, $roles)) {
            $arOrgsObj = $currentUser->getAllOrganization();
            $provider = new ArrayDataProvider([
                'allModels'  => $arOrgsObj,
                'pagination' => [
                    'pageSize' => 999,
                ],
                'key'        => 'id'
            ]);

            $arIdsOrgs = [];
            foreach ($arOrgsObj as $org) {
                $arIdsOrgs[] = $org->id;
            }

            $pConst = iikoPconst::findOne(['const_id' => $obConstModel->id, 'org' => $arIdsOrgs]);

            return $this->render('collations', [
                    'provider' => $provider,
                    'parentId' => $pConst,
                ]
            );
        }

        return $this->redirect('index');
    }

    /**
     * Создаём сопоставления в дочерних бизнесах
     *
     * @var iikoPconst->const_id $const_id
     * @return array
     */
    public function actionApplyCollation()
    {
        $obConstModel = iikoDicconst::findOne(['denom' => 'main_org']); // Получаем идентификатор константы бизнеса для сопоставления
        Yii::$app->response->format = Response::FORMAT_JSON;
        $ids = Yii::$app->request->post('ids'); // получаем дочерние бизнесы
        $mainId = Yii::$app->request->post('main'); //получаем главный бизнес
        $arModels = [];

        $arPconstModels = iikoPconst::find()->select('org')->where(['const_id' => $obConstModel->id, 'org' => $ids])->indexBy('org')->all();
        $arDeletedIds = array_keys($arPconstModels); // получаем массив уже имеющихся записей дочек из iiko_pconst

        foreach ($ids as $id) { // создаём массив для вснх дочерних бизнесов
            $pConst = new iikoPconst();
            $pConst->org = $id;
            $pConst->const_id = $obConstModel->id;
            $pConst->value = $mainId;
            $arModels[] = $pConst;
        }

        if (!empty($arDeletedIds)) { // если в iiko_pconst уже есть дочки, то удаляем их все.
            $resDel = $this->actionCancelCollation($arDeletedIds);
        }

        if (empty($arModels) && !empty($arDeletedIds)) { // если нужно только удалить и не нужно добавлять дочек, то возвращаем true
            return $resDel;
        } elseif (empty($arModels) && empty($arDeletedIds)) { // если не нужно ни удалять, ни добавлять дочек, то возвращается ошибка
            return ['success' => false, 'error' => 'Невозможно выполнить данную операцию'];
        }

        $modelCollection = new ModelsCollection();

        $multiple = $modelCollection->saveMultiple($arModels); // пытаемся добавить все дочерние бизнесы и вернуть статус выполнения команды
        if ($multiple['success'] === true) {
            $add_all = FullmapController::actionAddAllChildsProductsFromMain($mainId);
            if ($add_all === true) {
                return ['success' => true, 'error' => ''];
            } else {
                return ['success' => false, 'error' => 'Не удалось сохранить продукт дочернего бизнеса.'];
            }
        } else {
            return $multiple;
        }
    }

    /**
     * Удаляем сопоставления в дочерних бизнесах
     *
     * @var iikoPconst->const_id $const_id
     * @var array $ids for delete
     * @return array
     */
    public function actionCancelCollation($ids = null)
    {
        $obConstModel = iikoDicconst::findOne(['denom' => 'main_org']); // Получаем идентификатор константы бизнеса для сопоставления
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (is_null($ids)) { // если массив удаляемых дочерних бизнесов, переданный параметром, нулевой
            $ids = Yii::$app->request->post('ids'); //то получаем массив дочерних бизнесов из POST
        }
        try {
            $pConst = iikoPconst::deleteAll(['const_id' => $obConstModel->id, 'org' => $ids]); // удалемя все дочерние бизнесы из таблицы iiko_pconst
        } catch (\Throwable $throwable) {
            return ['success' => false, 'error' => $throwable->getMessage()]; // если не удалось, возвращаем ошибку
        }

        return ['success' => true]; // если удалось, возвращаем успешно
    }

    /**
     * Вносит изменения в список доступных товаров
     *
     * @return array
     */
    public function actionChangeSelectedProducts()
    {
        $izmen = []; //создаём массив, в который будут записаны изменения (он понадобится, если что-то пойдёт не так и все изменения в таблице-гриде нужно будет "откатить")
        $uspeh = 'true'; //задаём изначальное значение переменной, отвечающей за состояние успешности операций сохранения и удаления
        $i = 0; //задаём начальное значение переменной-итератору цикла
        $all = 2; //задаём начальное значение переменной, отвечающей за состояние флажка "Выделить все" (0 - снять выделения всех чекбоксов, 1 - установить выделения всех чекбоксов, 2 - вариант, когда в таблице есть и выделенные и невыделенные чекбоксы)
        $org = Yii::$app->user->identity->organization_id; //узнаём идентификатор организации
        $post = Yii::$app->request->post(); //узнаём параметры POST
        $goods = $post['goods']; //выделяем из POST значения чекбоксовь и записываем их в отдельный массив
        $ids = array_keys($goods); //также выделяем в отдельный массив ключи чекбоксов (ключи содержат в себе идентификаторы из столбца ID)
        foreach ($ids as $id) { //в цикле проверяем наличие в таблице iiko_selected_products наличие данных товаров
            $SelectedProduct = iikoSelectedProduct::findOne(['product_id' => $id]);
            (isset($SelectedProduct)) ? $est = 1 : $est = 0; //если данный товар в таблице уже есть $est=1 иначе $est=0
            if (($est == 1) and ($goods[$id]) == 0) { //если товар в таблице БД уже есть, а флажок в таблице-гриде "снят", то пытаемся удалить товар из таблицы БД
                if (!$SelectedProduct->delete()) { //если не удалось удалить товар, то переменная, отвечающая за состояние успешности операций сохранения и удаления, получает значение false
                    $uspeh = false;
                }
                $i++; //увеличиваем на единицу переменную-итератор
                $izmen[$i]['id'] = $id; //записываем в массив изменений значение ключа строки ID
                $izmen[$i]['val'] = 1; //записываем в массив изменений значение действия, которое предстоит сделать с чекбоксом (0 - снять выделение, 1 - выделить)
            }
            if (($est == 0) and ($goods[$id]) == 1) { //если товара в таблице БД нет, а флажок в таблице-гриде "установлен", то пытаемся добавить товар в таблицу БД
                $selectedProduct = new iikoSelectedProduct(); //создаём новый экземпляр класса Доступных товаров
                $selectedProduct->product_id = $id; //устанавливаем значение product_id (идентификатор продукта)
                $selectedProduct->organization_id = $org; //устанавливаем значение organization_id (идентификатор организации)
                if (!$selectedProduct->save()) { //если не удалось сохранить товар, то переменная, отвечающая за состояние успешности операций сохранения и удаления, получает значение false
                    $uspeh = 'false';
                };
                $i++; //увеличиваем на единицу переменную-итератор
                $izmen[$i]['id'] = $id; //записываем в массив изменений значение ключа строки ID
                $izmen[$i]['val'] = 0; //записываем в массив изменений значение действия, которое предстоит сделать с чекбоксом (0 - снять выделение, 1 - выделить)
            }
        }
        if (count($izmen) == self::SELECTED_PRODUCTS_PAGE_SIZE) { //если количество чекбоксов равно количеству строк в таблице-гриде, то есть изменены все чекбоксы на странице, то
            if ((!in_array(0, $izmen)) or (!in_array(1, $izmen))) { //если все чекбоксы содержат одинаковое значение (или все выделены, или все "сняты"),
                (in_array(0, $izmen)) ? $all = 0 : $all = 1; //узнаём значение всех чекбоксов и записываем его в переменную, отвечающую за состояние флажка "Выделить все"
            }
        }
        if ($uspeh == 'true') { //если все операции добавления новых значений и удаления ненужных в таблицу БД прошли успешно, то
            $pConst = iikoPconst::findOne(['const_id' => 7, 'org' => $org]);
            $count = iikoSelectedProduct::find()->where(['organization_id' => $org])->count(); //узнаём количество доступных товаров для данной организации
            $pConst->value = $count;
            $pConst->save(); //и сохраняем это значение в таблице iiko_pconst
        }
        $izmen[self::SELECTED_PRODUCTS_ALL_INDEX]['id'] = $uspeh; //записываем в массив изменений значение успешности операций добавления и удаления товаров
        $izmen[self::SELECTED_PRODUCTS_ALL_INDEX]['val'] = $all; //записываем в массив изменений значение переменной, отвечающей за состояние флажка "Выделить все"
        $izmen = json_encode($izmen); //кодируем массив изменений в JSON
        return $izmen;
    }

}
