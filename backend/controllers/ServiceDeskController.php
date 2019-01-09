<?php

namespace backend\controllers;

use common\models\OperatorCall;
use common\models\search\OperatorReportFastSearch;
use common\models\search\OperatorReportSearch;
use console\controllers\CronController;
use Yii;
use common\models\Organization;
use common\models\forms\ServiceDesk;
use common\models\Role;
use backend\models\OrganizationSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\components\AccessRule;
use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\ServiceRequestFactory;
use Google\Spreadsheet\SpreadsheetService;

define('CLIENT_APP_NAME', 'sonorous-dragon-167308');
define('SERVICE_ACCOUNT_CLIENT_ID', '114798227950751078238');
define('SERVICE_ACCOUNT_EMAIL', 'f-keeper@sonorous-dragon-167308.iam.gserviceaccount.com');
define('SERVICE_ACCOUNT_PKCS12_FILE_PATH', Yii::getAlias('@common') . '/google/GoogleApiDocs-356b554846a5.p12');
define('CLIENT_KEY_PW', 'notasecret');

/**
 * OrganizationController implements the CRUD actions for Organization model.
 */
class ServiceDeskController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class'      => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules'      => [
                    [
                        'actions' => ['index'],
                        'allow'   => true,
                        'roles'   => [
                            Role::ROLE_ADMIN,
                        ],
                    ],
                    [
                        'actions'       => ['operators-report', 'fast-operators-report', 'test'],
                        'allow'         => true,
                        'matchCallback' => function ($rule, $action) {
                            return in_array(Yii::$app->user->id, Yii::$app->params['operatorsReportAdminIDs']);
                        }
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Organization models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new ServiceDesk();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $objClientAuth = new \Google_Client ();
            $objClientAuth->setApplicationName(CLIENT_APP_NAME);
            $objClientAuth->setClientId(SERVICE_ACCOUNT_CLIENT_ID);
            $objClientAuth->setAssertionCredentials(new \Google_Auth_AssertionCredentials(
                SERVICE_ACCOUNT_EMAIL, ['https://spreadsheets.google.com/feeds', 'https://docs.google.com/feeds'], file_get_contents(SERVICE_ACCOUNT_PKCS12_FILE_PATH), CLIENT_KEY_PW
            ));
            /* putenv('GOOGLE_APPLICATION_CREDENTIALS='. SERVICE_ACCOUNT_JSON_FILE_PATH);
              $objClientAuth->useApplicationDefaultCredentials();
              $objClientAuth->addScope(Google_Service_Drive::DRIVE);
              var_dump($objClientAuth); */

            $objClientAuth->getAuth()->refreshTokenWithAssertion();
            $objToken = json_decode($objClientAuth->getAccessToken());

            $accessToken = $objToken->access_token;

            /**
             * Initialize the service request factory
             */
            $serviceRequest = new DefaultServiceRequest($accessToken);
            ServiceRequestFactory::setInstance($serviceRequest);

            /**
             * Get spreadsheet by title
             */
            $spreadsheetTitle = 'f-keeper';
            $spreadsheetService = new SpreadsheetService();
            $spreadsheetFeed = $spreadsheetService->getSpreadsheetFeed();
            $spreadsheet = $spreadsheetFeed->getByTitle($spreadsheetTitle);
            /**
             * Get particular worksheet of the selected spreadsheet
             */
            $worksheetTitle = 'ServiceDesk';
            $worksheetFeed = $spreadsheet->getWorksheetFeed();
            $worksheet = $worksheetFeed->getByTitle($worksheetTitle);
            $listFeed = $worksheet->getListFeed();

            $listFeed->insert([
                'author'        => 'Менеджер',
                'region'        => $model->locality,
                'fio'           => $model->fio,
                'phone'         => $model->phone,
                'message'       => $model->body,
                'startdatetime' => date("Y-m-d H:i:s")
            ]);
            if (Yii::$app->request->isPjax) {
                return $this->renderAjax('index', [
                    'model' => $model,
                ]);
            }
        }
        return $this->render('index', [
            'model' => $model,
        ]);
    }

    /**
     * Страница оператора заказов
     *
     * @return string
     */
    public function actionOperatorsReport()
    {
        $searchModel = new OperatorReportSearch();
        $searchModel->user_id = \Yii::$app->user->getId();
        $params = Yii::$app->request->queryParams;
        $searchModel->load($params);
        $filterValues['date_from'] = $params['date_from'] ?? ((new \DateTime('-7 day'))->format('d-m-Y'));
        $filterValues['date_to'] = $params['date_to'] ?? ((new \DateTime())->format('d-m-Y'));
        $dataProvider = $searchModel->search($params);

        return $this->render('operator', ['dataProvider' => $dataProvider, 'searchModel' => $searchModel, 'filterValues' => $filterValues]);
    }

    /**
     * Страница оператора заказов
     *
     * @return string
     */
    public function actionFastOperatorsReport()
    {
        $searchModel = new OperatorReportFastSearch();
        $searchModel->user_id = \Yii::$app->user->getId();
        $params = Yii::$app->request->queryParams;
        $searchModel->load($params);
        $filterValues['date_from'] = $params['date_from'] ?? ((new \DateTime('-7 day'))->format('d-m-Y'));
        $filterValues['date_to'] = $params['date_to'] ?? ((new \DateTime())->format('d-m-Y'));
        $dataProvider = $searchModel->search($params);

        return $this->render('operator-fast', ['dataProvider' => $dataProvider, 'searchModel' => $searchModel, 'filterValues' => $filterValues]);
    }

    //метод для теста - симуляция запуска крон
    public function actionTest()
    {
        $cron = new CronController(1, 1);
        $cron->actionHandleFiles();
        $cron->actionHandleFilesQueue();
        echo 'success';
    }

}
