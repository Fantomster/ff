<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 2019-02-08
 * Time: 14:28
 */

namespace api_web\modules\integration\classes\sync;

/**
 * Class ServiceMerc
 *
 * @package api_web\modules\integration\classes\sync
 */
class ServiceMerc extends AbstractSyncFactory
{
    const DICTIONARY_BUSINESS_ENTITY = 'businessEntity';
    const DICTIONARY_RUSSIAN_ENTERPRISE = 'russianEnterprise';
    const DICTIONARY_FOREIGN_ENTERPRISE = 'foreignEnterprise';
    const DICTIONARY_PRODUCT_ITEM = 'productItem';
    const DICTIONARY_TRANSPORT = 'transport';

    /**
     * Доступные словари
     *
     * @var array
     */
    public $dictionaryAvailable = [
        self::DICTIONARY_BUSINESS_ENTITY,
        self::DICTIONARY_RUSSIAN_ENTERPRISE,
        self::DICTIONARY_FOREIGN_ENTERPRISE,
        self::DICTIONARY_PRODUCT_ITEM,
        self::DICTIONARY_TRANSPORT,
    ];

    /**
     * Отправка запроса, обязательный метод
     *
     * @param array $params
     * @return array
     */
    public function sendRequest(array $params = []): array
    {
        // TODO_: Implement sendRequest() method.
        return [];
    }
}
