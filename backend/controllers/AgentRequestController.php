<?php

namespace backend\controllers;

use backend\models\OrganizationSearch;
use common\models\AgentRequest;
use common\models\FranchiseeAssociate;
use common\models\Organization;
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
        $query = (new Query())->select(['agent_request.id AS id', 'agent_request.target_email AS target_email', 'agent_request.comment AS comment', 'agent_request.created_at AS created_at', 'franchisee.signed AS signed', 'franchisee.legal_entity AS legal_entity'])
            ->from('agent_request')
            ->leftJoin('franchisee_user', 'franchisee_user.user_id=agent_request.agent_id')
            ->leftJoin('franchisee', 'franchisee.id=franchisee_user.franchisee_id')
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
        $dataProvider = null;
        $query = (new Query())->select(['organization.name AS name', 'organization.id AS id', 'organization.email AS email', 'franchisee.signed AS signed', 'franchisee.legal_entity AS legal_entity', 'franchisee_associate.agent_id AS agent_id', 'franchisee_associate.franchisee_id AS franchisee_id'])
            ->from('organization')
            ->leftJoin('franchisee_associate', 'franchisee_associate.organization_id=organization.id')
            ->leftJoin('franchisee', 'franchisee.id=franchisee_associate.franchisee_id')
            ->where(['organization.name' => $model->comment])->orWhere(['organization.email' => $model->target_email]);
        $searchModel = new OrganizationSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);

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
            $franchisee = FranchiseeAssociate::findOne(['organization_id' => $org_id]);
            $franchisee->agent_id = $agent_id;
            $franchisee->franchisee_id = $franchisee_id;
            $franchisee->save();
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
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
