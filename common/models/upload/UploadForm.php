<?php
namespace common\models\upload;

use yii\base\Model;
use yii\web\UploadedFile;

class UploadForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $importFile;

    public function rules()
    {
        return [
            [['importFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'xlsx'],
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