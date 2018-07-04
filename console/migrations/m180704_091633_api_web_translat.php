<?php

use yii\db\Migration;

/**
 * Class m180704_091633_api_web_translat
 */
class m180704_091633_api_web_translat extends Migration
{
    public $translations_ru = [
        'order_not_found' => 'Заказ не найден',
        'vendor_not_found' => 'Поставщик не найден',
        'client_not_found' => 'Ресторан не найден',
        'user_not_found' => 'Пользователь не найден',
        'product_not_found' => 'Товар не найден',
        'request_not_found' => 'Заявка не найдена',
        'category_not_found' => 'Категория не найдена',
        'empty_param' => 'Пустой параметр %s',
        'bad_sms_code' => 'Введен неверный код',
        'wait_sms_send' => 'Пожалуйста подождите %s секунд для повторного запроса кода.',
        'method_access_to_vendor' => 'Этот метод доступен только для ресторанов.'
    ];

    public $translations_en = [
        'order_not_found' => 'Order not found',
        'vendor_not_found' => 'Vendor not found',
        'client_not_found' => 'Client not found',
        'user_not_found' => 'User not found',
        'product_not_found' => 'Product not found',
        'request_not_found' => 'Request not found',
        'category_not_found' => 'Category not found',
        'empty_param' => 'You need to send a parameter %s',
        'bad_sms_code' => 'Enter the wrong code',
        'wait_sms_send' => 'Wait %s seconds.',
        'method_access_to_vendor' => 'This method is forbidden for the vendor.'
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp() {
        \console\helpers\BatchTranslations::insertCategory('ru', 'api_web', $this->translations_ru);
        \console\helpers\BatchTranslations::insertCategory('en', 'api_web', $this->translations_en);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'api_web', $this->translations_ru);
        \console\helpers\BatchTranslations::deleteCategory('en', 'api_web', $this->translations_en);
    }
}
