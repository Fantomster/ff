<?php

namespace api_web\controllers;

use api_web\classes\CatalogWebApi;
use api_web\classes\VendorWebApi;
use api_web\components\Registry;
use api_web\components\WebApiController;
use yii\filters\AccessControl;

/**
 * Class VendorController
 *
 * @property VendorWebApi $classWebApi
 * @package api_web\controllers
 */
class VendorController extends WebApiController
{
    public $className = VendorWebApi::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $access['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow'      => true,
                    'actions'    => [
                        'search',
                        'create',
                        'update',
                        'upload-file',
                    ],
                    'roles'      => [Registry::PURCHASER_RESTAURANT],
                    'roleParams' => ['user' => $this->user]
                ],
                [
                    'allow'      => true,
                    'actions'    => [
                        'get',
                        'upload-logo',
                        'personal-catalog-list',
                        'temp-catalog-list',
                        'get-list-main-index',
                        'delete-item-temporary',
                        'upload-temporary',
                        'prepare-temporary',
                        'delete-item-personal-catalog',
                        'import-custom-catalog',
                        'delete-main-catalog',
                        'change-main-index',
                        'cancel-temporary',
                        'get-temp-main-catalog',
                        'get-temp-duplicate-position',
                        'auto-delete-duplicate-temporary',
                        'set-currency-for-personal-catalog'
                    ],
                    'roles'      => [Registry::OPERATOR],
                    'roleParams' => ['user' => $this->user]
                ]
            ],
        ];

        $behaviors = array_merge($behaviors, $access);

        return $behaviors;
    }

    /**
     * Отключение логирования
     *
     * @var array
     */
    public $not_log_actions = [
        'upload-main-catalog',
        'upload-personal-catalog'
    ];

    /**
     * @SWG\Post(path="/vendor/get",
     *     tags={"Vendor"},
     *     summary="Информация о поставщике",
     *     description="Информация о поставщике",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                              "vendor_id": 1
     *                      }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              ref="#/definitions/Vendor"
     *          ),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "error"
     *     )
     * )
     * @throws \Exception
     */
    public function actionGet()
    {
        $this->response = $this->classWebApi->get($this->request);
    }

    /**
     * @SWG\Post(path="/vendor/create",
     *     tags={"Vendor"},
     *     summary="Создание нового поставщика в системе, находясь в аккаунте ресторана",
     *     description="Создание нового поставщика в системе, находясь в аккаунте ресторана",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                               "user":{
     *                                   "vendor_id": 1,
     *                                   "email":"test@test.ru",
     *                                   "fio":"Donald Trump",
     *                                   "phone": "+79182225588",
     *                                   "organization_name": "ООО Рога и Копыта",
     *                                   "inn": "0001112223",
     *                                   "contact_name": "Контактное лицо"
     *                               },
     *                               "catalog":{
     *                                   "products":{
     *                                      {
     *                                          "product": "Треска горячего копчения",
     *                                          "price": 499.80,
     *                                          "ed": "шт."
     *                                      }
     *                                  },
     *                                  "currency_id": 1
     *                              }
     *                      }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                "success": true,
     *                "organization_id": 2222,
     *                "user_id": 1111,
     *                "message": "Поставщик ООО Рога и Копыта и каталог добавлен! Инструкция по авторизации была
     *                отправлена на почту test@test.ru"
     *              }
     *          ),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "error"
     *     )
     * )
     * @throws \Exception
     */
    public function actionCreate()
    {
        $this->response = $this->classWebApi->create($this->request);
    }

    /**
     * @SWG\Post(path="/vendor/search",
     *     tags={"Vendor"},
     *     summary="Поиск поставщика по email",
     *     description="Поиск поставщика по email",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={"email":"test@test.ru"}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              ref="#/definitions/VendorSearch"
     *          ),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "error"
     *     )
     * )
     * @throws \Exception
     */
    public function actionSearch()
    {
        $this->response = $this->classWebApi->search($this->request);
    }

    /**
     * @SWG\Post(path="/vendor/update",
     *     tags={"Vendor"},
     *     summary="Редактирование поставщика",
     *     description="Редактирование поставщика",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         description="id - обязательное поле",
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                             "id": 3551,
     *                             "name": "ООО Рога и Копыта",
     *                             "phone": "+79182225588",
     *                             "email":"test@test.ru",
     *                             "inn": "1111111111",
     *                             "contact_name": "Контактное имя",
     *                             "gmt": 3,
     *                             "address": {
     *                                  "country":"Россия",
     *                                  "region": "Московская область",
     *                                  "locality": "Люберцы",
     *                                  "route": "улица Побратимов",
     *                                  "house": "владение 107",
     *                                  "lat": 55.7713,
     *                                  "lng": 37.7055,
     *                                  "place_id":"ChIJM4NYCODJSkERVeMzXqoIJho"
     *                             }
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              ref="#/definitions/Vendor"
     *          ),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "error"
     *     )
     * )
     * @throws \Exception
     */
    public function actionUpdate()
    {
        $this->response = $this->classWebApi->update($this->request);
    }

    /**
     * @SWG\Post(path="/vendor/upload-logo",
     *     tags={"Vendor"},
     *     summary="Смена логотипа поставщика",
     *     description="Смена логотипа поставщика.",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                      "vendor_id": 1,
     *                      "image_source": "data:image/png;base64,iVBORw0KGgoAA=="
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              ref="#/definitions/Vendor"
     *          ),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "error"
     *     )
     * )
     * @throws \Exception
     */
    public function actionUploadLogo()
    {
        $this->response = $this->classWebApi->uploadLogo($this->request);
    }

    /**
     * @SWG\Post(path="/vendor/personal-catalog-list",
     *     tags={"Vendor/Catalog"},
     *     summary="Список товаров в индивидуальном каталоге",
     *     description="Список товаров в индивидуальном каталоге",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                      "vendor_id": 3010,
     *                      "pagination":{
     *                          "page":1,
     *                          "page_size":12
     *                      }
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(ref="#/definitions/VendorCatalogGoods"),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "error"
     *     )
     * )
     * @throws \Exception
     */
    public function actionPersonalCatalogList()
    {
        $this->response = (new CatalogWebApi())->getGoodsInCatalog($this->request);
    }

    /**
     * @SWG\Post(path="/vendor/temp-catalog-list",
     *     tags={"Vendor/Catalog"},
     *     summary="Список товаров во временном каталоге",
     *     description="Список товаров во временном каталоге",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                      "vendor_id": 3010,
     *                      "pagination":{
     *                          "page":1,
     *                          "page_size":12
     *                      }
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(ref="#/definitions/VendorCatalogGoods"),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "error"
     *     )
     * )
     * @throws \Exception
     */
    public function actionTempCatalogList()
    {
        $this->response = (new CatalogWebApi())->getGoodsInTempCatalog($this->request);
    }

    /**
     * @SWG\Post(path="/vendor/upload-file",
     *     tags={"Vendor/Catalog"},
     *     summary="Загрузка индивидуального каталога",
     *     description="Загрузка файла на файловый сервер.
     * Ответ возвращает 20 строк файла, для предпросмотра, и выбора колонок
     * На этом этапе, в базе не хранится ничего, кроме названия файла
     * vendor_id = ID вендора каталога в который происходит загрузка
     * data = документ Excel в base64",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                      "vendor_id": 3010,
     *                      "data":
     *                      "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,BASE64_ENCODE_SOURCE"
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                  "result": true,
     *                  "temp_id": 2,
     *                  "rows": {
     *                      {
     *                          "Артикул",
     *                          "Наименование",
     *                          "Кратность",
     *                          "Цена",
     *                          "Единица измерения",
     *                          "Комментарий"
     *                      },
     *                      {
     *                          "10",
     *                          "Товар 10",
     *                          "",
     *                          "100",
     *                          "бутылка",
     *                          ""
     *                      },
     *                      {
     *                          "111004",
     *                          "Балтика 7",
     *                          "1.25",
     *                          "45.5",
     *                          "бутылка",
     *                          ""
     *                      }
     *                  }
     *             }
     *          ),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "error"
     *     )
     * )
     * @throws
     */
    public function actionUploadFile()
    {
        $this->response = (new CatalogWebApi())->uploadFile($this->request);
    }

    /**
     * @SWG\Post(path="/vendor/get-list-main-index",
     *     tags={"Vendor/Catalog"},
     *     summary="Список ключей для загрузки каталога",
     *     description="Список ключей, доступных для выбора пользователю. Далее по этому ключу будет осуществляться
     *     поиск дублей. Передавать в метод /vendor/import-personal-catalog параметр index_field",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                 "product": "Продукт",
     *                 "article": "Артикул",
     *                 "other": "Другое"
     *             }
     *          ),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "error"
     *     )
     * )
     */
    public function actionGetListMainIndex()
    {
        $this->response = (new CatalogWebApi())->getListMainIndex();
    }

    /**
     * @SWG\Post(path="/vendor/prepare-temporary",
     *     tags={"Vendor/Catalog"},
     *     summary="Маппинг, валидация и импорт индивидуального каталога",
     *     description="Метод Импортирует файл с сервера во временную таблицу БД, по правилам которые переданы в
     *     параметре mapping vendor_id = ID вендора index_field = ключ поиска дублей mapping = очередность колонок, при
     *     загрузке файла
     *
     *     Пример:
     *     POST /vendor/upload-personal-catalog вернул результат
     *     {
     *          result: true,
     *          temp_id: 2,
     *          rows: [
     *              [
     *                  Артикул,
     *                  Наименование,
     *                  Кратность,
     *                  Цена,
     *                  Единица измерения,
     *                  Комментарий
     *              ],
     *              [
     *                  10,
     *                  Товар 10,
     *                  '',
     *                  100,
     *                  бутылка,
     *                  ''
     *              ],
     *              [
     *                  111004,
     *                  Балтика 7,
     *                  1.25,
     *                  45.5,
     *                  бутылка,
     *                  ''
     *              ]
     *         ]
     *     }
     *
     *     Тогда в mapping мы передаем очередность полей как на фронте ее отмечает пользователь
     *     mapping = {article:1, units:3, ed:5, price:4, product:2, other:6}
     * ",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         description="",
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                      "vendor_id": 3010,
     *                      "index_field": "article",
     *                      "mapping": {"article":1, "product":2, "units":3, "price":4, "ed":5, "other":6}
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                  "result": true
     *             }
     *          ),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "error"
     *     )
     * )
     * @throws \Exception
     */
    public function actionPrepareTemporary()
    {
        $this->response = (new CatalogWebApi())->prepareTemporary($this->request);
    }

    /**
     * @SWG\Post(path="/vendor/upload-temporary",
     *     tags={"Vendor/Catalog"},
     *     summary="Обновление индивидуального каталога",
     *     description="Обновление индивидуального каталога",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         description="",
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                      "vendor_id": 3010
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                  "result": true
     *             }
     *          ),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "error"
     *     )
     * )
     * @throws
     */
    public function actionUploadTemporary()
    {
        $this->response = (new CatalogWebApi())->uploadTemporary($this->request);
    }

    /**
     * @SWG\Post(path="/vendor/delete-item-temporary",
     *     tags={"Vendor/Catalog"},
     *     summary="Удаление позиции из временного каталога при загрузке",
     *     description="Удаление позиции из временного каталога при загрузке",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         description="",
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                      "temp_id": 4,
     *                      "id": 10
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                  "result": true
     *             }
     *          ),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "error"
     *     )
     * )
     * @throws
     */
    public function actionDeleteItemTemporary()
    {
        $this->response = (new CatalogWebApi())->deleteItemTemporary($this->request);
    }

    /**
     * @SWG\Post(path="/vendor/delete-item-personal-catalog",
     *     tags={"Vendor/Catalog"},
     *     summary="Удаление позиции из каталога",
     *     description="Удаление позиции из каталога",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         description="",
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                      "vendor_id": 4,
     *                      "product_id": 10
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                  "result": true
     *             }
     *          ),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "error"
     *     )
     * )
     * @throws
     */
    public function actionDeleteItemPersonalCatalog()
    {
        $this->response = (new CatalogWebApi())->deleteItemPersonalCatalog($this->request);
    }

    public function actionImportCustomCatalog()
    {
        (new CatalogWebApi())->importCustomCatalog($this->request);
    }

    /**
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDeleteMainCatalog()
    {
        $this->response = (new CatalogWebApi())->deleteMainCatalog($this->request);
    }

    /**
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionChangeMainIndex()
    {
        $this->response = (new CatalogWebApi())->changeMainIndex($this->request);
    }

    /**
     * @SWG\Post(path="/vendor/cancel-temporary",
     *     tags={"Vendor/Catalog"},
     *     summary="Удаление загруженного необработанного каталога",
     *     description="Удаление загруженного необработанного каталога",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         description="",
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                      "vendor_id": 3674
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                      "result": true
     *                  }
     *          ),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "error"
     *     )
     * )
     * @throws
     */
    public function actionCancelTemporary()
    {
        $this->response = (new CatalogWebApi())->cancelTemporary($this->request);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetTempMainCatalog()
    {
        $this->response = (new CatalogWebApi())->getTempMainCatalog($this->request);
    }

    /**
     * @SWG\Post(path="/vendor/get-temp-duplicate-position",
     *     tags={"Vendor/Catalog"},
     *     summary="Возвращает список дублей в загруженом каталоге",
     *     description="Возвращает список дублей в загруженом каталоге",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         description="",
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                      "vendor_id": 3010
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                      "1_test": {
     *                              {
     *                                  "id": 1,
     *                                  "temp_id": 2,
     *                                  "article": "1_test",
     *                                  "product": "Продукт 1",
     *                                  "price": 100,
     *                                  "units": 1,
     *                                  "note": null,
     *                                  "ed": "кг",
     *                                  "CountOf": 4
     *                              },
     *                              {
     *                                  "id": 2,
     *                                  "temp_id": 2,
     *                                  "article": "1_test",
     *                                  "product": "Продукт 2",
     *                                  "price": 110,
     *                                  "units": 1,
     *                                  "note": null,
     *                                  "ed": "кг",
     *                                  "CountOf": 4
     *                              }
     *                          }
     *                  }
     *          ),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "error"
     *     )
     * )
     * @throws \Exception
     */
    public function actionGetTempDuplicatePosition()
    {
        $this->response = (new CatalogWebApi())->getTempDuplicatePosition($this->request);
    }

    /**
     * @SWG\Post(path="/vendor/auto-delete-duplicate-temporary",
     *     tags={"Vendor/Catalog"},
     *     summary="Автоматическое удаление дублей в каталоге",
     *     description="Автоматическое удаление дублей в каталоге",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         description="",
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                      "vendor_id": 3674
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                      "result": true
     *                  }
     *          ),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "error"
     *     )
     * )
     * @throws \Exception
     */
    public function actionAutoDeleteDuplicateTemporary()
    {
        $this->response = (new CatalogWebApi())->autoDeleteDuplicateTemporary($this->request);
    }

    /**
     * @SWG\Post(path="/vendor/set-currency-for-personal-catalog",
     *     tags={"Vendor/Catalog"},
     *     summary="Установка валюты для индивидуального каталога",
     *     description="Установка валюты для индивидуального каталога",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         description="",
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                      "vendor_id": 3674,
     *                      "currency_id": 1
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                      "result": true
     *                  }
     *          ),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "error"
     *     )
     * )
     * @throws \Exception
     */
    public function actionSetCurrencyForPersonalCatalog()
    {
        $this->response = (new CatalogWebApi())->setCurrencyForPersonalCatalog($this->request);
    }
}
