<?php

use yii\db\Migration;

/**
 * Class m180926_081332_fix_translations_for_order
 */
class m180926_081332_fix_translations_for_order extends Migration
{

    public $translations = [
        'ru' => [
            'message' => [
                'frontend.controllers.del_two' => 'удалил {prod} из заказа',
                'frontend.controllers.order.change_three' => 'изменил количество {prod} с {oldQuan} {ed} на',
                'frontend.controllers.order.change_price' => 'изменил цену {prod} с {productPrice} {currencySymbol} на',
                'frontend.controllers.order.made_discount' => 'сделал скидку на заказ № {order_id} в размере:',
                'frontend.controllers.order.not_changed' => 'изначальная скидка сохранена для новых условий заказа №',
            ],
        ],
        'en' => [
            'message' => [
                'frontend.controllers.del_two' => 'deleted {prod} from the order',
                'frontend.controllers.order.change_three' => 'changed quantity {prod} from {oldQuan} {ed} to',
                'frontend.controllers.order.change_price' => 'changed price {prod} from {productPrice} {currencySymbol} to',
                'frontend.controllers.order.made_discount' => 'made a reduction to the order No. {order_id} equal to:',
                'frontend.controllers.order.not_changed' => 'the original discount was saved for the new order conditions №',
            ],
        ],
        'es' => [
            'message' => [
                'frontend.controllers.del_two' => 'ha eliminado {prod} del pedido',
                'frontend.controllers.order.change_three' => 'ha modificado el número de {prod} de {oldQuan} {ed} por',
                'frontend.controllers.order.change_price' => 'ha cambiado el precio {prod} de {productPrice} {currencySymbol} por',
                'frontend.controllers.order.made_discount' => 'ha hecho un descuento para el pedido No. {order_id} de:',
                'frontend.controllers.order.not_changed' => 'el descuento original se guardó para las nuevas condiciones de pedido №',
            ],
        ],
        'md' => [
            'message' => [
                'frontend.controllers.del_two' => 'a șters {prod} din comandă',
                'frontend.controllers.order.change_three' => 'a modificat cantitatea {prod} din {oldQuan} {ed} în',
                'frontend.controllers.order.change_price' => 'a modificat prețul la {prod} din {productPrice} {currencySymbol} în',
                'frontend.controllers.order.made_discount' => 'a oferit o reducere în mărime de    la comanda nr. {order_id}',
                'frontend.controllers.order.not_changed' => 'reducerea inițială a fost preconizată pentru noile condiții ale comenzii nr.',
            ],
        ],
        'ua' => [
            'message' => [
                'frontend.controllers.del_two' => 'видалив {prod} з замовлення',
                'frontend.controllers.order.change_three' => 'змінив кількість {prod} з {oldQuan} {ed} на',
                'frontend.controllers.order.change_price' => 'змінив ціну {prod} з {productPrice} {currencySymbol} на',
                'frontend.controllers.order.made_discount' => 'зробив знижку на замовлення № {order_id} в розмірі:',
                'frontend.controllers.order.not_changed' => 'початкова знижка збережена для нових умов замовлення №',
            ],
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        \console\helpers\BatchTranslations::insert($this->translations);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        \console\helpers\BatchTranslations::delete($this->translations);
    }

    /*
      // Use up()/down() to run migration code without a transaction.
      public function up()
      {

      }

      public function down()
      {
      echo "m180926_081332_fix_translations_for_order cannot be reverted.\n";

      return false;
      }
     */
}
