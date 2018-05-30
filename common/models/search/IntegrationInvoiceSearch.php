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
 *
 * @property IntegrationInvoiceContent[] $Content
 * @property Organization $organization
 * @property IntegrationSettingFromEmail $integrationSettingFromEmail
 */
class IntegrationInvoiceSearch extends IntegrationInvoice
{
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
          [['date', 'created_at', 'total', 'updated_at'], 'safe'],

        ];
    }

    /**
     * Search
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params): ActiveDataProvider
    {

        $organization = Organization::findOne(User::findOne(Yii::$app->user->id)->organization_id)->id;

        $query = IntegrationInvoice::find()
            ->where(['organization_id' => $organization]);


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=>[
                'defaultOrder'=>[
                    'updated_at' => SORT_DESC
                ],
            ]
        ]);


        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'date' => $this->date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

        ]);

     /*   $query->andFilterWhere(['like', 'slug', $this->slug])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'body', $this->body]);

     */

        return $dataProvider;
    }
}
