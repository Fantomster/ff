<?php

namespace api_web\modules\integration\classes\sync;

use api_web\components\Registry;
use api_web\modules\integration\classes\documents\WaybillContent;
use api_web\modules\integration\classes\sync\rkws\RkwsWaybill;
use common\models\IntegrationSettingValue;
use common\models\licenses\License;
use common\models\OuterAgent;
use common\models\OuterCategory;
use common\models\OuterStore;
use Yii;
use yii\db\Transaction;
use yii\db\mssql\PDO;
use yii\web\BadRequestHttpException;
use creocoder\nestedsets\NestedSetsBehavior;
use common\models\AllServiceOperation;
use common\models\OuterTask;
use frontend\modules\clientintegr\modules\rkws\components\UUID;
use api\common\models\RkAccess;
use api\common\models\RkSession;

class ServiceRkws extends AbstractSyncFactory
{
    /** @var string $licenseCode License record CODE */
    public $licenseCode;
    /** @var string $licenseMixcartId License record ID */
    public $licenseMixcartId;

    /** @var string $now */
    public $now;

    /** @var string $entityTableName */
    public $entityTableName;

    public $index;

    /** @var  int Заполняется при ответе от r-keeper */
    public $orgId;

    public $urlCmdInit = 'http://ws.ucs.ru/WSClient/api/Client/Cmd';
    public $urlLoginInit = 'http://ws.ucs.ru/WSClient/api/Client/Login';

    public $urlCmd;
    public $urlLogin;

    public $dirResponseXml = '@api_web/modules/integration/views/sync/rkws/request';

    const COOK_AUTH_PREFIX_SESSION = '.ASPXAUTH';
    const COOK_AUTH_PREFIX_LOGIN = '_ASPXAUTH';

    const COOK_AUTH_STR_BEGIN = 'Set-Cookie';

    public static $OperDenom;

    public $useNestedSets = false;
    public $nestedSetsSpecialValuesForElements = [];

    /** @var array $additionalXmlFields Поле во входящем xml -> поле в нашей модели данных */
    public $additionalXmlFields = [];

    protected $logCategory = "rkws_log";

    /**
     * Basic service method "Send request"
     *
     * @return array?
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function sendRequestForObjects(): ?array
    {
        # 1. Start "Send request" action
        $this->log('Initialized new procedure action "Send request" in ' . __METHOD__);
        $cook = $this->prepareServiceWithAuthCheck();

        $url = $this->getUrlCmd();
        $xml = '<?xml version="1.0" encoding="utf-8"?>
        <RQ cmd="get_objects">
          <PARAM name="start" val="1"/>
          <PARAM name="limit" val="1000"/>
          <PARAM name="onlyactive" val="0" />
        </RQ>';
        $xmlData = $this->sendByCurl($url, $xml, self::COOK_AUTH_PREFIX_SESSION . "=" . $cook . ";");

        $this->log('Result XML-data for objects is: ' . PHP_EOL . $xmlData);

        return [
            //wtf
//            'service_prefix' => SyncLog::$servicePrefix,
//            'log_index'      => SyncLog::$logIndex,
        ];

    }

    /**
     * Разбор полученного xml
     *
     * @param string|null $data
     * @return array
     */
    function parsingXml(string $data = null): array
    {
        return [];
    }

