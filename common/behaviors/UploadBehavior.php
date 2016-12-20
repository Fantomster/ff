<?php

namespace common\behaviors;

use Yii;
use dosamigos\resourcemanager\ResourceManagerInterface;
use yii\web\UploadedFile;
use yii\base\InvalidParamException;
use yii\db\BaseActiveRecord;
use yii\helpers\FileHelper;

/**
 * Description of UploadBehavior
 *
 * @author sharaf
 */
class UploadBehavior extends \mongosoft\file\UploadBehavior {
    
    /**
     * @var ResourceManagerInterface handles resource to upload/uploaded.
     */
    public $resourceManager;
    
    /**
     * @var array options to resourceManager->save() function
     */
    public $saveOptions = [];
    
    private $_resourceNames = [];
    
    /**
     * @var UploadedFile the uploaded file instance.
     */
    protected $_file;    
    
    protected $_oldValue;
    
    protected $_deleting = false;
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->resourceManager = $this->resourceManager ?: Yii::$app->get('resourceManager');
    }

    /**
     * This method is invoked before validation starts.
     */
    public function beforeValidate()
    {
        /** @var BaseActiveRecord $model */
        $model = $this->owner;
        if (in_array($model->scenario, $this->scenarios)) {
            $file = $model->getAttribute($this->attribute);
            if (is_string($file) && (strpos($file, 'data:image/png;base64,') !== false)) {
                $this->_file = $this->getFromBase64();
            } elseif ($file instanceof UploadedFile) {
                $this->_file = $file;
            } else {
                if ($this->instanceByName === true) {
                    $this->_file = UploadedFile::getInstanceByName($this->attribute);
                } else {
                    $this->_file = UploadedFile::getInstance($model, $this->attribute);
                }
            }
            if (($this->_file instanceof UploadedFile) && ($this->_file->name)) {
                $this->_file->name = $this->getFileName($this->_file);
                $model->setAttribute($this->attribute, $this->_file);
            }
        }
    }
    
    /**
     * This method is called at the beginning of inserting or updating a record.
     */
    public function beforeSave()
    {
        /** @var BaseActiveRecord $model */
        $model = $this->owner;
        if (in_array($model->scenario, $this->scenarios)) {
            if ($model->getAttribute($this->attribute) == 'delete') {
                $this->delete($this->attribute, true);
                $this->_deleting = true;
                $model->setAttribute($this->attribute, null);
            } elseif ($this->_file instanceof UploadedFile) {
                if (!$model->getIsNewRecord() && $model->isAttributeChanged($this->attribute)) {
                    if ($this->unlinkOnSave === true) {
                        $this->delete($this->attribute, true);
                    }
                }
                $model->setAttribute($this->attribute, $this->_file->name);
            } else {
                // Protect attribute
                unset($model->{$this->attribute});
            }
        } else {
            if (!$model->getIsNewRecord() && $model->isAttributeChanged($this->attribute)) {
                if ($this->unlinkOnSave === true) {
                    $this->delete($this->attribute, true);
                }
            }
        }
    }

    /**
     * This method is called at the end of inserting or updating a record.
     * @throws \yii\base\InvalidParamException
     */
    public function afterSave()
    {
        /** @var BaseActiveRecord $model */
        $model = $this->owner;
        $value = $model->getAttribute($this->attribute);

        if (!$this->_deleting && ($this->_file instanceof UploadedFile)) {
            $path = $this->getUploadPath($this->attribute);
            if (is_string($path)) {
                $this->beforeUpload();
                $this->save($this->_file, $path);
                $this->owner->setAttribute($this->attribute, $this->_file->name);
                $this->afterUpload();
            } else {
                throw new InvalidParamException("Directory specified in 'path' attribute doesn't exist or cannot be created.");
            }
        }

        if (!$this->_deleting && empty($value)) {
            $model->setAttribute($this->attribute, $this->_oldValue);
        }
    }
    
    /**
     * Returns file url for the attribute.
     * @param string $attribute
     * @return string|null
     */
    public function getUploadUrl($attribute)
    {
        $url = $this->getUploadPath($attribute);
        $resourceName = $this->getResourceName($url);
        return $url ? $this->resourceManager->getUrl($resourceName) : null;
    }

    /**
     * Return resource file name (for dosamigos\resourcemanager)
     * @param $path
     * @return string
     */
    public function getResourceName($path)
    {
        if (!isset($this->_resourceNames[$path])) {
            $path_parts = pathinfo($path);
            $basename = $path_parts['filename'];//(isset($path_parts['dirname']) ? $path_parts['dirname'] . DIRECTORY_SEPARATOR : '') . $path_parts['filename'];
            $category = $this->getResourceCategory();
            $this->_resourceNames[$path] = ($category !== null ? $category . '/' : '')
                . md5($basename) . (isset($path_parts['extension']) ? '.' . $path_parts['extension'] : '');
        }

        return $this->_resourceNames[$path];
    }

    /**
     * Return resource category
     * @return string|null
     */
    public function getResourceCategory()
    {
        return property_exists($this->owner, 'resourceCategory') ? $this->owner->resourceCategory : null;
    }

    public function getFromBase64()
    {
        /** @var BaseActiveRecord $model */
        $model = $this->owner;

        $temp_file1 = UploadedFile::getInstance($model, $this->attribute);
        $temp_data = $model->getAttribute($this->attribute);

        $data = substr($temp_data, strlen('data:image/png;base64,'));
        $mime_type = 'image/png';
        $data = base64_decode($data);

        if ($temp_file1) {
            $uploadArr['name'] = $temp_file1->name;
            $uploadArr['size'] = $temp_file1->size;
        } else {
            $uploadArr['name'] = uniqid('img') . '.png';
            $uploadArr['size'] = strlen($data);
        }

        $temp_filename = $uploadArr['name'];
        $temp_filename = substr($temp_filename, 0, strrpos($temp_filename, '.')) . '.png';
        $temp_path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $temp_filename;
        @file_put_contents($temp_path, $data);

        $upload = new UploadedFile();
        $upload->name = $temp_filename;
        $upload->tempName = $temp_path;
        $upload->type = $mime_type;
        $upload->size = $uploadArr['size'];
        $upload->error = UPLOAD_ERR_OK;

        return $upload;
    }
    
    /**
     * Saves the uploaded file.
     * @param UploadedFile $file the uploaded file instance
     * @param string $path the file path used to save the uploaded file
     * @return boolean true whether the file is saved successfully
     */
    protected function save($file, $path)
    {
        return $this->resourceManager->save($file, $this->getResourceName($path), $this->saveOptions);
    }
    
    /**
     * Deletes old file.
     * @param string $attribute
     * @param boolean $old
     */
    protected function delete($attribute, $old = false)
    {
        $path = $this->getUploadPath($attribute, $old);
        $this->resourceManager->delete($this->getResourceName($path));
    }
}