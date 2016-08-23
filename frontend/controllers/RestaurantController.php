<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use common\models\User;
use common\models\Role;
use common\models\Profile;
use common\models\Organization;
use common\models\RelationCategory;
use common\models\Category;
use common\models\Catalog;
use common\models\RelationSuppRest;
use common\models\CatalogBaseGoods;
use common\models\CatalogGoods;
use yii\helpers\ArrayHelper;
use yii\web\Response;

class RestaurantController extends Controller {

    public function actionIndex() {
        $user = new User;
        $profile = new Profile();
        $relation_category = new RelationCategory;
        $organization = new Organization;
        return $this->render("index", compact("user", "profile", "organization", "relation_category"));
    }

    public function actionChkmail() {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            if (User::find()->select('email')->where(['email' => \Yii::$app->request->post('email')])->exists()) {
                $email = \Yii::$app->request->post('email');
                $user = User::find()->where(['email' => $email])->one();
                //$organization = Organization::find()->select('name')->where(['id' => $user['organization_id']])->one();
                $result = ['success' => true, 'message' => 'Есть совпадение по Email!', 'fio' => $user->profile->full_name, 'organization' => $user->organization->name];
                return $result;
            } else {
                $result = ['success' => false, 'message' => 'Нет совпадений по Email!'];
                return $result;
                exit;
            }
        } else {
            $result = ['success' => false, 'message' => 'err: форма передана не ajax-ом!'];
            return $result;
            exit;
        }
    }

    public function actionCreate() {
        $user = new User;
        $profile = new Profile();
        $relationCategory = new RelationCategory;
        $organization = new Organization;
        //$catalog = new Catalog;
        //$category = new Category;
        //$relationSuppRest = new RelationSuppRest;
        //$catalogBaseGoods = new CatalogBaseGoods;
        //$catalogGoods = new CatalogGoods;

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            //$postProfile = Yii::$app->request->post('profile');
            $postCatalog = Yii::$app->request->post('catalog');

            //$arrProfile = json_decode($postProfile, JSON_UNESCAPED_UNICODE);
            $arrCatalog = json_decode($postCatalog, JSON_UNESCAPED_UNICODE);
            if ($arrCatalog === Array()) {
                $result = ['success' => false, 'message' => 'err: Каталог пустой!'];
                return $result;
                exit;
            }
            //$email = $arrProfile[0]['profile']['email'];
            //$fio = $arrProfile[0]['profile']['username'];
            //$org = $arrProfile[0]['profile']['organization'];

            $post = Yii::$app->request->post();
            $user->load($post);
            $profile->load($post);
            $organization->load($post);
            $organization->type_id = \common\models\OrganizationType::TYPE_SUPPLIER;
            $relationCategory->load($post);
            $currentUser = User::findIdentity(Yii::$app->user->id);

            //$categorys = $arrProfile[0]['profile']['category'];
            //if($user->validate()/* && $organization->validate() && $relation_category->validate()*/){  
            if (!User::find()->where(['email' => $user->email])->exists()) {
                /* $transaction=Yii::$app->db->beginTransaction();
                  try
                  { */
//                $user->username = $fio;
//                $user->email = $email;
//                $user->save();
//                $id_user = $user->id;
//
//                $organization->name = $org;
//                $organization->type_id = '2';
//                $organization->save();
//                $id_org = $organization->id;
//
//                $user->organization_id = $id_org;
//                $user->save();

                if ($user->validate() && $profile->validate() && $organization->validate()) {
                    $user->setRegisterAttributes(Role::getManagerRole($organization->type_id))->save();
                    $profile->setUser($user->id)->save();
                    $organization->save();
                    $user->setOrganization($organization->id)->save();

                    $currentUser->sendInviteToVendor($user);
                    
                    
                    //вынеси обработку каталога в отдельный метод
                    //
                    //save() не позволяет работать с более чем 1 строкой или надо переопределять модель каждый раз когда нужно добвить строку
                    //поэтому так:
                    /*
                      \Yii::$app->db->createCommand()->batchInsert(Catalog::tableName(), ['name', 'org_supp_id', 'type'], [
                      ['default', $id_org, 1],
                      ['default', $id_org, 2],
                      ])->execute();
                      //last insert id не получить банчем или выполнять по очереди, что будет кастылем
                      //подумаю над реализацией, пока сделаю прямой запрос
                     */
                    $sql = "insert into " . Catalog::tableName() . "(`name`,`org_supp_id`,`type`) VALUES ('default',$organization->id,1)";
                    \Yii::$app->db->createCommand($sql)->execute();
                    $lastInsert_base_cat_id = Yii::$app->db->getLastInsertID();
                    /**
                     *
                     * добавляем каталог для ресторана на основе базового
                     *    
                     * */
                    $sql = "insert into " . Catalog::tableName() . "(`name`,`org_supp_id`,`type`) VALUES ('default',$organization->id,2)";
                    \Yii::$app->db->createCommand($sql)->execute();
                    $lastInsert_cat_id = Yii::$app->db->getLastInsertID();

                    $rest_org_id = $currentUser->organization->id; //Yii::$app->user->id;
                    //$relationCategory->category = $categorys;
                    $relationCategory->relation_rest_supp_id = $rest_org_id;
                    //$relationCategory->relation_supp_rest_id = $id_org;
                    $relationCategory->save();

                    foreach ($arrCatalog as $arrCatalogs) {
                        $article = htmlspecialchars(trim($arrCatalogs['dataItem']['article']));
                        $product = htmlspecialchars(trim($arrCatalogs['dataItem']['product']));
                        $units = htmlspecialchars(trim($arrCatalogs['dataItem']['units']));
                        $price = htmlspecialchars(trim($arrCatalogs['dataItem']['price']));
                        $note = htmlspecialchars(trim($arrCatalogs['dataItem']['note']));
                        $sql = "insert into " . CatalogBaseGoods::tableName() . "(`cat_id`,`category_id`,`article`,`product`,`units`,`price`) VALUES ($lastInsert_base_cat_id,1,'$article','$product','$units','$price')";
                        \Yii::$app->db->createCommand($sql)->execute();
                        $lastInsert_base_goods_id = Yii::$app->db->getLastInsertID();
                        $sql = "insert into " . CatalogGoods::tableName() . "(`cat_id`,`cat_base_goods_id`,`article`,`product`,`units`,`price`,`note`) VALUES ($lastInsert_cat_id,$lastInsert_base_goods_id,'$article','$product','$units','$price','$note')";
                        \Yii::$app->db->createCommand($sql)->execute();
                    }
                    /*
                      }
                      $transaction->commit();//коммитим наш запрос
                      }
                      catch(Exception $e)  //в случае возникновения ошибки при выполнении одного из запросов выбрасывается исключение
                      {
                      $transaction->rollback();
                      $result = ['success'=>false,'message'=>'err: не удалось добавить в бд'];
                      exit;
                      } */
                    $result = ['success' => true, 'message' => 'Валидация пройдена, создание нового пользователя и каталога!'];
                    return $result;
                }
                $result = ['success' => false, 'message' => 'err: Что-то тут не так, шеф!'];
                return $result;
            } else {
                $result = ['success' => false, 'message' => 'err: User уже есть в базе! Банить юзера за то, что вылезла подобная ошибка))!'];
                return $result;
                exit;
            }
            //}else{
            //$result = ['success'=>false,'message'=>'err: Валидация не пройдена! Заполнены не все необходимые поля!'];  
            //return $result; 
            //exit; 
            //}
        } else {
            $result = ['success' => false, 'message' => 'err: форма передана не ajax-ом!'];
            return $result;
            exit;
        }
    }

    public function actionInvite() {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            ///////
        }
    }

}
