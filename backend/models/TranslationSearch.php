<?php

namespace backend\models;

use common\models\Message;
use common\models\SourceMessage;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * UserSearch represents the model behind the search form about `common\models\User`.
 */
class TranslationSearch extends SourceMessage
{

    public $category;
    public $message;
    public $translation;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return "{{%source_message}}";
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['message', 'category', 'translation'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $sourceMessageTable = SourceMessage::tableName();
        $messageTable = Message::tableName();

        $query = SourceMessage::find();
        $query->joinWith(['messages']);

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'sort'       => ['defaultOrder' => ['id' => SORT_DESC]],
            'pagination' => [
                'pageSize' => 20
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $dataProvider->sort->attributes['message'] = [
            'asc'  => ["$sourceMessageTable.message" => SORT_ASC],
            'desc' => ["$sourceMessageTable.message" => SORT_DESC],
        ];

        // grid filtering conditions
        $query->andFilterWhere(['like', 'message', $this->message])
            ->andFilterWhere(['like', 'category', $this->category])
            ->andFilterWhere(['like', "$messageTable.translation", $this->translation]);

        return $dataProvider;
    }
}
