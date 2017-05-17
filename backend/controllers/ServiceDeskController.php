<?php

namespace backend\controllers;

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



define('SERVICE_ACCOUNT_CLIENT_ID', '114798227950751078238');
define('SERVICE_ACCOUNT_EMAIL', 'f-keeper@sonorous-dragon-167308.iam.gserviceaccount.com');
define('SERVICE_ACCOUNT_PKCS12_FILE_PATH', Yii::getAlias('@common') . '/google/GoogleApiDocs-356b554846a5.p12');
define('CLIENT_KEY_PW', 'notasecret');

/**
 * OrganizationController implements the CRUD actions for Organization model.
 */
class ServiceDeskController extends Controller {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => [
                            Role::ROLE_ADMIN,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Organization models.
     * @return mixed
     */
    public function actionIndex() {
        $model = new ServiceDesk();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $accessToken = self::getGoogleTokenFromKeyFile(SERVICE_ACCOUNT_CLIENT_ID, SERVICE_ACCOUNT_EMAIL, SERVICE_ACCOUNT_PKCS12_FILE_PATH);
$serviceRequest = new DefaultServiceRequest($accessToken);
            //$serviceRequest->setSslVerifyPeer(false);
            ServiceRequestFactory::setInstance($serviceRequest);
            
            $spreadsheetService = new \Google\Spreadsheet\SpreadsheetService();
            $worksheetFeed = $spreadsheetService->getPublicSpreadsheet("19vqYJCAQBGPNLuyJpd4jL6O7MT4CxHUhzC2tCvfUtPQ");
            $worksheet = $worksheetFeed->getByTitle('list1');
            $listFeed = $worksheet->getListFeed();
            $serviceRequest = new DefaultServiceRequest($accessToken);
            //$serviceRequest->setSslVerifyPeer(false);
            ServiceRequestFactory::setInstance($serviceRequest);
            
            $spreadsheetService = new \Google\Spreadsheet\SpreadsheetService();
            $worksheetFeed = $spreadsheetService->getPublicSpreadsheet("19vqYJCAQBGPNLuyJpd4jL6O7MT4CxHUhzC2tCvfUtPQ");
            $worksheet = $worksheetFeed->getByTitle('list1');
            $listFeed = $worksheet->getListFeed();
            
            foreach ($listFeed->getEntries() as $entries) {
                var_dump($entries->getValues()); 
            }
//            var_dump($listFeed);
//            $data = ['фио' => 'john_doe'];
            $listFeed->insert(['fio' => 'aaaaaaa']);
            /*$spreadsheetService = new \Google\Spreadsheet\SpreadsheetService();
            $spreadsheetFeed = $spreadsheetService->getSpreadsheets();

            $spreadsheet = $spreadsheetFeed->getByTitle('19vqYJCAQBGPNLuyJpd4jL6O7MT4CxHUhzC2tCvfUtPQ');

            $worksheetFeed = $spreadsheet->getWorksheets();
            $worksheet = $worksheetFeed->getByTitle('list1');
            $listFeed = $worksheet->getListFeed();
            var_dump($listFeed);*/
        }
            return $this->render('index', [
                'model' => $model,
            ]);
    }
    protected function getGoogleTokenFromKeyFile($clientId, $clientEmail, $pathToP12File) {
    
    $client = new \Google_Client();
    $client->setClientId($clientId);

    $cred = new \Google_Auth_AssertionCredentials(
        $clientEmail,
        array('https://spreadsheets.google.com/feeds','https://docs.google.com/feeds'),
        file_get_contents($pathToP12File),
        CLIENT_KEY_PW
    );
    
    $client->setAssertionCredentials($cred);
    if ($client->getAuth()->isAccessTokenExpired()) {
        $client->getAuth()->refreshTokenWithAssertion($cred);
    }

    $service_token = json_decode($client->getAccessToken());
    return $service_token->access_token;
}
}
