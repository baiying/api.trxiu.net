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
use app\service\CurdService;
use app\service\AnchorService;

class FansController extends BaseController
{

    private $fansService;
    private $anchorService;

    /**
     * 根据Openid获取用户信息
     */
    public function actionGetFansInfoByOpenid(){

        $result = $data = $where = array();

        $this->checkMethod('get');
        $rule = [
            'openid' => ['type' => 'string', 'required' => TRUE],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());
        $where['wx_openid'] = $args['openid'];
        $this->fansService = new FansService();
        $result = $this->fansService->getFans($where);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_FAILED,$result['message'],$result['data']);
        }
        $fans = $result['data'];
        if($fans['anchor_id']!=0){
            $this->anchorService = new AnchorService();
            $anchorWhere['anchor_id'] = $fans['anchor_id'];
            $result = $this->anchorService->getAnchorInformation($anchorWhere);
            if($result['status']==false){
                $this->renderJson(ApiCode::ERROR_API_FAILED,$result['message'],$result['data']);
            }
            $fans['anchor'] = $result['data'];
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$fans);

    }

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
            'anchor_id' => ['type' => 'string', 'required' => FALSE],
            'page' => ['type' => 'int', 'required' => FALSE, 'default' => '1'],
            'size' => ['type' => 'int', 'required' => FALSE, 'default' => '10'],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());

        isset($args['wx_openid']) && $where['wx_openid'] = $args['wx_openid'];
        isset($args['wx_name']) && $where['wx_name'] = $args['wx_name'];
        isset($args['wx_thumb']) && $where['wx_thumb'] = $args['wx_thumb'];
        isset($args['anchor_id']) && $where['anchor_id'] = explode(",", $args['anchor_id']);
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
     * register
     * 注册用户信息
     * 如果该用户已经注册为粉丝，则直接返回粉丝ID
     * @param string $data['openid']    微信账号openid
     * @param string $data['nickname']  微信账号昵称
     * @param number $data['sex']       性别
     * @param string $data['country']   国家
     * @param string $data['province']  省份
     * @param string $data['city']      城市
     * @param string $data['headimgurl']头像地址
     * @param string $data['unionid']   unionid
     * @param string $data['access_token']  access_token
     * @param string $data['refresh_token'] refresh_token
     * @param string $data['expires_in']    授权有效时间（秒）
     * @return array
     */
    public function actionRegister() {
        $this->checkMethod('post');
        $rule = [
            'openid'        => ['type'=>'string', 'required'=>true, 'default'=>''],
            'nickname'      => ['type'=>'string', 'required'=>false, 'default'=>''],
            'sex'           => ['type'=>'int', 'required'=>false, 'default'=>1],
            'country'       => ['type'=>'string', 'required'=>false, 'default'=>''],
            'province'      => ['type'=>'string', 'required'=>false, 'default'=>''],
            'city'          => ['type'=>'string', 'required'=>false, 'default'=>''],
            'headimgurl'    => ['type'=>'string', 'required'=>false, 'default'=>''],
            'unionid'       => ['type'=>'string', 'required'=>false, 'default'=>''],
            'access_token'  => ['type'=>'string', 'required'=>false, 'default'=>''],
            'refresh_token' => ['type'=>'string', 'required'=>false, 'default'=>''],
            'expires_in'    => ['type'=>'int', 'required'=>false, 'default'=>0],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->post());
        
        // 查询用户信息是否已经注册，如果已注册则直接返回粉丝ID，否则将用户注册为粉丝并返回粉丝ID
        $curd = new CurdService();
        $res = $curd->fetchOne('app\models\Fans', ['wx_openid'=>$args['openid']]);
        if(!empty($res['data'])) {
            // 用户已注册则直接返回粉丝ID
            $fans = $res['data'];
            $this->renderJson(ApiCode::SUCCESS, $res['message'], ['fans_id'=>$fans->fans_id]);
            
        } else {
            // 用户未注册则用户信息入库
            $service = new FansService();
            $res = $service->register($args);
            if(!$res['status']) {
                $this->renderJson(ApiCode::ERROR_API_FAILED, '用户信息注册失败');
            } 
            $this->renderJson(ApiCode::SUCCESS, '用户信息注册成功', ['fans_id'=>$res['data']['fans_id']]);
        }
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
