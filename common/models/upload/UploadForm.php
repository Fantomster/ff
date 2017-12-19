<?php
namespace common\models\upload;

use yii\base\Model;
use yii\web\UploadedFile;
use Yii;

class UploadForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $importFile;
    public $importType;

    public function rules()
    {
        return [
            [['importType'], 'integer'],
            //[['importType'], 'required'],
            [['importFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'xlsx', 'checkExtensionByMimeType' => false],
        ];
    }
    public function attributeLabels() {
        return [
            'importType' => Yii::t('app', 'common.models.import_type', ['ru'=>'Тип импорта']),
        ];
    }
    public function upload()
    {
        if ($this->validate()) {
            $path = 'upload/' . date("Ymd_Hms") . '.' . $this->importFile->extension;
            $this->importFile->saveAs($path);
            return $path;
        } else {
            return false;
        }
    }
    
}