<?php

namespace api\modules\v1\modules\mobile\controllers;

use api\modules\v1\modules\mobile\resources\RequestCallback;
use api\modules\v1\modules\mobile\resources\RequestCounters;
use common\models\Organization;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use api\modules\v1\modules\mobile\resources\Request;
use yii\data\ActiveDataProvider;
use api\modules\v1\modules\mobile\models\User;
use common\models\Role;
use yii\web\ServerErrorHttpException;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class RequestController extends ActiveController
{

    /**
     * @var string
     */
    public $modelClass = 'api\modules\v1\modules\mobile\resources\Request';

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors = array_merge($behaviors, $this->module->controllerBehaviors);

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'index'  => [
                'class'               => 'yii\rest\IndexAction',
                'modelClass'          => $this->modelClass,
                'prepareDataProvider' => [$this, 'prepareDataProvider']
            ],
            /*'update' => [
                'class' => 'yii\rest\UpdateAction',
                'modelClass' => 'common\models\Request',
                'checkAccess' => [$this, 'checkAccess'],
                'scenario' => $this->updateScenario,
            ],*/
            'create' => [
                'class'       => 'yii\rest\CreateAction',
                'modelClass'  => 'common\models\Request',
                'checkAccess' => [$this, 'checkAccess'],
                'scenario'    => $this->createScenario,
            ],
            'view'   => [
                'class'       => 'yii\rest\ViewAction',
                'modelClass'  => $this->modelClass,
                'findModel'   => [$this, 'findModel'],
                'checkAccess' => [$this, 'checkAccess'],
            ],
        ];
    }

    /**
     * @param $id
     * @return Request
     * @throws NotFoundHttpException
     */
    public function findModel($id)
    {
        $model = Request::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException;
        }

        return $model;
    }

    /**
     * @return ActiveDataProvider
     * @throws \Throwable
     */
    public function prepareDataProvider()
    {
        $params = new Request();
        $params->setAttributes(Yii::$app->request->queryParams);
        /** @var User $user */
        $user = Yii::$app->user->getIdentity();
        $org = $user->organization;

        $request_counters = (new Query())
            ->select('count(*)')
            ->from(RequestCounters::tableName())
            ->where('request_id = request.id')
            ->createCommand()
            ->getRawSql();

        $request_callback = (new Query())
            ->select('count(*)')
            ->from(RequestCallback::tableName())
            ->where('request_id = request.id')
            ->createCommand()
            ->getRawSql();

        $query = Request::find()
            ->select([
                "*",
                "({$request_counters}) as views",
                "({$request_callback}) as callbacks"
            ]);

        if ($org->type_id == Organization::TYPE_RESTAURANT) {
            $query->andWhere(['rest_org_id' => $org->id]);
        }

        $query->andFilterWhere([
            'id'                      => $params->id,
            'category'                => $params->category,
            'product'                 => $params->product,
            'comment'                 => $params->comment,
            'regular'                 => $params->regular,
            'amount'                  => $params->amount,
            'rush_order'              => $params->rush_order,
            'payment_method'          => $params->payment_method,
            'deferment_payment'       => $params->deferment_payment,
            'responsible_supp_org_id' => $params->responsible_supp_org_id,
            'count_views'             => $params->count_views,
            'created_at'              => $params->created_at,
            'end'                     => $params->end,
            'active_status'           => $params->active_status
        ]);

        return new ActiveDataProvider([
            'query'      => $query,
            'pagination' => [
                'page'     => isset($params->page) ? ($params->page - 1) : 0,
                'pageSize' => isset($params->count) ? $params->count : null,
            ],
        ]);
    }

    /**
     * Checks the privilege of the current user.
     * This method should be overridden to check whether the current user has the privilege
     * to run the specified action against the specified data model.
     * If the user does not have access, a [[ForbiddenHttpException]] should be thrown.
     *
     * @param string          $action the ID of the action to be executed
     * @param \yii\base\Model $model  the model to be accessed. If `null`, it means no specific model is being accessed.
     * @param array           $params additional parameters
     * @throws \yii\web\ForbiddenHttpException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        // check if the user can access $action and $model
        // throw ForbiddenHttpException if access should be denied
        if ($action === 'update' || $action === 'delete' || $action === 'view') {
            /** @var \common\models\User $user */
            $user = Yii::$app->user->identity;

            if ($model->rest_org_id !== $user->organization_id)
                throw new \yii\web\ForbiddenHttpException(sprintf('You can only %s articles that you\'ve created.', $action));
        }
    }

    /**
     * @param $id
     * @return ActiveRecord
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionRemoveSupply($id)
    {
        /* @var $model ActiveRecord */
        $model = $this->findModel($id);

        $this->checkAccess('update', $model);

        $supp_org_id = $model->responsible_supp_org_id;

        $model->scenario = $this->updateScenario;
        $model->responsible_supp_org_id = null;
        if ($model->save() === false && !$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
        }

        $request = $model;

        //Тут пошли уведомления
        //Для начала подготовим текст уведомлений и шаблоны email
        $subject = Yii::t('app', 'api.modules.v1.modules.mobile.controllers.req', ['ru' => "mixcart.ru - заявка №%s"]);
        $sms_text = Yii::t('app', 'api.modules.v1.modules.mobile.controllers.you_dismissed', ['ru' => 'Вы сняты с исполнения по заявке №%s']);
        $email_template = 'requestSetResponsibleMailToSuppReject';
        $client_email_template = 'requestSetResponsibleReject';

        //Данные тексты для рассылки
        $templateMessage = [
            'sms_text'              => sprintf($sms_text, $request->id),
            'email_template'        => $email_template,
            'email_subject'         => sprintf($subject, $request->id),
            'client_email_template' => $client_email_template
        ];
        //Для начала соберем сотрудников постовщика, которым необходимо разослать уведомления
        //Это руководители, и сотрудник который создал отклик
        $vendor_users = User::find()->where([
            'role_id' => Role::ROLE_SUPPLIER_MANAGER
        ])/*->orWhere([
                    'id' => $request_callback->supp_user_id
                ])*/
        ->andWhere([
            'organization_id' => $supp_org_id,
            'status'          => User::STATUS_ACTIVE
        ])->all();

        if (!empty($vendor_users)) {
            //Поехали рассылать
            foreach ($vendor_users as $user) {
                //Отправляем смс поставщику, о принятии решения по его отклику
                if ($user->profile->phone && $user->getSmsNotification($supp_org_id)->request_accept == 1) {
                    Yii::$app->sms->send($templateMessage['sms_text'], $user->profile->phone);
                }
                //Отправляем емайлы поставщику, о принятии решения по его отклику
                if ($user->email && $user->getEmailNotification($supp_org_id)->request_accept == 1) {
                    $mailer = Yii::$app->mailer;
                    $mailer->htmlLayout = 'layouts/request';
                    $mailer->compose($templateMessage['email_template'], [
                        "request" => $request,
                        "vendor"  => $user
                    ])->setTo($user->email)
                        ->setSubject($templateMessage['email_subject'])
                        ->send();
                }
            }
        }
        //Так же необходимо отправить емейлы, на доп.адреса
        //только те, которые хотят получать эти уведомления
        /* $additional_email = AdditionalEmail::find()->where([
             'organization_id' => $request->supp_org_id,
             'request_accept' => 1
         ])->all();
         //Если есть такие емайлы, шлем туда
         if (!empty($additional_email)) {
             $vendor = User::findOne($request_callback->supp_user_id);
             foreach ($additional_email as $add_email) {
                 $mailer = Yii::$app->mailer;
                 $mailer->htmlLayout = 'layouts/request';
                 $mailer->compose($templateMessage['email_template'], compact("request", "vendor"))
                     ->setTo($add_email->email)
                     ->setSubject($templateMessage['email_subject'])
                     ->send();
             }
         }      */

        return $model;
    }

    /**
     * @param $id
     * @return ActiveRecord
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionUpdate($id)
    {
        /* @var $model ActiveRecord */
        $model = $this->findModel($id);

        $this->checkAccess('update', $model);

        $model->scenario = $this->updateScenario;
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->save() === false && !$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
        }

        if ($model->responsible_supp_org_id == null)
            return $model;

        $request = $model;

        //Тут пошли уведомления
        //Для начала подготовим текст уведомлений и шаблоны email
        $sms_text = Yii::t('app', 'api.modules.v1.modules.mobile.controllers.you_set', ['ru' => "Вы назначены исполнителем по заявке №%s"]);
        $subject = Yii::t('app', 'api.modules.v1.modules.mobile.controllers.mix', ['ru' => "mixcart.ru - заявка №%s"]);
        $email_template = 'requestSetResponsibleMailToSupp';
        $client_email_template = 'requestSetResponsible';

        //Данные тексты для рассылки
        $templateMessage = [
            'sms_text'              => sprintf($sms_text, $request->id),
            'email_template'        => $email_template,
            'email_subject'         => sprintf($subject, $request->id),
            'client_email_template' => $client_email_template
        ];
        //Для начала соберем сотрудников постовщика, которым необходимо разослать уведомления
        //Это руководители, и сотрудник который создал отклик
        $vendor_users = User::find()->where([
            'role_id' => Role::ROLE_SUPPLIER_MANAGER
        ])/*->orWhere([
                    'id' => $request_callback->supp_user_id
                ])*/
        ->andWhere([
            'organization_id' => $model->responsible_supp_org_id,
            'status'          => User::STATUS_ACTIVE
        ])->all();

        if (!empty($vendor_users)) {
            //Поехали рассылать
            foreach ($vendor_users as $user) {
                //Отправляем смс поставщику, о принятии решения по его отклику
                if ($user->profile->phone && $user->getSmsNotification($model->responsible_supp_org_id)->request_accept == 1) {
                    Yii::$app->sms->send($templateMessage['sms_text'], $user->profile->phone);
                }
                //Отправляем емайлы поставщику, о принятии решения по его отклику
                if ($user->email && $user->getEmailNotification($model->responsible_supp_org_id)->request_accept == 1) {
                    $mailer = Yii::$app->mailer;
                    $mailer->htmlLayout = 'layouts/request';
                    $mailer->compose($templateMessage['email_template'], [
                        "request" => $request,
                        "vendor"  => $user
                    ])->setTo($user->email)
                        ->setSubject($templateMessage['email_subject'])
                        ->send();
                }
            }
        }
        //Так же необходимо отправить емейлы, на доп.адреса
        //только те, которые хотят получать эти уведомления
        /*   $additional_email = AdditionalEmail::find()->where([
               'organization_id' => $request->responsible_supp_org_id,
               'request_accept' => 1
           ])->all();
           //Если есть такие емайлы, шлем туда
           if (!empty($additional_email)) {
               $vendor = User::findOne($request_callback->supp_user_id);
               foreach ($additional_email as $add_email) {
                   $mailer = Yii::$app->mailer;
                   $mailer->htmlLayout = 'layouts/request';
                   $mailer->compose($templateMessage['email_template'], compact("request", "vendor"))
                       ->setTo($add_email->email)
                       ->setSubject($templateMessage['email_subject'])
                       ->send();
               }
           }       */

        return $model;
    }
}
