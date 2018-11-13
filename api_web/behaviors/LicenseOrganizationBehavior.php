<?php

namespace api_web\behaviors;

use common\models\licenses\LicenseOrganization;
use common\models\OrganizationDictionary;
use common\models\OuterDictionary;
use yii\base\Behavior;
use yii\db\ActiveRecord;

class LicenseOrganizationBehavior extends Behavior
{
    /** @var LicenseOrganization $model */
    public $model;

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert'
        ];
    }

    public function afterInsert($event)
    {
        $this->createDictionary($event);
    }

    /**
     * Создание категорий, для подключаемой интеграции
     *
     * @param $event
     */
    public function createDictionary($event)
    {
        //Список справочников необходимых для интеграции
        $service_id = $this->model->license->service_id;
        //Если у лицензии есть сервис
        if (!empty($service_id)) {
            $dictionaryList = OuterDictionary::findAll(['service_id' => $service_id]);
            if (!empty($dictionaryList)) {
                //Проверяем, нет ли уже справочников для этой организации в этой интеграции
                //Если нет, создаем
                foreach ($dictionaryList as $dictionary) {
                    $exists = OrganizationDictionary::find()->where([
                        'org_id'       => $this->model->org_id,
                        'outer_dic_id' => $dictionary->id
                    ])->exists();

                    if (!$exists) {
                        $model = new OrganizationDictionary([
                            'outer_dic_id' => $dictionary->id,
                            'org_id'       => $this->model->org_id,
                            'status_id'    => OrganizationDictionary::STATUS_DISABLED,
                            'count'        => 0
                        ]);
                        $model->save();
                    }
                }
            }
        }
    }
}