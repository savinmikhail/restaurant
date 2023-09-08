<?php

namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;

class UploadForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $image;

    public function rules()
    {
        return [
            [
                [
                    'image',
                ],
                'file',
                'checkExtensionByMimeType' => false,
                'skipOnEmpty' => false,
                'extensions' => 'png,jpg,svg,jpeg,webp',
            ],
        ];
    }

    public function upload($name = '')
    {
        if ($this->validate()) {
            \yii\helpers\FileHelper::createDirectory(dirname(\Yii::getAlias('@webroot').'/upload/'.(($name != '') ? $name : $this->image->baseName).'.'.strtolower($this->image->extension)), 0777, 1);
            $this->image->saveAs('upload/'.(($name != '') ? $name : $this->image->baseName).'.'.strtolower($this->image->extension));

            return (($name != '') ? $name : $this->image->baseName).'.'.strtolower($this->image->extension);
        } else {
            return '';
        }
    }
}
