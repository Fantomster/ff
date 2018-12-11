<?php
/**
 * Created by PhpStorm.
 * User: Silukov Konstantin
 * Date: 8/14/2018
 * Time: 11:04 AM
 */

namespace common\helpers;

use yii\base\Model;
use yii\db\ActiveRecord;

/**
 * Class ModelsCollection
 * В дальнейшем возможно добавить \ArrayAccess и \Iterator интерфейсы
 *
 * @package common\helpers
 */
class ModelsCollection extends Model
{
    /**
     * Saves multiple models. Only for inserts.
     *
     * @param ActiveRecord[] $models
     * @return array
     */
    public function saveMultiple(array $models, bool $validate = true, string $dbName = 'db_api')
    {
        if (count($models) > 0) { // если массив дочерних бизнесов ненулевой,

            $firstModel = reset($models); //устанавливаем внутренний указатель массива на его первый элемент
            $columnsToInsert = $firstModel->attributes();   // узнаём названия столбцов таблицы, в которую будем записывать данные
            $rowsToInsert = [];

            foreach ($models as $model) { //для каждого дочернего бизнеса
                $isValid = false;
                if ($validate) {
                    $isValid = $model->validate();
                }
                if ($isValid || !$validate) {
                    if ($model->beforeSave(true)) { // если данные корректны
                        $rowsToInsert[] = array_values($model->attributes);// выбираются все значения массива
                    }
                } else { // если не все данные корректны, возвращаем ошибку
                    return ['success' => false, 'error' => $model->errors];
                }
            }

            $transaction = \Yii::$app->db->beginTransaction(); // начинаем транзакцию
            try {
                $numberAffectedRows = \Yii::$app->{$dbName}->createCommand()
                    ->batchInsert($firstModel->tableName(), $columnsToInsert, $rowsToInsert)
                    ->execute(); // вставляем в таблицу массив значений дочерних бизнесов
                $transaction->commit(); // заканчиваем транзакцию
            } catch (\Throwable $throwable) {
                $transaction->rollBack(); // если не удалось, возвращаем ошибку
                return ['success' => false, 'error' => $throwable->getMessage()];
            }

            $isSuccess = ($numberAffectedRows === count($models)); // если количество всталвенных строк равно количеству элементов массива дочерних бизнесов, то успешно

            if ($isSuccess) { // действия после успешного сохранения записей в таблице
                $changedAttributes = array_fill_keys($columnsToInsert, null);
                foreach ($models as $model) {
                    $model->afterSave(true, $changedAttributes);
                }
            }

            return ['success' => $isSuccess]; //возвращаем успешно
        } else { // если массив дочерних бизнесов нулевой, то возвращаем ошибку

            return ['success' => false, 'error' => 'count(models) < 0 or not array'];
        }
    }

}