<?php 
namespace app\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;
class TariffConnectMailForm extends Model
{    
    public $tariff_id;
    public $file;
    public $address_id;

    public function rules()
    {
        return [
            [['tariff_id', 'address_id'], 'required'],
            [['tariff_id', 'address_id',], 'integer'],
            [['file'], 'file', 'checkExtensionByMimeType' => true, 'skipOnEmpty' => false, 'extensions' => '',]
        ];
    }

    public function upload()
    {
        if ($this->validate()) {
            $filename = 'tmp/'.time().'_' . $this->file->baseName . '.' . $this->file->extension;
            $this->file->saveAs($filename);
            return $filename;
        } else {
            return '';
        }
    }
}
