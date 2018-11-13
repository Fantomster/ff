<?php

use yii\db\Migration;

/**
 * Class m181107_060615_add_lang
 */
class m181107_060615_add_lang extends Migration
{
    public $translations = [
        'cart.cart_content_not_found'        => 'Нет такого товара в корзине',
        'cart.quantity_must_be_greater_zero' => 'Количество должно быть больше 0',
        'catalog.access_denied'              => 'Каталог %s недоступен для вас.',
        'catalog.temp_not_found'             => 'Временный каталог не найден.',
        'catalog.not_found'                  => 'Каталог не найден.',
        'catalog.not_empty'                  => 'Каталог не пуст.',
        'catalog.is_empty'                   => 'Каталог пуст.',
        'catalog.delete_failed'              => 'При удалении каталога произошла ошибка',
        'catalog.main_index_empty'           => 'Для каталога не назначен ключ',
        'chat.access_denied'                 => 'У вас нет дуступа к чату',
        'chat.dialogs_not_found'             => 'Диалоги не найдены',
        'chat.dialog_not_found'              => 'Диалог не найден',
        'chat.not_message'                   => 'Нет сообщений',
        'RelationUserOrganization_not_found' => 'Нет доступа к организации',
        'additional_email.not_found'         => 'Дополнительный email не найден',
        'user.role_set_access'               => 'Нельзя присвоить эту роль пользователю',
        'user.work_in_role'                  => 'Этот сотрудник уже работает под ролью: %s',
        'user.not_staff'                     => 'Этот пользователь не является вашим сотрудником.',
        'user.delete_myself'                 => 'Удаление себя из списка сотрудников недоступно.',
        'guide.vendor_create_denied'         => 'Создание шаблона, доступно только для Ресторана.',
        'guide.access_denied'                => 'Доступ к шаблону запрещен, скорее всего это не ваш шаблон.',
        'guide.not_found'                    => 'Шаблон не найден',
        'guide.template_name'                => 'Шаблон по заказу №',
        'guide.empty_goods'                  => 'В шаблоне отсутствуют товары.',
        'guide.operation_not_found'          => 'Неизвестная операция: %s',
        'guide.max_products'                 => 'В шаблон можно добавить максимум %s продуктов.',
        'guide.not_add_product_in_guide'     => 'Вы не можете добавить этот товар в шаблон: %s',
        'guide.product_not_found'            => 'Продукт (%s) не найден в шаблоне',
        'integration_setting.not_found'      => 'Настройка не найдена',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        \console\helpers\BatchTranslations::insertCategory('ru', 'api_web', $this->translations);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'api_web', $this->translations);
    }
}
