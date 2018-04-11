<?php

namespace api_web\helpers;

use yii\web\UploadedFile;

/**
 * Description of UploadFile
 *
 * @author elbabuino
 */
class File {
    /**
     * @param string $data
     * @param string $extension
     * 
     * Return uploaded file temporary path
     * @return \yii\web\UploadedFile
     */
    public static function getFromBase64($data, $extension = null) {
        $temp_file1 = UploadedFile::getInstance($model, $this->attribute);
        $temp_data = $model->getAttribute($this->attribute);

        $data = substr($temp_data, strlen('data:image/png;base64,'));
        $mime_type = 'image/jpeg';
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
        $temp_filename_jpg = substr($temp_filename, 0, strrpos($temp_filename, '.')) . '.jpg';
        $temp_path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $temp_filename;
        $temp_path_jpg = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $temp_filename_jpg;
        @file_put_contents($temp_path, $data);

        Image::getImagine()->open($temp_path)->save($temp_path_jpg, ['jpeg_quality' => 85]);

        $upload = new UploadedFile();
        $upload->name = $temp_filename_jpg;
        $upload->tempName = $temp_path_jpg;
        $upload->type = $mime_type;
        $upload->size = filesize($temp_path_jpg);//$uploadArr['size'];
        $upload->error = UPLOAD_ERR_OK;

        return $upload;
    }
}
