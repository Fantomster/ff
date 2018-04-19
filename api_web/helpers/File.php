<?php

namespace api_web\helpers;

use yii\web\UploadedFile;

/**
 *
 * @author elbabuino
 */
class File {
    /**
     * @param string $base64
     * @param string $type
     * @param string $extension
     * 
     * Return uploaded file temporary path
     * @return \yii\web\UploadedFile
     */
    public static function getFromBase64($base64, $type, $extension = '') {
        $data = substr($base64, strlen($type));
        $mime_type = $type;
        $data = base64_decode($data);

        $temp_filename = uniqid() . "." . $extension;
        $temp_path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $temp_filename;
        @file_put_contents($temp_path, $data);

        $upload = new UploadedFile();
        $upload->name = $temp_filename;
        $upload->tempName = $temp_path;
        $upload->type = $mime_type;
        $upload->size = filesize($temp_path);
        $upload->error = UPLOAD_ERR_OK;

        return $upload;
    }
    
}
