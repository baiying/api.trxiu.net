<?php
namespace app\controllers;

use Yii;
use app\controllers\BaseController;
use app\components\ApiCode;
use app\service\SettingService;

class SettingController extends BaseController {
    
    public function actionSetting() {
        $service = new SettingService();
        $res = $service->setting();
        $this->renderJson(ApiCode::SUCCESS, $res['message'], $res['data']);
    }
    
    public function actionUpdate() {
        $this->checkMethod('post');
        $rule = [
            'fee' => ['type'=>'float', 'required'=>false],
            'rule_vote' => ['type'=>'string', 'required'=>false],
            'rule_red' => ['type'=>'string', 'required'=>false],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->post());
        $service = new SettingService();
        $res = $service->update($data);
        if($res['status']) {
            $this->renderJson(ApiCode::SUCCESS, $res['message']);
        } else {
            $this->renderJson(ApiCode::ERROR_API_FAILED, $res['message']);
        }
    }
}