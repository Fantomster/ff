<?php

use yii\db\Migration;

/**
 * Class m181206_151715_add_translations_web_api
 */
class m181206_151715_add_translations_web_api extends Migration
{
    public $translations = [
        'empty_param|{param}'=>'Неуказан параметр|{param}',
        'bad_order_type|{type}'=>'Не правильный тип заказа|{type}',
        'catalog.access_denied|{cat}"'=>'Не доступа к каталогу|{cat}',
        'cart.cart_content_not_found'=>'Содержимое корзины не найдено',
        'cart.quantity_must_be_greater_zero'=>'Количество должно быть больше нуля ',
        'catalog.not_empty'=>'Каталог не пустой',
        'catalog.is_empty'=>'Каталог пустой',
        'catalog.delete_failed'=>'Невозможно удалить каталог',
        'base_catalog_not_found'=>'Базовый каталог не найден',
        'catalog_temp_not_found'=>'Временный каталог не найден',
        'catalog_temp_content_not_found'=>'Содержимое временного каталога не найдено',
        'catalog_temp_exists_duplicate'=>'Временный каталог уже содержит данную позицию',
        'catalog.delete_failed'=>'Невозможно удалить временный каталог',
        'catalog_not_found'=>'Каталог не найден',
        'product_not_found'=>'Позиция не найдена',
        'catalog.main_index_empty'=>'Пустой основной индекс',
        'this_is_not_your_catalog'=>'Это не Ваш каталог',
        'chat.access_denied'=>'Нет доступа к чату',
        'chat.dialogs_not_found'=>'Диалоги не найдены',
        'chat.dialog_not_found'=>'Диалог не найден',
        'method_access_to_vendor'=>'Действие только для постащика',
        'client_not_found'=>'Ресторан не найден',
        'RelationUserOrganization_not_found'=>'Связь пользователя с организацией не найдена',
        'model_not_found'=>'Данные не найдены',
        'additional_email.not_found'=>'Дополнительный email не найден',
        'user.role_set_access'=>'Невозможно назначить данную роль',
        'user.work_in_role|{role}'=>'Пользователю уже назначена роль|{role}',
        'user.employee.update.access_denied'=>'Нет прав на редактирование сотрудиника',
        'user.not_staff'=>'Пользователь не в штате',
        'user.role_set_access'=>'Нет прав на изменение роли',
        'user.delete_myself'=>'Невозможно активную учетную запись',
        'user.employee.delete.access_denied'=>'Нет прав на удаление сотрудника',
        'user_not_found'=>'Пользователь не найден',
        'document.not_support_type'=>'Указанный тип документа не поддерживается',
        'business unavailable to current user'=>'Указанный бизнес не связан с активным пользователем',
        'waybill_not_found'=>'Накладная не найдена',
        'document.waybill_in_the_state_of_reset_or_unloaded'=>'Документ в состоянии сброшен или выгружен',
        'waybill.error_reset_positions'=>'Ошибка сброса позиций накладной',
        'agent.not_found'=>'Агент не найден',
        'store.not_found'=>'Склад не найден',
        'store.is_category'=>'Это категория, а не склад',
        '{type} not found'=>'{type} не найден',
        'order_not_found'=>'Заказ не найден',
        'Available only for EDO documents and Supplier Consignment Notes. '=>'Доступно только для документов ЭДО и Накладных поставщика',
        'Must be \ "Sent by Supplier \" '=>'Должен быть статус \"Отправлено поставщиком\"',
        'No EDI Access Options '=>'Отсутствуют параметры доступа к EDI',
        'An error occurred while sending data '=>'В процессе отправки данных возникла ошибка',
        'integration.email.setting_not_found'=>'Настройки email не найдены',
        'integration.email.bad_organization_id'=>'Не корректный идентификатор организации',
        'guide.vendor_create_denied'=>'Поставщик не может создавать шаблоны',
        'order_content_not_found'=>'Состав заказа не найден',
        'guide.empty_goods'=>'Пустой шаблон',
        'guide.operation_not_found|{operation}'=>'Операция не найдена|{operation}',
        'guide.max_products|1000'=>'Максимальное количество позиций|1000',
        'guide.not_add_product_in_guide|{product}'=>'Невозможно добавить продукт в шаблон|{product}',
        'guide.product_not_found|{product}'=>'Позиция не найдена|{product}',
        'guide.not_found'=>'Шаблон не найден',
        'guide.access_denied'=>'Нет доступа к шаблону',
        'integration_setting.not_found'=>'Настройки интеграции не найдены',
        'setting.main_org_equal_child_org'=>'Настройки основной организации совпадают с дочерней',
        'Dont have active license for this service'=>'У вас нет активной лицензии на эту услугу ',
        'waybill.content_not_found'=>'Не найден состав накладной',
        'waybill.outer_product_not_found'=>'Позиция в учетной системе не найдена',
        'waybill.content_exists'=>'Позиция уже есть в накладной',
        'Filter "price" not array'=>'Фильтр «Цена» не массив',
        'product_access_denied'=>'Нет доступа к продукту',
        'wrong_param{param}'=>'Некорректный параметр|{param}',
        'Notification not found'=>'Уведомление не найдено',
        'order.access.change.denied'=>'Нет прав на изменение заказа',
        'order.access.change.canceled_status'=>'Нет прав на отмену заказа',
        'order.discount.types'=>'Некорректный тип скидки',
        'order.discount.100_percent'=>'Скидка должна быть менее 100%',
        'order.discount.empty_amount'=>'Пустое значение скидки',
        'error.request'=>'Ошибка в запросе',
        'order.discount.big_amount'=>'Слишком большой размер скидки',
        'order.delete_product_empty'=>'Не указана позиция для удаления',
        'order.bad_vendor|{vendor}'=>'Некорректный поставщик|{vendor}',
        'order.add_product_is_already_in_order|{propduct}'=>'Позиция уже есть в заказе|{product}',
        'order.edit_comment_access_denied'=>'Нет прав на редактирование комментария',
        'order.view_access_denied'=>'Нет прав на просмотр заказа',
        'order.edit_access_denied'=>'Нет прав на редактирование заказа',
        'order.already_done'=>'Заказ уже принят',
        'order.already_cancel'=>'Заказ уже отменен',
        'order_content.empty'=>'Заказ пуст',
        'bad_service_id_in_order|{id}|{var1} or {var2}'=>'Не правильный идентификатор сервиса в заказе|{id}|{var1} или  {var2}',
        'It is expected the three-digit code'=>'Ожидается трехзначный код ',
        'Must be a numeric value'=>'Должно быть числовое значение ',
        'is empty'=>'пустой',
        'queue or org_id parameters is empty'=>'queue или org_id parameters указан',
        'This section is available only for restaurants ... '=>'Раздел доступен только для ресторанов...',
        'This section is available only to suppliers ... '=>'Раздел доступен только для поставщиков...',
        'You can’t watch offers, only restaurants can ... '=>'Вы не можете смотреть предложения, могут только рестораны...',
        'You can not create an application, can only restaurants ... '=>'Вы не можете создавать заявки, могут только рестораны...',
        'You can not send an offer, available only to suppliers ... '=>'Вы не можете отправить предложение, доступно только поставщикам...',
        'You have already left a response '=>'Вы уже оставили отклик',
        'You are not a restaurant, go further ... '=>'Вы не ресторан, проходите дальше...',
        'You are already installed by the performer. '=>'Вы уже установлены исполнителем.',
        'An executive has already been assigned to this application. '=>'На эту заявку уже назначен исполнитель.',
        'request_not_found'=>'Заявка не найдена',
        'category_not_found'=>'Категория не найдена',
        'Request not active'=>'Заявка не активна',
        'You are not a restaurant, go further ... '=>'Вы не ресторан, проходите дальше...',
        'You can not watch other applications. '=>'Вы не можете смотреть чужие заявки.',
        'You can not see this application, it is outside your delivery area. '=>'Вы не можете видеть эту заявку, она вне зоны вашей доставки.',
        'Application closed. '=>'Заявка закрыта.',
        'Not found RequestCallback'=>'Не найдены отклики на заявку',
        'It is necessary to establish delivery regions. '=>'Необходимо установить регионы доставки.',
        'Not found RequestCallback|{id}'=>'Не найден отклик|{id}',
        'user_not_found'=>'Пользователь не найден',
        'This email is already present in the system. '=>'Данный Email уже присутствует в системе.',
        'Bad format. (+79112223344)'=>'Не правильный формат. (+79112223344)',
        'wait_sms_send|{sec}'=>'Ожидание отправки sms|{sec}',
        'organization not found'=>'Организация не найдена',
        'No rights to switch to this organization. '=>'Нет прав переключиться на эту организацию',
        'access denied.'=>'Нетдоступа',
        'No organizations available '=>'Нет доступных организаций',
        'vendor_not_found'=>'Поставщик не найден',
        'You are not working with this provider. '=>'Вы не работаете с этим поставщиком',
        'bad_old_password'=>'Не правильный старый пароль',
        'same_password'=>'Тот же пароль',
        'bad_password|{pass}'=>'Не правильный пароль|{pass}',
        'bad_format_phone'=>'Неправильный формат телефона',
        'bad_format_code'=>'Не правильный формат кода',
        'not_code_to_change_phone'=>'Невозможно поменять код',
        'bad_sms_code'=>'Не правильный SMS код',
        'you have no rights for this action'=>'У вас нет прав на это действие ',
        'no such users profile'=>'Нет такого профиля пользователя',
        'no such user relation'=>'Не найдена связь с пользователем',
        'user.wrong_agreement_name'=>'Неправильное название соглашения ',
        'user.cannot_disable_accepted_agreement'=>'Нельзя отменить принятое соглашение ',
        'vendor.you_are_not_working_with_this_supplier'=>'Вы не работаете с этим поставщиком ',
        'Field vendor_id mast be integer'=>'Поле vendor_id должно быть числом',
        'vendor.not_found_vendors'=>'Не найдены поставщики',
        'vendor.not_allow_editing'=>'Нет прав на редактирование этого поставщика',
        'vendor.not_you_editing'=>'Вы можете редактировать только свои данные',
        'vendor.is_not_vendor'=>'Не поставщик',
        'vendor.is_work'=>'Поставщик работает',
        'The download format is different from XLSX'=>'Формат загрузки отличается от формата XLSX ',
        'User with email: {email} found in our system, but he did not complete the registration. As soon as he goes through the supplier registration procedure, you can add him. '=>'Пользователь с емайлом: {email} найден у нас в системе, но он не завершил регистрацию. Как только он пройдет процедуру регистрации поставщика, вы сможете добавить его.',
        'auth_failed'=>'Ошибка аутентификации',
        'param_value_to_large|page_size|200'=>'Значение параметра слишком большое|page_size|200',
        'Bad request, data request is empty'=>'Некорректный запрос отсутствует request',
        'empty Email'=>'Пустой Email',
        'User with this Email was not found in the system. '=>'Пользователь с таким Email не найден в системе.',
        'wrong_param|{param}'=>'Неверный параметр|{param}',
        'Request Provider no allow This IP:{ip}'=>'Запрос поставщика не разрешает этот IP: {ip}',
        'empty Body in Request'=>'Пустое тело запроса',
        'Payment not found {body}'=>'Платеж не найден {body}',
        'page_not_found'=>'Страница не найдена',
        'iikoApi attribute not found:{attr}'=>'iikoApi атрибут не найден:{attr}',
        'Server response:{code}|{text}'=>'Код ответа сервера:{code}|{text}',
        'Response already recorded.'=>'Ответ уже записан',
        'User already recorded.'=>'Пользователь уже записан',
        'waybill.you_dont_have_order_content'=>'Нет содержимого заказа ',
        'waybill.you_dont_have_licenses_for_services'=>'Нет лицензии на эту услугу',
        'waybill.you_dont_have_order_content_for_waybills'=>'У Вас нет содержания заказа для накладных',
        'waybill.you_dont_have_mapped_products'=>'У Вас нет сопоставленных продуктов ',
        'waybill.no_map_for_create_waybill'=>'Нет сопоставлений для создания накладной ',
        'waybill.no_store_for_create_waybill'=>'Нет склада для создания накладной ',
        'waybill cannot adding waybill_content with id {id} '=>'Накладная не может добавить waybill_content с идентификатором {id}',
        'OrderContent dont exists with id {id}'=>'Содержание заказа не существует с идентификатором {id}',
        'waybill.order_content_allready_has_waybill_content {id}'=>'Контент заказа уже имеет контент накладной {id}',
        'choose_integration_service'=>'Выберите сервис для интеграции',
        'store.not_found'=>'Склад не найден',
        'Service was not recognized by task_id'=>'Task_id не был признан службой ',
        'document_has_not_path_to_order'=>'Документ не связан с заказом',
        'dictionary.act_write_off_number_error'=>'Акт не найден',
        'dictionary.request_error'=>'Ошибка запроса',
        'dictionary.egais_get_setting_error'=>'Неправильные настройки ЕГАИС',
        'dictionary.egais_type_document_error'=>'Неправильный тип документа ЕГАИС',
        'Not found operation service_id:{id} {denom}'=>'Не найден сервис: {id} {denom}',
        'startDate field is not specified'=>'Начальная дата неуказана',
        'Uuid is bad'=>'Некорректный uuid',
        'Uuid is required'=>'Uuid обязателен',
        'Uuid is required and must be array'=>'Uuid обязатален и может быть массив',
        'VSD does not belong to this organization: {uuid}'=>'ВСД не принадлежит данной организации: {uuid}',
        'You dont have available businesses, plz add relation to organization for your user'=>'У вас нет доступных предприятий, пожалуйста добавьте привязку предприятия к вашей организации'
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