    /**
     * Отправка запроса
     *
     * @param array $params
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function sendRequest(array $params = []): array
    {
        # 1. Start "Send request" action
        $this->log('Initialized new procedure action "Send request" in ' . __METHOD__);
        $cook = $this->prepareServiceWithAuthCheck();
        # 2. Если нет сессии - завершаем с ошибкой
        if (!$cook) {
            throw new BadRequestHttpException('Cannot authorize with curl');
        }
        #Если пришел запрос на обновление продуктов
        if ($params['dictionary'] == 'product') {

            $models = OuterCategory::find()->where([
                'service_id' => Registry::RK_SERVICE_ID,
                'org_id'     => $this->user->organization_id,
                'selected'   => 1
            ])->all();

            if (empty($models)) {
                throw new BadRequestHttpException('Не выбраны категории для загрузки товаров');
            }
            $params['product_group'] = $models;
        }

        return $this->sendRequestPrivate($params, $cook);
    }

    /**
     * @param array $params
     * @param       $cook
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     */
    private function sendRequestPrivate(array $params, $cook): array
    {
        $url = $this->getUrlCmd();
        $guid = UUID::uuid4();

        $dictionary = $this->getOrganizationDictionary($this->serviceId, $this->user->organization_id);
        $transaction = $this->createTransaction();
        try {
            $xml = $this->prepareXmlWithTaskAndServiceCode($this->index, $this->licenseCode, $guid, $params);
            $xmlData = $this->sendByCurl($url, $xml, self::COOK_AUTH_PREFIX_SESSION . "=" . $cook . ";");
            if ($xmlData) {
                $xml = (array)simplexml_load_string($xmlData);
                //Проверка ошибок
                $this->checkErrorResponse($xml);

                if (isset($xml['@attributes']['taskguid']) && isset($xml['@attributes']['code']) && $xml['@attributes']['code'] == 0) {
                    $operation = AllServiceOperation::findOne(['service_id' => $this->serviceId, 'denom' => static::$OperDenom]);
                    $task = new OuterTask([
                        'service_id'     => $this->serviceId,
                        'retry'          => 0,
                        'org_id'         => $this->user->organization_id,
                        'inner_guid'     => $guid,
                        'salespoint_id'  => (string)$this->licenseMixcartId,
                        'int_status_id'  => OuterTask::STATUS_REQUESTED,
                        'outer_guid'     => $xml['@attributes']['taskguid'],
                        'broker_version' => $xml['@attributes']['version'],
                        'oper_code'      => $operation->code
                    ]);
                    $task->save();

                    $dictionary->status_id = $dictionary::STATUS_SEND_REQUEST;
                    $dictionary->save();

                    $transaction->commit();
                    $this->log('SUCCESS. json-response-data: ' . str_replace(',', PHP_EOL . '      ', json_encode($task->attributes)));
                    return $this->prepareModel($dictionary);
                }
            }
        } catch (\Throwable $e) {
            $transaction->rollBack();
            $dictionary->status_id = $dictionary::STATUS_ERROR;
            $dictionary->save();
            throw $e;
        }

        throw new BadRequestHttpException('empty_service_response_for_transaction');
    }

