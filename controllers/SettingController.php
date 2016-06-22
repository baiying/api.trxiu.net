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
        $args = [];
        Yii::$app->request->post('fee') && $args['fee'] = floatval(Yii::$app->request->post('fee'));
        Yii::$app->request->post('rule_vote') && $args['rule_vote'] = Yii::$app->request->post('rule_vote');
        Yii::$app->request->post('rule_red') && $args['rule_red'] = Yii::$app->request->post('rule_red');
        $service = new SettingService();
        $res = $service->update($args);
        if($res['status']) {
            $this->renderJson(ApiCode::SUCCESS, $res['message'], $res['data']);
        } else {
            $this->renderJson(ApiCode::ERROR_API_FAILED, $res['message'], $res['data']);
        }
    }
}