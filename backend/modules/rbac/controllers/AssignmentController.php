<?php
/**
 * Created by PhpStorm.
 * Developer: Arsen
 * Vk: https://vk.com/a.arsik
 * Inst: https://www.instagram.com/arsen.web/
 * Date: 2019-02-05
 * Time: 12:01
 */

namespace backend\modules\rbac\controllers;

use common\models\rbac\AssignmentModel;
use backend\modules\rbac\helpers\RbacHelper;
use common\models\rbac\search\AssignmentSearch;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class AssignmentController extends Controller
{
    /**
     * @var \yii\web\IdentityInterface the class name of the [[identity]] object
     */
    public $userIdentityClass;

    /**
     * @var string search class name for assignments search
     */
    public $searchClass = [
        'class' => AssignmentSearch::class,
    ];

    /**
     * @var string id column name
     */
    public $idField = 'id';

    /**
     * @var string username column name
     */
    public $usernameField = 'username';

    /**
     * @var array assignments GridView columns
     */
    public $gridViewColumns = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->userIdentityClass === null) {
            $this->userIdentityClass = Yii::$app->user->identityClass;
        }

        if (empty($this->gridViewColumns)) {
            $this->gridViewColumns = [
                $this->idField,
                $this->usernameField,
            ];
        }
    }

    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return [
            'verbs'             => [
                'class'   => 'yii\filters\VerbFilter',
                'actions' => [
                    'index'  => ['get'],
                    'view'   => ['get'],
                    'assign' => ['post'],
                    'remove' => ['post'],
                ],
            ],
            'contentNegotiator' => [
                'class'   => 'yii\filters\ContentNegotiator',
                'only'    => ['assign', 'remove'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ];
    }

    /**
     * Displays a single Assignment model.
     *
     * @param int $id
     * @param     $orgId
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionView(int $id, $orgId = null)
    {
        $model = $this->findModel($id);
        $orgList = RbacHelper::getOrgByUserId($id);

        if (!empty($orgList) && is_null($orgId)) {
            $orgId = key($orgList);
        }

        return $this->render('view', [
            'user'          => $model->user,
            'items'         => $model->getUserItemsByOrg($orgId),
            'usernameField' => $this->usernameField,
            'orgId'         => $orgId,
            'orgList'       => $orgList
        ]);
    }

    /**
     * Assign items
     *
     * @param int $id
     * @param int $orgId
     * @return Response
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \Exception
     */
    public function actionAssign(int $id, int $orgId)
    {
        $items = Yii::$app->getRequest()->post('items', []);
        $assignmentModel = $this->findModel($id);
        $assignmentModel->assignUserByOrg($items, $orgId);

        return $this->redirect([
            'assignment/view',
            'id'    => $id,
            'orgId' => $orgId
        ]);
    }

    /**
     * Remove items
     *
     * @param int $id
     * @param int $orgId
     * @return Response
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionRemove(int $id, int $orgId)
    {
        $items = Yii::$app->getRequest()->post('items', []);
        $assignmentModel = $this->findModel($id);
        $assignmentModel->revokeUserByOrg($items, $orgId);

        return $this->redirect([
            'assignment/view',
            'id'    => $id,
            'orgId' => $orgId
        ]);
    }

    /**
     * Finds the Assignment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id
     * @return AssignmentModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     * @throws \yii\base\InvalidConfigException
     */
    protected function findModel(int $id)
    {
        $class = $this->userIdentityClass;

        if (($user = $class::findIdentity($id)) !== null) {
            return new AssignmentModel($user);
        }

        throw new NotFoundHttpException(Yii::t('yii2mod.rbac', 'The requested page does not exist.'));
    }
}