    /**
     * @param null $code
     * @return null|string
     * @throws BadRequestHttpException
     * @throws \Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function prepareServiceWithAuthCheck($code = null): ?string
    {
        $this->licenseCode = $code;
        # 1. Check if authorization is required && active license exists
        $this->log('Begin "auth check" in ' . __METHOD__);
        $this->now = date('Y-m-d H:i:s', time());
        # 2. Find license
        /**@var License $license */
        $license = License::checkByServiceId($this->user->organization_id, Registry::RK_SERVICE_ID);
        if (!$license) {
            throw new BadRequestHttpException('no_active_mixcart_license');
        }
        # 3. Remember license codes
        if (is_null($this->licenseCode)) {
            $this->licenseCode = IntegrationSettingValue::getSettingsByServiceId(Registry::RK_SERVICE_ID, $this->user->organization_id, ['code']);
            if (!$this->licenseCode) {
                throw new BadRequestHttpException('Не задана настройка [code] для R-keeper.');
            }
        }
        # 5. Фиксируем активную лицензия найдена и инициализируем транзакции в БД
        $transaction = $this->createTransaction();
        # 6. Пытаемся найти активную сессию и если все хорошо - то используем ее
        $sess = RkSession::findOne(['acc' => $this->user->organization_id, 'status' => 1]);
        if ($sess && $sess->cook) {
            # 6.1. Активная лицензия найдена - проверяем сессию в куки
            $cookie = self::COOK_AUTH_PREFIX_SESSION . "=" . $sess->cook . ";";
            $xmlData = $this->sendByCurl($this->getUrlCmd(), $this->prepareXmlForTestConnection($this->licenseCode), $cookie);
            if ($xmlData) {
                $xml = (array)simplexml_load_string($xmlData);
                if (isset($xml['OBJECTINFO'])) {
                    $xml = (array)$xml['OBJECTINFO'];
                    $err = (isset($xml['ERROR']) && $xml['ERROR']) ? $xml['ERROR'] : null;
                    if (isset($xml['@attributes']['id']) && $xml['@attributes']['id'] == $this->licenseCode && !$err) {
                        # 6.1.1. Активная сессия в куки подтверждена - используем ее и прекращаем процедуры
                        $this->log('Service licence session with active state id good - use it');
                        /** @var PDO $transaction */
                        $transaction->rollback();
                        return $sess->cook;
                    }
                }
            }
            $this->deactivateSessionWithoutCommit($sess, $transaction);
        }
        # 7. Если сюда попали - то активной сессии нет!!!
        # Пытаемся создать новую
        # Checkout existing valig connection params
        $access = RkAccess::findOne(['locked' => 0]);
        if ($access) {
            $this->log('Service licence connection parameters found - try to use it');
            # 7.1. Try to prepare new session
            $xmlData = $this->sendByCurl($this->getUrlLogin(), $this->prepareXmlWithAuthParams($access));
            if ($xmlData) {
                preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $xmlData, $matches);
                $cookList = [];
                foreach ($matches[1] as $item) {
                    parse_str($item, $cook);
                    $cookList = array_merge($cookList, $cook);
                }
                if (isset($cookList[self::COOK_AUTH_PREFIX_LOGIN]) && $cookList[self::COOK_AUTH_PREFIX_LOGIN]) {
                    # 7.1.1. Try to save new session
                    $sess = new RkSession();
                    $sess->cook = $cookList[self::COOK_AUTH_PREFIX_LOGIN];
                    $sess->fd = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
                    $sess->td = Yii::$app->formatter->asDate('2030-01-01 23:59:59', 'yyyy-MM-dd HH:mm:ss');
                    $sess->acc = $this->user->organization_id;
                    $sess->status = 1;
                    $sess->fid = 1;
                    $sess->ver = 1;
                    $sess->status = 1;
                    if (!$sess->save()) {
                        $transaction->rollback();
                        throw new BadRequestHttpException('rkws_session_create_error');
                    }
                    # 7.2. Use valid session
                    $transaction->commit();
                    return $sess->cook;
                }
                $transaction->rollback();
                throw new BadRequestHttpException('rkws_session_no_cookie');
            } else {
                throw new BadRequestHttpException('empty_service_response');
            }
        }
        throw new BadRequestHttpException('empty_service_access_params');
    }

    /**
     * @param RkSession   $sess
     * @param Transaction $transaction
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidArgumentException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function deactivateSessionWithoutCommit(RkSession $sess, Transaction $transaction)
    {
        # 6.2. Активная сессия в куки не подтверждена
        $sess->status = 0;
        $sess->td = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
        if (!$sess->save()) {
            $transaction->rollback();
            throw new BadRequestHttpException('rkws_session_update_error');
        }
    }

    /**
     * @return Transaction
     */
    public function createTransaction(): Transaction
    {
        return \Yii::$app->db_api->beginTransaction(Transaction::READ_UNCOMMITTED);
    }

    /**
     * Prepare URL to test service connection with session is active
     *
     * @return string?
     */
    public function getUrlCmd(): ?string
    {
        if (!$this->urlCmd) {
            $url = $this->urlCmdInit;
            if (isset(Yii::$app->params['rkeepCmdURL']) && Yii::$app->params['rkeepCmdURL']) {
                $url = Yii::$app->params['rkeepCmdURL'];
            }
            $this->urlCmd = $url;
        }
        $this->log('Request URL getUrlCmd() : ' . $this->urlCmd);
        return $this->urlCmd;
    }

    /**
     * Prepare URL to test service connection with login params
     *
     * @return string?
     */
    public function getUrlLogin(): string
    {
        if (!$this->urlLogin) {
            $url = $this->urlLoginInit;
            if (isset(Yii::$app->params['rkeepAuthURL']) && Yii::$app->params['rkeepAuthURL']) {
                $url = Yii::$app->params['rkeepAuthURL'];
            }
            $this->urlLogin = $url;
        }
        $this->log('Request URL getUrlLogin() : ' . $this->urlLogin);
        return $this->urlLogin;
    }

    /**
     * Prepare Xml to test service connection with session is active
     *
     * @param string $code
     * @return string
     */
    public function prepareXmlForTestConnection(string $code): string
    {
        return '<?xml version="1.0" encoding="utf-8" ?>
    <RQ cmd="get_objectinfo">
        <PARAM name="object_id" val="' . $code . '" />
    </RQ>';
    }

    /**
     * Prepare Xml to test service connection session with login params
     *
     * @param RkAccess $access
     * @return string
     */
    public function prepareXmlWithAuthParams(RkAccess $access): string
    {
        $this->log('Prepare XML-data type "Service new login and password connection" in ' . __METHOD__);
        $key = $access->lic;
        $usr = $access->login . ';';
        $usr .= strtolower(md5($access->login . $access->password)) . ';';
        $usr .= strtolower(md5($access->token));

        return '<?xml version="1.0" encoding="UTF-8"?><AUTHCMD key="' . $key . '" usr="' . base64_encode($usr) . '" />';
    }

    /**
     * @return string
     */
    public function getCallbackURL(): string
    {
        return Yii::$app->params['rkeepCallBackURL'] . '?';
    }

    /**
     * @param       $index
     * @param       $code
     * @param       $guid
     * @param array $params
     * @return string
     */
    public function prepareXmlWithTaskAndServiceCode($index, $code, $guid, array $params = []): string
    {
        $cb = $this->getCallbackURL() . AbstractSyncFactory::CALLBACK_TASK_IDENTIFIER . '=' . $guid;
        $this->log('Callback URL and salespoint code for the template are:' . $cb . ' (' . $code . ')');

        $renderParams = [
            'cb'   => $cb,
            'code' => $code,
            'guid' => $guid
        ];
        if (isset($params['product_group']) && $params['product_group']) {
            $renderParams['productGroup'] = $params['product_group'];
        }
        if (isset($params['code']) && $params['code']) {
            $this->log('Made object code replacement:' . $code . ' -> ' . $params['code']);
            $renderParams['code'] = $params['code'];
        }
        $template = Yii::$app->view->render($this->dirResponseXml . '/' . ucfirst($index), $renderParams);
        $template = trim($template);
        $this->log('Template result is:' . PHP_EOL . $template);
        return $template;
    }

    /**
     * Метод отправки накладной
     *
     * @param array $request
     * @return array
     * @throws \Exception
     */
    public function sendWaybill($request): array
    {
        # 1. Проверяем наличие id накладной
        if (!isset($request['ids'])) {
            throw new BadRequestHttpException('empty ids');
        }

        $result = [];
        foreach ($request['ids'] as $waybill_id) {
            # 2. Ищем накладную
            $waybill = \api_web\modules\integration\classes\documents\Waybill::findOne(['id' => $waybill_id, 'service_id' => $this->serviceId]);
            if (empty($waybill)) {
                throw new BadRequestHttpException('Накладная ' . $waybill_id . ' не найдена ');
            }

            # 3. Выбираем даные по накладной для отправки
            $records = WaybillContent::find()
                ->select('waybill_content.*, outer_product.outer_uid as product_rid, outer_unit.outer_uid as unit_rid')
                ->leftJoin('outer_product', 'outer_product.id = outer_product_id')
                ->leftJoin('outer_unit', 'outer_unit.id = outer_product.outer_unit_id')
                ->andWhere('waybill_id = :wid', [':wid' => $waybill_id])
//                ->andWhere(['unload_status' => 1])
                ->asArray(true)->all();

            if (!isset($records)) {
                $result[$waybill_id] = false;
                $this->log('No records found to be sent in ' . __METHOD__);
                continue;
            }

            # 4. Start "Send waybill" action
            $this->log('Initialized new procedure action "Send request" in ' . __METHOD__);
            $cook = $this->prepareServiceWithAuthCheck();

            # 2. Если нет сессии - завершаем с ошибкой
            if (!$cook) {
                $this->log('Cannot authorize with session or login data');
                throw new BadRequestHttpException('Cannot authorize with curl');
            }

            $url = $this->getUrlCmd();
            $guid = UUID::uuid4();

            //$xml = $this->prepareXmlWithTaskAndServiceCode($this->index, $this->licenseCode, $guid, $params);

            $cb = str_replace('load-dictionary', '', Yii::$app->params['rkeepCallBackURL']) . "send-waybill?" . AbstractSyncFactory::CALLBACK_TASK_IDENTIFIER . '=' . $guid;
            $this->log('Callback URL and salespoint code for the template are:' . $cb . ' (' . $this->licenseCode . ')');

            $settings = IntegrationSettingValue::getSettingsByServiceId(Registry::RK_SERVICE_ID, $this->user->organization_id, ['useAcceptedDocs', 'sh_version']);
            $exportApproved = $settings['useAcceptedDocs'] ?? 0;
            $shVersion = $settings['sh_version'] ?? 4;

            $records = RkwsWaybill::prepareItemsWaybill($records, $shVersion);

            $outerAgent = OuterAgent::findOne($waybill->outer_agent_id);
            $outerStore = OuterStore::findOne($waybill->outer_store_id);
            $xml = Yii::$app->view->render($this->dirResponseXml . '/Waybill', [
                'waybill'        => $waybill,
                'agentUid'       => $outerAgent->outer_uid,
                'storeUid'       => $outerStore->outer_uid,
                'records'        => $records,
                'exportApproved' => $exportApproved ?? 0,
                'code'           => $this->licenseCode,
                'guid'           => $guid,
                'cb'             => $cb
            ]);

            $this->log($xml);
            $xmlData = $this->sendByCurl($url, $xml, self::COOK_AUTH_PREFIX_SESSION . "=" . $cook . ";");

            if ($xmlData) {
                $xml = (array)simplexml_load_string($xmlData);
                //Проверка ошибок
                $this->checkErrorResponse($xml);

                if (isset($xml['@attributes']['taskguid']) && isset($xml['@attributes']['code']) && $xml['@attributes']['code'] == 0) {
                    $transaction = $this->createTransaction();
                    $waybill->status_id = Registry::WAYBILL_UNLOADING;
                    if (!$waybill->save()) {
                        $this->log('Error while saving waybill status');
                    }
                    $oper = AllServiceOperation::findOne(['service_id' => $this->serviceId, 'denom' => 'sh_doc_receiving_report']);
                    $task = new OuterTask([
                        'service_id'     => $this->serviceId,
                        'retry'          => 0,
                        'org_id'         => $this->user->organization_id,
                        'user_id'        => $this->user->id,
                        'inner_guid'     => $guid,
                        'salespoint_id'  => (string)$this->licenseMixcartId,
                        'int_status_id'  => OuterTask::STATUS_REQUESTED,
                        'outer_guid'     => $xml['@attributes']['taskguid'],
                        'broker_version' => $xml['@attributes']['version'],
                        'oper_code'      => $oper->code,
                        'waybill_id'     => $waybill->id,
                    ]);
                    if ($task->save()) {
                        $transaction->commit();
                        $this->log('SUCCESS. json-response-data: ' .
                            str_replace(',', PHP_EOL . '      ', json_encode($task->attributes)));
                        $result[] = $waybill->prepare();
                    } else {
                        $transaction->rollBack();
                        $this->log('Cannot save task!');
                        $result[] = $waybill->prepare();
                        //throw new BadRequestHttpException('rkws_task_save_error');
                    }
                }
            } else {
                $this->log('Service connection parameters for final transaction are wrong');
                $result[] = $waybill->prepare();
            }
        }

        return $result;
    }

    /**
     * @param OuterTask   $task
     * @param string|null $data
     * @return string
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function callbackData(OuterTask $task, string $data = null)
    {
        $orgDic = $this->getOrganizationDictionary($task->service_id, $task->org_id);

        try {
            $this->checkErrorResponse((array)simplexml_load_string($data));
        } catch (\Exception $e) {
            \Yii::error('ERROR RK: ' . $e->getMessage());
            $orgDic->status_id = $orgDic::STATUS_ERROR;
            $orgDic->save();
            $orgDic->noticeToFCM();
            return self::XML_LOAD_RESULT_FAULT;
        }

        # 2. Получаем массив входящих данных
        $arrayNew = $this->parsingXml($data);
        # 3. Таблица справочника
        /** @var yii\db\ActiveRecord $entityTableName */
        $entityTableName = $this->entityTableName;
        # 4. Фиксируем вспомагательные переменные для контроля ошибок записи/обновления данных в БД
        $transaction = $this->createTransaction();
        $saveCount = 0;
        $saveErr = [];
        # 5.2.1. Помечаем все данные как удаленные
        $entityTableName::updateAll(['is_deleted' => 1], ['org_id' => $task->org_id, 'service_id' => $this->serviceId]);

        if ($this->useNestedSets) {
            # 5.1.2. Получаем сведения о существовании записи root для nestedSets
            $rootModel = $entityTableName::findOne(['org_id' => $task->org_id, 'service_id' => $this->serviceId, 'level' => 0]);
            if ($rootModel && isset($rootModel->outer_uid)) {
                $root_rid = $rootModel->outer_uid;
            } else {
                $root_rid = md5($task->org_id . $this->serviceId . microtime(true));
            }
            array_unshift($arrayNew, ['rid' => $root_rid, 'name' => 'Все', 'parent' => null]);
            # 5.1.3. Перебираем данные
            $list = [];
            $arRids = array_map(function ($el) {
                return $el['rid'];
            }, $arrayNew);
            $models = $entityTableName::find()
                ->where([
                    'outer_uid'  => $arRids,
                    'org_id'     => $task->org_id,
                    'service_id' => $this->serviceId
                ])->indexBy('outer_uid')->all();

            foreach ($this->iterator($arrayNew) as $k => $v) {
                /** @var OuterCategory $model */
                $model = $models[$v['rid']] ?? null;
                if (!$model) {
                    $model = new $entityTableName([
                        'outer_uid'  => $v['rid'],
                        'org_id'     => $task->org_id,
                        'service_id' => $this->serviceId,
                        'is_deleted' => 0
                    ]);
                    if ($model->hasAttribute('parent_outer_uid')) {
                        $model->parent_outer_uid = $v['parent'] ?? null;
                    }
                } else {
                    $model->is_deleted = 0;
                    $model->name = $v['name'];
                    if ($model->hasAttribute('parent_outer_uid')) {
                        $model->parent_outer_uid = $v['parent'] ?? null;
                    }
                    if ($model->dirtyAttributes) {
                        $model->save();
                    }
                    $saveCount++;
                    $list[$model->outer_uid] = $model;
                    continue;
                }
                if (!$v['parent']) {
                    $v['parent'] = $root_rid;
                } elseif ($this->nestedSetsSpecialValuesForElements) {
                    foreach ($this->nestedSetsSpecialValuesForElements as $kk => $vv) {
                        $model->$kk = $vv;
                    }
                }
                $list[$v['rid']] = $model;
                /** @noinspection PhpUndefinedFieldInspection */
                $model->name = $v['name'];
                /** @var $model NestedSetsBehavior */
                if ($v['rid'] == $root_rid) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    if (!$model->id) {
                        $model->makeRoot();
                    }
                } elseif (!$v['parent']) {
                    $model->prependTo($list[$root_rid]);
                } else {
                    $model->prependTo($list[$v['parent']]);
                }
                $saveCount++;
            }
        } else {
            # 5.2.2. Перебираем новые данные и пробуем добавить/обновить записи в БД
            foreach ($this->iterator($arrayNew) as $elementNew) {
                $entity = $entityTableName::findOne([
                    'org_id'     => $task->org_id,
                    'outer_uid'  => $elementNew['rid'],
                    'service_id' => $task->service_id
                ]);

                if (!$entity) {
                    $entity = new $entityTableName();
                    $entity->org_id = $task->org_id;
                    $entity->outer_uid = $elementNew['rid'];
                    $entity->service_id = $task->service_id;
                }

                /** @noinspection PhpUndefinedFieldInspection */
                foreach ($this->additionalXmlFields as $k => $v) {
                    if (isset($elementNew[$k])) {
                        $entity->$v = $elementNew[$k];
                    }
                }
                /** @noinspection PhpUndefinedFieldInspection */
                $entity->is_deleted = 0;
                if ($entity->dirtyAttributes) {
                    if ($entity->save()) {
                        $saveCount++;
                    } else {
                        /** @noinspection PhpUndefinedFieldInspection */
                        $saveErr['dicElement'][$entity->id][] = $entity->errors;
                    }
                }
            }
        }

        # 6. Фиксируем изменения в текущей задаче
        if ($saveCount && !$saveErr) {
            $task->int_status_id = OuterTask::STATUS_CALLBACKED;
            $task->retry++;
            $task->save();
            $orgDic->count = $saveCount;
            $orgDic->status_id = $orgDic::STATUS_ACTIVE;
            $orgDic->save();
            $transaction->commit();
            $orgDic->noticeToFCM();
            $this->log('Number of save counts while there were no errors is ' . $saveCount);
            return self::XML_LOAD_RESULT_SUCCESS;
        }

        $this->log('No rows were inserted or updated!');
        $saveErr = ['save' => 'no_save_data'];
        $transaction->rollback();
        $orgDic->status_id = $orgDic::STATUS_ERROR;
        $orgDic->save();
        $orgDic->noticeToFCM();
        $this->log('Fixed save errors: ' . json_encode($saveErr));
        return self::XML_LOAD_RESULT_FAULT;
    }

    /**
     * @param OuterTask   $task
     * @param string|null $data
     * @return string
     */
    public function receiveXMLDataWaybill(OuterTask $task, string $data = null)
    {
        try {
            $this->checkErrorResponse((array)simplexml_load_string($data));
        } catch (\Exception $e) {
            $this->log('Error: ' . $e->getMessage());
            return self::XML_LOAD_RESULT_FAULT;
        }

        $task->int_status_id = OuterTask::STATUS_CALLBACKED;
        $task->retry++;
        $task->save();
        $this->log('Waybill successfully send');
        return self::XML_LOAD_RESULT_SUCCESS;
    }

    /**
     * @param array $request
     * @return array
     * @throws BadRequestHttpException
     */
    public function checkConnect($request = [])
    {

        if (isset($request['params'])) {
            if (isset($request['params']['code'])) {
                $this->licenseCode = $request['params']['code'];
            }
        }

        try {
            $cook = $this->prepareServiceWithAuthCheck($this->licenseCode);
            $url = $this->getUrlCmd();
            $xml = '<?xml version="1.0" encoding="utf-8"?>
            <RQ cmd="get_objectinfo">
                <PARAM name="object_id" val="' . $this->licenseCode . '"/>
            </RQ>';
            $xmlData = $this->sendByCurl($url, $xml, self::COOK_AUTH_PREFIX_SESSION . "=" . $cook . ";");
            if (!empty($xmlData)) {
                $xml = (array)simplexml_load_string($xmlData);
                $this->checkErrorResponse($xml);
            } else {
                throw new BadRequestHttpException('Bad connection.');
            }
            return ['result' => true];
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }

    /**
     * Проверка ошибок в респонсе от WS
     *
     * @param $xml
     * @throws BadRequestHttpException
     */
    private function checkErrorResponse($xml)
    {
        //Если ошибка
        if (isset($xml['ERROR']) || (isset($xml['@attributes']['code']) && $xml['@attributes']['code'] == 5)) {
            if (isset($xml['ERROR'])) {
                $error = (array)$xml['ERROR'];
                $code = $error['@attributes']['code'];
                $text = $error['@attributes']['Text'];
            } else {
                $code = $xml['@attributes']['code'];
                $text = $xml['@attributes']['Text'];
            }
            throw new BadRequestHttpException("RESPONSE WS: " . $text . ' (code: ' . $code . ')');
        }
    }
}
