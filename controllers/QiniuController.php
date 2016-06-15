<?php
namespace app\controllers;

use Yii;
use Qiniu\Auth;
use app\components\ApiCode;

class QiniuController extends BaseController {
    
    private $accessKey = '-Lv2shihgIYB_VfI7gxiSDvrnrM2WJRqZ_6Nx4Co';
    private $secretKey = 'KD942VBuMchUketGTAXbPACPe2I9jyeHWLgAXx0m';
    private $bucket    = 'trxiu';
    
    /**
     * get-token
     * 获取上传token
     */
    public function actionGetToken() {
        $auth = new Auth($this->accessKey, $this->secretKey);
        $this->renderJson(ApiCode::SUCCESS, 'token生成成功', $auth->uploadToken($this->bucket));
    }
}