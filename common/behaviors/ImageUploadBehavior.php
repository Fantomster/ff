<?php

namespace common\behaviors;

use Imagine\Image\ManipulatorInterface;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\BaseActiveRecord;
use yii\helpers\ArrayHelper;
use yii\imagine\Image;
use yii\web\UploadedFile;

/**
 * Description of ImageUploadBehavior
 *
 * @author sharaf
 */
class ImageUploadBehavior extends UploadBehavior {
    /**
     * @var string
     */
    public $placeholder;
    /**
     * @var boolean
     */
    public $createThumbsOnSave = true;
    /**
     * @var boolean
     */
    public $createThumbsOnRequest = false;
    /**
     * @var array the thumbnail profiles
     * - `width`
     * - `height`
     * - `quality`
     */
    public $thumbs = [
        'thumb' => ['width' => 90, 'height' => 90, 'quality' => 90],
    ];
    /**
     * @var string|null
     */
    public $thumbPath;
    /**
     * @var string|null
     */
    public $thumbUrl;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->createThumbsOnSave) {
            if ($this->thumbPath === null) {
                $this->thumbPath = $this->path;
            }
            if ($this->thumbUrl === null) {
                $this->thumbUrl = $this->url;
            }

            foreach ($this->thumbs as $config) {
                $width = ArrayHelper::getValue($config, 'width');
                $height = ArrayHelper::getValue($config, 'height');
                if ($height < 1 && $width < 1) {
                    throw new InvalidConfigException(sprintf(
                        'Length of either side of thumb cannot be 0 or negative, current size ' .
                        'is %sx%s', $width, $height
                    ));
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function beforeUpload()
    {
        //parent::beforeUpload();
//        if ($this->createThumbsOnSave) {
//            $this->createThumbs();
//        }
    }

    /**
     * @inheritdoc
     */
    protected function afterUpload()
    {
        parent::afterUpload();
        if ($this->createThumbsOnSave) {
            $this->createThumbs();
        }
    }

    public function isExists($attribute, $profile = 'thumb')
    {
        $thumbPath = $this->getThumbUploadPath($attribute, $profile);
        return $thumbPath !== null ? $this->resourceManager->fileExists($this->getResourceName($thumbPath)) : false;
    }

    /**
     * @throws \yii\base\InvalidParamException
     */
    protected function createThumbs()
    {
        $path = $this->_file->tempName; //$this->getUploadPath($this->attribute);
        foreach ($this->thumbs as $profile => $config) {
            $thumbPath = $this->getThumbUploadPath($this->attribute, $profile);
            if ($thumbPath !== null) {
                if (!$this->resourceManager->fileExists($this->getResourceName($thumbPath))) {
                    $this->generateImageThumb($config, $path, $thumbPath);
                }
            }
        }
    }

    /**
     * @param string $attribute
     * @param string $profile
     * @param boolean $old
     * @return string
     */
    public function getThumbUploadPath($attribute, $profile = 'thumb', $old = false)
    {
        /** @var BaseActiveRecord $model */
        $model = $this->owner;
        $path = $this->resolvePath($this->thumbPath);
        $attribute = ($old === true) ? $model->getOldAttribute($attribute) : $model->$attribute;
        $filename = $attribute ? $this->getThumbFileName($attribute, $profile) : null;

        return $filename ? Yii::getAlias($path . '/' . $filename) : null;
    }

    /**
     * @param string $attribute
     * @param string $profile
     * @return string|null
     */
    public function getThumbUploadUrl($attribute, $profile = 'thumb')
    {
        $url = $this->getThumbUploadPath($this->attribute, $profile);
        $resourceName = $this->getResourceName($url);
        return $url ? $this->resourceManager->getUrl($resourceName) : null;
    }

    /**
     * @param $profile
     * @return string
     */
    protected function getPlaceholderUrl($profile)
    {
        list ($path, $url) = Yii::$app->assetManager->publish($this->placeholder);
        $filename = basename($path);
        $thumb = $this->getThumbFileName($filename, $profile);
        $thumbPath = dirname($path) . DIRECTORY_SEPARATOR . $thumb;
        $thumbUrl = dirname($url) . '/' . $thumb;

        if (!$this->resourceManager->fileExists($this->getResourceName($thumbPath))) {
            $this->generateImageThumb($this->thumbs[$profile], $path, $thumbPath);
        }

        return $thumbUrl;
    }

    /**
     * @inheritdoc
     */
    protected function delete($attribute, $old = false)
    {
        parent::delete($attribute, $old);

        $profiles = array_keys($this->thumbs);
        foreach ($profiles as $profile) {
            $path = $this->getThumbUploadPath($attribute, $profile, $old);
            $this->resourceManager->delete($this->getResourceName($path));
        }
    }

    /**
     * @param $filename
     * @param string $profile
     * @return string
     */
    protected function getThumbFileName($filename, $profile = 'thumb')
    {
        return $profile . '-' . $filename;
    }

    /**
     * @param $config
     * @param $path
     * @param $thumbPath
     */
    protected function generateImageThumb($config, $path, $thumbPath)
    {
        $width = ArrayHelper::getValue($config, 'width');
        $height = ArrayHelper::getValue($config, 'height');
        $quality = ArrayHelper::getValue($config, 'quality', 95);
        $mode = ArrayHelper::getValue($config, 'mode', ManipulatorInterface::THUMBNAIL_INSET);

        if (!$width || !$height) {
            $image = Image::getImagine()->open($path);
            $ratio = $image->getSize()->getWidth() / $image->getSize()->getHeight();
            if ($width) {
                $height = ceil($width / $ratio);
            } else {
                $width = ceil($height * $ratio);
            }
        }

        $maxWidth = ArrayHelper::getValue($config, 'maxWidth');
        $width = ($maxWidth && ($width > $maxWidth)) ? $maxWidth : $width;

        $maxHeight = ArrayHelper::getValue($config, 'maxHeight');
        $height = ($maxHeight && ($height > $maxHeight)) ? $maxHeight : $height;

        // Fix error "PHP GD Allowed memory size exhausted".
        ini_set('memory_limit', '512M');
        @mkdir(dirname($thumbPath), 0777, true);
        Image::thumbnail($path, $width, $height, $mode)->save($thumbPath, ['quality' => $quality]);

        $file = new UploadedFile();
        $file->error = UPLOAD_ERR_OK;
        $file->tempName = $thumbPath;
        $this->resourceManager->save($file, $this->getResourceName($thumbPath), $this->saveOptions);

        if (is_file($thumbPath)) {
            unlink($thumbPath);
        }
    }
    
}
