<?php
/**
 * Created by PhpStorm.
 * User: Maple
 * Date: 16/5/27
 * Time: 10:14
 */

namespace app\controllers;

use Yii;
use app\components\ApiCode;
use app\service\FansService;
use app\controllers\BaseController;

class FansController extends BaseController
{

    private $fansService;

    public function actionTest(){
        $page = 1;
        $size = 10;
//        $where['wx_openid'] = 1;
        $where['wx_name'] = ['saq','sqw'];
//        $where['wx_thumb'] = 'dsds';
//        $ext['orderBy'] = 'fans_id DESC';
//        $ext['groupBy'] = 'fans_id';
//        $ext['limit']['page'] = $page!='' ?$page :1;
//        $ext['limit']['size'] = $size!='' ?$size :10;
        //计算limit数据
//        $ext['limit']['start'] = ($ext['limit']['page'] - 1) * $ext['limit']['size'];
        $this->fansService = new FansService();
        $a = $this->fansService->Test('*',$where);
        echo json_encode($a);exit;
    }

}