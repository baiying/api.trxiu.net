<?php
namespace app\controllers;

use Yii;
use app\controllers\BaseController;
use app\components\ApiCode;
use app\service\ManagerService;

class ManagerController extends BaseController {
    /**
     * register
     * 管理员注册接口
     * @param string $username  管理员登录名
     * @param string $password  管理员登录密码
     * @return array
     */
    public function actionRegister() {
        // 设置接口访问方式
        $this->checkMethod('post');
        // 设置接口参数白名单
        $rule = [
            'username'      => ['type' => 'string', 'required' => TRUE],
            'password'      => ['type' => 'string', 'required' => TRUE],
            'mobile'        => ['type' => 'string', 'default' => ''],
            'real_name'     => ['type' => 'string', 'default' => ''],
        ];
        $data = $this->getRequestData($rule, Yii::$app->request->post());
        // 注册管理员信息
        $service = new ManagerService();
        $res = $service->registerManager($data);
        if($res['status']) {
            // 返回注册成功结果（JSON格式）
            $this->renderJson(ApiCode::SUCCESS, "{$data['username']} 注册成功", $res['data']);
        } else {
            // 返回注册失败结果（JSON格式）
            $this->renderJson(ApiCode::ERROR_API_FAILED, $res['message'], $res['data']);
        }
    }
    /**
     * edit
     * 编辑管理员信息接口
     * @param string $username  管理员登录名
     * @param string $password  管理员登录密码
     * @param number $managerid 管理员ID
     * @return array
     */
    public function actionEdit() {
        // 设置接口访问方式
        $this->checkMethod('post');
        // 设置接口参数白名单
        $rule = [
            'username' => ['type' => 'string', 'required' => TRUE],
            'password' => ['type' => 'string', 'required' => TRUE],
            'mobile'   => ['type' => 'string', 'default' => ''],
            'real_name'=> ['type' => 'string', 'default' => ''],
            'managerid'=> ['type' => 'int', 'required' => TRUE]
        ];
        $data = $this->getRequestData($rule, Yii::$app->request->post());
        $managerId = $data['managerid'];
        unset($data['managerid']);
        // 注册管理员信息
        $service = new ManagerService();
        $res = $service->editManager($managerId, $data);
        if($res['status']) {
            // 返回注册成功结果（JSON格式）
            $this->renderJson(ApiCode::SUCCESS, "{$data['username']} 信息成功", $res['data']);
        } else {
            // 返回注册失败结果（JSON格式）
            $this->renderJson(ApiCode::ERROR_API_FAILED, $res['message'], $res['data']);
        }
    }
    /**
     * search
     * 查询管理员列表
     * @param string $username     管理员登录名
     * @param string $mobile       手机号
     * @param string $auth_token   auth_token
     * @param number $status       账号状态
	 * @param string $order        结果排序条件
	 * @param number $page         查询页码
	 * @param number $pagesize     每页记录条数
	 * @return array
     */
    public function actionSearch() {
        // 设置接口访问方式
        $this->checkMethod('get');
        // 设置接口参数白名单
        $ruleWhere = [
            'username'      => ['type' => 'string'],
            'mobile'        => ['type' => 'string'],
            'auth_token'    => ['type' => 'string'],
            'status'        => ['type' => 'int'], 
        ];
        $ruleArgs = [
            'order'         => ['type' => 'string', 'default' => 'manager_id ASC'],
            'page'          => ['type' => 'int', 'default' => 1],
            'pagesize'      => ['type' => 'int', 'default' => 20]
        ];
        // 处理传入的参数
        $where = $this->getRequestData($ruleWhere, Yii::$app->request->get());
        $args  = $this->getRequestData($ruleArgs, Yii::$app->request->get());
        // 查询符合条件的管理员数据
        $service = new ManagerService();
        $res = $service->search($where, $args);
        if($res['status']) {
            // 返回查询成功结果（JSON格式）
            $this->renderJson(ApiCode::SUCCESS, $res['message'], $res['data']['data'], intval($res['data']['count']));
        } else {
            // 返回查询失败结果（JSON格式）
            $this->renderJson(ApiCode::ERROR_API_FAILED, $res['message'], $res['data']);
        }
    }
    /**
     * login
     * 管理员登录接口
     * @param string $username
     * @param string $password
     */
    public function actionLogin() {
        $this->checkMethod('post');
        $rule = [
            'username' => ['type'=>'string', 'required'=>true],
            'password' => ['type'=>'string', 'required'=>true]
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->post());
        $service = new ManagerService();
        $res = $service->login($args['username'], $args['password']);
        if($res['status']) {
            $this->renderJson(ApiCode::SUCCESS, $res['message'], $res['data']);
        } else {
            $this->renderJson(ApiCode::ERROR_API_FAILED, $res['message']);
        }
    }
}