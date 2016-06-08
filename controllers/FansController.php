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

    /**
     * 添加粉丝
     */
    public function actionAddFans(){
        $result = $data = $where = array();

        $this->checkMethod('get');
        $rule = [
            'wx_openid' => ['type' => 'string', 'required' => TRUE],
            'wx_name' => ['type' => 'string', 'required' => TRUE],
            'wx_sex' => ['type' => 'int', 'required' => FALSE],
            'wx_thumb' => ['type' => 'string', 'required' => TRUE],
            'anchor_id' => ['type' => 'int', 'required' => FALSE],
            'wx_access_token' => ['type' => 'string', 'required' => FALSE],
            'wx_refresh_token' => ['type' => 'string', 'required' => FALSE],
            'wx_access_token_expire' => ['type' => 'int', 'required' => FALSE],
            'wx_continue' => ['type' => 'string', 'required' => FALSE],
            'wx_province' => ['type' => 'string', 'required' => FALSE],
            'wx_city' => ['type' => 'string', 'required' => FALSE],
            'wx_unionid' => ['type' => 'string', 'required' => FALSE],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());

        //构建查询条件
        $data['wx_openid'] = $args['wx_openid'];//微信账号openid
        $data['wx_name'] = $args['wx_name'];//微信账号名称
        $data['wx_thumb'] = $args['wx_thumb'];//微信头像
        $data['wx_sex'] = isset($args['wx_sex']) ? $args['wx_sex'] : 3;//性别,1男，2女，3未知
        $data['anchor_id'] = isset($args['wx_sex']) ? $args['anchor_id'] : 0;//主播ID，主播用户该字段大于0，非主播用户为0
        isset($args['wx_access_token']) && $data['wx_access_token'] = $args['wx_access_token'];//微信账号access_token，有效期2小时
        isset($args['wx_refresh_token']) && $data['wx_refresh_token'] = $args['wx_refresh_token'];//微信账号refresh_token，有效期30天，到期后需要用户重新授权
        isset($args['wx_access_token_expire']) && $data['wx_access_token_expire'] = $args['wx_access_token_expire'];//access_token过期时间戳
        isset($args['wx_continue']) && $data['wx_continue'] = $args['wx_continue'];//国家
        isset($args['wx_province']) && $data['wx_province'] = $args['wx_province'];//省份
        isset($args['wx_city']) && $data['wx_city'] = $args['wx_city'];//城市
        isset($args['wx_unionid']) && $data['wx_unionid '] = $args['wx_unionid'];//微信账号unionid
        $data['create_time'] = time();
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
    public function actionGetFansList(){
        $result = $data = $where = $ext = array();

        $this->checkMethod('get');
        $rule = [
            'wx_openid' => ['type' => 'string', 'required' => FALSE],
            'wx_name' => ['type' => 'string', 'required' => FALSE],
            'wx_thumb' => ['type' => 'string', 'required' => FALSE],
            'page' => ['type' => 'int', 'required' => FALSE, 'default' => '1'],
            'size' => ['type' => 'int', 'required' => FALSE, 'default' => '10'],
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
    public function actionGetFansById(){
        $result = $data = $where = $ext = array();

        $this->checkMethod('get');
        $rule = [
            'fans_id' => ['type' => 'int', 'required' => TRUE],
            'wx_openid' => ['type' => 'string', 'required' => FALSE],
            'wx_name' => ['type' => 'string', 'required' => FALSE],
            'wx_thumb' => ['type' => 'string', 'required' => FALSE],
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
    public function actionUpFansById(){
        $result = $data = $where = $ext = array();

        $this->checkMethod('get');
        $rule = [
            'fans_id' => ['type' => 'string', 'required' => TRUE],
            'wx_openid' => ['type' => 'string', 'required' => FALSE],
            'wx_name' => ['type' => 'string', 'required' => FALSE],
            'wx_sex' => ['type' => 'int', 'required' => FALSE],
            'wx_thumb' => ['type' => 'string', 'required' => FALSE],
            'anchor_id' => ['type' => 'int', 'required' => FALSE],
            'wx_access_token' => ['type' => 'string', 'required' => FALSE],
            'wx_refresh_token' => ['type' => 'string', 'required' => FALSE],
            'wx_access_token_expire' => ['type' => 'int', 'required' => FALSE],
            'wx_continue' => ['type' => 'string', 'required' => FALSE],
            'wx_province' => ['type' => 'string', 'required' => FALSE],
            'wx_city' => ['type' => 'string', 'required' => FALSE],
            'wx_unionid' => ['type' => 'string', 'required' => FALSE],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());

        //构建查询条件
        $where['fans_id'] = $args['fans_id'];
        isset($args['wx_openid']) && $data['wx_openid'] = $args['wx_openid'];//微信账号openid
        isset($args['wx_name']) && $data['wx_name'] = $args['wx_name'];//微信账号名称
        isset($args['wx_thumb']) && $data['wx_thumb'] = $args['wx_thumb'];//微信头像
        isset($args['wx_sex']) && $data['wx_sex'] =  $args['wx_sex'];//性别,1男，2女，3未知
        isset($args['wx_sex']) &&$data['anchor_id'] =  $args['anchor_id'];//主播ID，主播用户该字段大于0，非主播用户为0
        isset($args['wx_access_token']) && $data['wx_access_token'] = $args['wx_access_token'];//微信账号access_token，有效期2小时
        isset($args['wx_refresh_token']) && $data['wx_refresh_token'] = $args['wx_refresh_token'];//微信账号refresh_token，有效期30天，到期后需要用户重新授权
        isset($args['wx_access_token_expire']) && $data['wx_access_token_expire'] = $args['wx_access_token_expire'];//access_token过期时间戳
        isset($args['wx_continue']) && $data['wx_continue'] = $args['wx_continue'];//国家
        isset($args['wx_province']) && $data['wx_province'] = $args['wx_province'];//省份
        isset($args['wx_city']) && $data['wx_city'] = $args['wx_city'];//城市
        isset($args['wx_unionid']) && $data['wx_unionid '] = $args['wx_unionid'];//微信账号unionid

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
    public function actionDelFansById(){
        $result = $data = $where = $ext = array();

        $this->checkMethod('get');
        $rule = [
            'fans_id' => ['type' => 'string', 'required' => TRUE],
            'wx_openid' => ['type' => 'string', 'required' => FALSE],
            'wx_name' => ['type' => 'string', 'required' => FALSE],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());

        //构建查询条件
        $where['fans_id'] = $args['fans_id'];
        isset($args['wx_openid']) && $where['wx_openid'] = $args['wx_openid'];
        isset($args['wx_name']) && $where['wx_name'] = $args['wx_name'];

        $this->fansService = new FansService();
        $result = $this->fansService->delFans($where);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_NOTEXIST,$result['message'],$result['data']);
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$result['data']);

    }
}