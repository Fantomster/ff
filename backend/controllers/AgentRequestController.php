<?php

namespace backend\controllers;

use backend\models\UserSearch;
use common\models\AgentRequest;
use common\models\FranchiseeAssociate;
use common\models\Role;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\components\AccessRule;
use yii\web\NotFoundHttpException;

/**
 * FranchiseeController implements the CRUD actions for Franchisee model.
 */
class AgentRequestController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'delete-user' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'link'],
                        'allow' => true,
                        'roles' => [Role::ROLE_ADMIN],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Franchisee models.
     * @return mixed
     */
    public function actionIndex()
    {
        $query = (new Query())->select(['agent_request.id AS id', 'agent_request.target_email AS target_email', 'agent_request.comment AS comment', 'agent_request.created_at AS created_at', 'user.email AS user_email', 'profile.full_name AS full_user_name'])
            ->from('agent_request')
            ->leftJoin('user', 'user.id=agent_request.agent_id')
            ->leftJoin('profile', 'profile.user_id=agent_request.agent_id')
            ->where(['agent_request.is_processed'=>0])
            ->orderBy('agent_request.id');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single AgentRequest model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = AgentRequest::findOne($id);
        if(!$model){
            throw new NotFoundHttpException(Yii::t('error', 'backend.controllers.agent_error', ['ru'=>'The requested page does not exist.']));
        }
        $searchModel = new UserSearch();
        if(!isset(\Yii::$app->request->queryParams['UserSearch'])){
            $params['UserSearch']['email'] = $model->target_email;
            $dataProvider = $searchModel->search($params);
        }else{
            $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);
        }
        return $this->render('view', compact('model', 'searchModel', 'dataProvider'));
    }

    /**
     * Displays a single AgentRequest model.
     * @param integer $id
     * @return mixed
     */
    public function actionLink($id, $org_id, $franchisee_id=null, $agent_id=null)
    {
        if ($franchisee_id && $agent_id && $org_id) {
            $franchiseeAssociate = FranchiseeAssociate::findOne(['organization_id' => $org_id]);
            if($franchiseeAssociate==null){
                $franchiseeAssociate = new FranchiseeAssociate();
            }
            $franchiseeAssociate->agent_id = $agent_id;
            $franchiseeAssociate->franchisee_id = $franchisee_id;
            $franchiseeAssociate->organization_id = $org_id;
            $franchiseeAssociate->save();
        }
        $model = AgentRequest::findOne($id);
        $model->is_processed = true;
        $model->save();
        return $this->redirect('/agent-request/index');
    }

    /**
     * Finds the AgentRequest model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return AgentRequest the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $query = (new Query())->select(['agent_request.id AS id', 'agent_request.target_email AS target_email', 'agent_request.comment AS comment', 'agent_request.created_at AS created_at', 'agent_request.is_processed AS is_processed', 'franchisee.signed AS signed', 'franchisee.legal_entity AS legal_entity'])
            ->from('agent_request')
            ->leftJoin('franchisee_user', 'franchisee_user.user_id=agent_request.agent_id')
            ->leftJoin('franchisee', 'franchisee.id=franchisee_user.franchisee_id')
            ->where(['agent_request.id' => $id]);
        if ($query !== null) {
            return $query;
        } else {
            throw new NotFoundHttpException(Yii::t('error', 'backend.controllers.agent_error_two', ['ru'=>'The requested page does not exist.']));
        }
    }
}
