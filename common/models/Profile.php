<?php

namespace common\models;

use common\behaviors\ImageUploadBehavior;
use Imagine\Image\ManipulatorInterface;
use yii\helpers\ArrayHelper;

/**
 * Profile model
 *
 * @inheritdoc
 *
 * @property string $phone
 * @property string $sms_allow
 * @property string $avatar
 * 
 * @property string $avatarUrl
 * @property string $miniAvatarUrl
 */
class Profile extends \amnah\yii2\user\models\Profile {

    const DEFAULT_AVATAR = '/images/no-avatar.jpg';
    
    const SMS_ALLOW = 1;
    const SMS_DISALLOW = 0;
    
    
    public $resourceCategory = 'avatar';

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return ArrayHelper::merge(parent::behaviors(), [
                    [
                        'class' => ImageUploadBehavior::className(),
                        'attribute' => 'avatar',
                        'scenarios' => ['default'],
                        'path' => '@app/web/upload/temp/',
                        'url' => '/upload/temp/',
                        'thumbs' => [
                            'avatar' => ['width' => 90, 'height' => 90, 'mode' => ManipulatorInterface::THUMBNAIL_OUTBOUND],
                            'mini' => ['width' => 25, 'height' => 25, 'mode' => ManipulatorInterface::THUMBNAIL_OUTBOUND],
                        ],
                    ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        $rules = parent::rules();
        $rules[] = [['full_name'], 'required'];
        $rules[] = [['full_name'], 'required', 'on' => ['register', 'complete'], 'message' => 'Пожалуйста, напишите, как к вам обращаться'];
        $rules[] = [['full_name', 'phone'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'];
        $rules[] = [['phone'], 'string', 'max' => 255];
//        $rules[] = [['phone'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'];
//        $rules[] = [['phone'], 'match', 'pattern' => '/^\+7 \([0-9]{3}\) [0-9]{3} [0-9]{2} [0-9]{2}$/', 'message' => 'Некорректный номер'];
//        $rules[] = [['phone'], \borales\extensions\phoneInput\PhoneInputValidator::className()];
        $rules[] = [['phone'], 'default', 'value' => null];
        $rules[] = [['phone'], 'required', 'on' => ['register', 'complete'], 'message' => 'Пожалуйста, введите свой номер телефона'];
        $rules[] = [['avatar'], 'image', 'extensions' => 'jpg, jpeg, gif, png'];
        $rules[] = [['sms_allow'], 'default', 'value' => 0];
//        //переопределим сообщения валидации быдланским способом
//        $pos = array_search(['email', 'required'], $rules);
//        $rules[$pos]['message'] = 'Пожалуйста, напишите ваш адрес электронной почты';

        return $rules;
    }

    public function attributeLabels() {
        return [
            'full_name' => 'ФИО',
            'phone' => 'Телефон',
        ];
    }
    
    /**
     * @return string url to avatar image
     */
    public function getAvatarUrl()
    {
        return $this->avatar ? $this->getThumbUploadUrl('avatar', 'avatar') : self::DEFAULT_AVATAR;
    }
    
    public function getMiniAvatarUrl() {
        return $this->avatar ? $this->getThumbUploadUrl('avatar', 'mini') : self::DEFAULT_AVATAR;
    }
}
