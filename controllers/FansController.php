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