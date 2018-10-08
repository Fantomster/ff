<?php

namespace common\models\search;

use Yii;
use yii\db\Exception;
use yii\data\ActiveDataProvider;
use common\models\IntegrationInvoice;
use common\models\Organization;
use common\models\User;

/**
 * This is the model class for table "integration_invoice".
 *
 * @property int $id
 * @property int $organization_id
 * @property int $integration_setting_from_email_id
 * @property string $number
 * @property string $date
 * @property string $email_id
 * @property int $order_id
 * @property string $file_mime_type
 * @property string $file_content
 * @property string $file_hash_summ
 * @property string $created_at
 * @property string $updated_at
 * @property string $name_postav
 * @property string $consignee
 * @property int $vendor_id
 *
 * @property IntegrationInvoiceContent[] $Content
 * @property Organization $organization
 * @property IntegrationSettingFromEmail $integrationSettingFromEmail
 */
class IntegrationInvoiceSearch extends IntegrationInvoice
{
    public $date_from;
    public $date_to;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'integration_invoice';
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date', 'created_at', 'total', 'updated_at', 'date_from', 'date_to', 'number', 'name_postav', 'consignee'], 'safe'],

        ];
    }

    /**
     * Search
     * @param array $params
     * @return ActiveDataProvider
     */

    /**
     * Search
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {

        $organization = Organization::findOne(User::findOne(Yii::$app->user->id)->organization_id)->id;
        $this->load($params);

        $query = IntegrationInvoice::find()
            ->where(['organization_id' => $organization]);

        $from = \DateTime::createFromFormat('d.m.Y H:i:s', $this->date_from . " 00:00:00");
        if ($from) {
            $t1_f = $from->format('Y-m-d H:i:s');
        }
        $to = \DateTime::createFromFormat('d.m.Y H:i:s', $this->date_to . " 00:00:00");
        if ($to) {
            $to->add(new \DateInterval('P1D'));
            $t2_f = $to->format('Y-m-d H:i:s');
        }

        if (isset($t1_f)) {
            $query->andFilterWhere(['>=', $this->tableName() . '.date', $t1_f]);
        }
        if (isset($t2_f)) {
            $query->andFilterWhere(['<=', $this->tableName() . '.date', $t2_f]);
        }

        if (isset($this->number)) {
            $query->andFilterWhere(['=', $this->tableName() . '.number', $this->number]);
        }

        if (isset($this->name_postav)) {
            if (strlen($this->name_postav) > 0) {
                $query->andWhere($this->tableName() . '.name_postav like "%' . $this->name_postav . '%"');
            }
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
            'pagination' => ['pageSize' => 20],
        ]);

        return $dataProvider;
    }
}
