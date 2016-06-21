<?php
namespace app\service;
/**
 * 系统参数服务类
 */
use Yii;
use app\service\BaseService;
use app\service\CurdService;
use app\models\Setting;

class SettingService extends BaseService {
    
    private function getSetting() {
        return Setting::findOne(['id'=>1]);
    }
    
    public function setting() {
        return $this->export(TRUE, 'OK', $this->getSetting());
    }
    
    public function update($data = []) {
        $modelName = "app\models\Setting";
        $curd = new CurdService();
        return $curd->updateRecord($modelName, ['id'=>1], $data);
    }
}