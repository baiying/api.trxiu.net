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
        $where['wx_name'] = [1];
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

    /**
     * 添加粉丝
     */
    public function actionAddfans(){
        $result = $data = $where = array();

        $this->checkMethod('get');
        $rule = [
            'wx_openid' => ['type' => 'string', 'required' => TRUE, 'default' => ''],
            'wx_name' => ['type' => 'string', 'required' => TRUE, 'default' => ''],
            'wx_thumb' => ['type' => 'string', 'required' => TRUE, 'default' => ''],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());

        //构建查询条件
        $data['wx_openid'] = $args['wx_openid'];
        $data['wx_name'] = $args['wx_name'];
        $data['wx_thumb'] = $args['wx_thumb'];
        $this->fansService = new FansService();
        $result = $this->fansService->addFans($data);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_FAILED,$result['message'],$result['data']);
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$result['data']);

    }

    /**
     * 获取粉丝列表
     */
    public function actionGetfanslist(){
        $result = $data = $where = $ext = array();

        $this->checkMethod('get');
        $rule = [
            'wx_openid' => ['type' => 'string', 'required' => FALSE, 'default' => ''],
            'wx_name' => ['type' => 'string', 'required' => FALSE, 'default' => ''],
            'wx_thumb' => ['type' => 'string', 'required' => FALSE, 'default' => ''],
            'page' => ['type' => 'int', 'required' => FALSE, 'default' => ''],
            'size' => ['type' => 'int', 'required' => FALSE, 'default' => ''],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());

        isset($args['wx_openid']) && $where['wx_openid'] = $args['wx_openid'];
        isset($args['wx_name']) && $where['wx_name'] = $args['wx_name'];
        isset($args['wx_thumb']) && $where['wx_thumb'] = $args['wx_thumb'];
        $ext['limit']['page'] = isset($args['page']) ? $args['page'] : 1;
        $ext['limit']['size'] = isset($args['size']) ? $args['size'] : 10;
        //计算limit数据
        $ext['limit']['start'] = ($ext['limit']['page'] - 1) * $ext['limit']['size'];
        $ext['orderBy'] = ['create_time'=>'desc'];

        $this->fansService = new FansService();
        $result = $this->fansService->getList('*',$where,$ext);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_FAILED,$result['message'],$result['data']);
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$result['data']);

    }

    /**
     * 根据ID获取粉丝
     */
    public function actionGetfansbyid(){
        $result = $data = $where = $ext = array();

        $this->checkMethod('get');
        $rule = [
            'fans_id' => ['type' => 'string', 'required' => TRUE, 'default' => ''],
            'wx_openid' => ['type' => 'string', 'required' => FALSE, 'default' => ''],
            'wx_name' => ['type' => 'string', 'required' => FALSE, 'default' => ''],
            'wx_thumb' => ['type' => 'string', 'required' => FALSE, 'default' => ''],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());

        //构建查询条件
        $where['fans_id'] = $args['fans_id'];
        isset($args['wx_openid']) && $where['wx_openid'] = $args['wx_openid'];
        isset($args['wx_name']) && $where['wx_name'] = $args['wx_name'];
        isset($args['wx_thumb']) && $where['wx_thumb'] = $args['wx_thumb'];

        $this->fansService = new FansService();
        $result = $this->fansService->getFans($where);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_FAILED,$result['message'],$result['data']);
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$result['data']);

    }

    /**
     * 更新粉丝信息
     */
    public function actionUpfansbyid(){
        $result = $data = $where = $ext = array();

        $this->checkMethod('get');
        $rule = [
            'fans_id' => ['type' => 'string', 'required' => TRUE, 'default' => ''],
            'wx_openid' => ['type' => 'string', 'required' => FALSE, 'default' => ''],
            'wx_name' => ['type' => 'string', 'required' => FALSE, 'default' => ''],
            'wx_thumb' => ['type' => 'string', 'required' => FALSE, 'default' => ''],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());

        //构建查询条件
        $where['fans_id'] = $args['fans_id'];
        isset($args['wx_openid']) && $data['wx_openid'] = $args['wx_openid'];
        isset($args['wx_name']) && $data['wx_name'] = $args['wx_name'];
        isset($args['wx_thumb']) && $data['wx_thumb'] = $args['wx_thumb'];

        $this->fansService = new FansService();
        $result = $this->fansService->upFans($data,$where);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_FAILED,$result['message'],$result['data']);
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$result['data']);
    }

    /**
     * 删除粉丝
     */
    public function actionDelfansbyid(){
        $result = $data = $where = $ext = array();

        $this->checkMethod('get');
        $rule = [
            'fans_id' => ['type' => 'string', 'required' => TRUE, 'default' => ''],
            'wx_openid' => ['type' => 'string', 'required' => FALSE, 'default' => ''],
            'wx_name' => ['type' => 'string', 'required' => FALSE, 'default' => ''],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());

        //构建查询条件
        $where['fans_id'] = $args['fans_id'];
        $where['wx_openid'] = $args['wx_openid'];
        $where['wx_name'] = $args['wx_name'];

        $this->fansService = new FansService();
        $result = $this->fansService->delFans($where);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_NOTEXIST,$result['message'],$result['data']);
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$result['data']);

    }
}