<?php

namespace common\models;

use Yii;
use common\behaviors\ImageUploadBehavior;
use Imagine\Image\ManipulatorInterface;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "profile".
 *
 * @property int    $id         Идентификатор записи в таблице
 * @property int    $user_id    Идентификатор пользователя
 * @property string $created_at Дата и время создания записи в таблице
 * @property string $updated_at Дата и время последнего изменения записи в таблице
 * @property string $full_name  Полное имя пользователя
 * @property string $timezone   Временная зона, в которой работает пользователь
 * @property string $phone      Номер телефона пользователя
 * @property string $avatar     Название файла, содержащего аватарку
 * @property int    $sms_allow  Показатель состояния согласия на смс-оповещения (0 - не согласен, 1 - согласен)
 * @property int    $job_id     Идентификатор должности пользователя
 * @property int    $gender     Идентификатор гендерного пола пользователя
 * @property string $avatarUrl
 * @property string $miniAvatarUrl
 * @property User   $user
 */
class Profile extends \amnah\yii2\user\models\Profile
{

    const DEFAULT_AVATAR = '/images/no-avatar.jpg';

    const SMS_ALLOW = 1;
    const SMS_DISALLOW = 0;

    public $resourceCategory = 'avatar';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%profile}}';
    }


    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            [
                'class'     => ImageUploadBehavior::className(),
                'attribute' => 'avatar',
                'scenarios' => ['default'],
                'path'      => '@app/web/upload/temp/',
                'url'       => '/upload/temp/',
                'thumbs'    => [
                    'avatar' => ['width' => 90, 'height' => 90, 'mode' => ManipulatorInterface::THUMBNAIL_OUTBOUND],
                    'mini'   => ['width' => 25, 'height' => 25, 'mode' => ManipulatorInterface::THUMBNAIL_OUTBOUND],
                ],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = parent::rules();
        //$rules[] = [['full_name'], 'required'];
        $rules[] = [['full_name'], 'required', 'on' => ['complete'], 'message' => Yii::t('app', 'common.models.please', ['ru' => 'Пожалуйста, напишите, как к вам обращаться'])];
        $rules[] = [['full_name'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'];
        $rules[] = [['phone'], 'string', 'max' => 255];
        $rules[] = [['phone'], \borales\extensions\phoneInput\PhoneInputValidator::className(), 'on' => ['register', 'invite'], 'message' => Yii::t('app', 'common.models.incorrect_number', ['ru' => 'Некорректный номер'])];
        $rules[] = [['phone'], 'match', 'pattern' => '/^(\s*)?(\+)?([- _():=+]?\d[- _():=+]?){10,14}(\s*)?$/', 'message' => Yii::t('app', 'common.models.incorrect_number', ['ru' => 'Некорректный номер'])];
        $rules[] = [['phone'], 'default', 'value' => null];
        $rules[] = [['phone'], 'required', 'on' => ['register'], 'message' => Yii::t('app', 'common.models.fill_phone', ['ru' => 'Пожалуйста, введите свой номер телефона'])];
        $rules[] = [['phone'], 'required', 'on' => ['invite'], 'message' => Yii::t('app', 'common.models.plefse_phone', ['ru' => 'Пожалуйста, введите номер телефона'])];
        $rules[] = [['avatar'], 'image', 'extensions' => 'jpg, jpeg, gif, png'];
        $rules[] = [['sms_allow'], 'default', 'value' => 0];
        $rules[] = [['gender'], 'default', 'value' => 0];
        $rules[] = [['job_id'], 'default', 'value' => 0];

        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'full_name' => Yii::t('app', 'franchise.models.profile', ['ru' => 'ФИО']),
            'phone'     => Yii::t('app', 'franchise.models.profile.phone', ['ru' => 'Телефон']),
        ];
    }

    /**
     * @return string url to avatar image
     */
    public function getAvatarUrl()
    {
        return $this->avatar ? $this->getThumbUploadUrl('avatar', 'avatar') : self::DEFAULT_AVATAR;
    }

    /**
     * @return string
     */
    public function getMiniAvatarUrl()
    {
        return $this->avatar ? $this->getThumbUploadUrl('avatar', 'mini') : self::DEFAULT_AVATAR;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
