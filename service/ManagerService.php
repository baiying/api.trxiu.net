<?php
namespace app\service;
/**
 * 后台管理员账号服务类
 */
use Yii;
use app\service\BaseService;
use app\service\CurdService;
use app\models\Manager;

class ManagerService extends BaseService {
    /**
     * registerManager
     * 注册管理员
     * @param string $data['username']  登录名
     * @param string $data['password']  登录密码
     * @return array
     */
	public function registerManager($data = []) {
	    $modelName = "app\models\Manager";
		if(!isset($data['username'])) {
		    return $this->export(FALSE, '缺少username');
		}
		if(!isset($data['password'])) {
		    return $this->export(FALSE, '缺少password');
		}
		
		$service = new CurdService();
		
		// 判断用户名是否唯一
		$res = $service->fetchOne($modelName, ['username'=>$data['username']]);
		if(!empty($res['data'])) {
		    return $this->export(FALSE, '用户名已被注册');
		}
		// 注册新用户信息
		$data['password'] = $modelName::setPassword($data['password']);
		$data['auth_token'] = $modelName::generateAuthKey();
		$data['create_time'] = time();
		return $service->createRecord($modelName, $data);
	}
	/**
	 * editManager
	 * 修改Manager基本信息
	 * @param number $managerId   管理员ID 
	 * @param string $data['username']     管理员登录名
	 * @param string $data['password']     管理员密码
	 * @param string $data['mobile']       手机号
	 * @param string $data['real_name']    真实姓名
	 * @return array
	 */
	public function editManager($managerId, $data = []) {
	    $modelName = "app\models\Manager";
	    $service = new CurdService();
	    // 获取管理员信息
	    $resManager = $service->fetchOne($modelName, ['manager_id'=>$managerId]);
	    if(empty($resManager['data'])) {
	        return $this->export(FALSE, "未查询到管理员信息");
	    }
	    
	    $manager = $resManager['data'];
	    // 如果修改密码，对密码进行加密
	    if(isset($data['password'])) {
	        $data['password'] = $manager->setPassword($data['password']);
	    }
	    // 如果修改管理员登录名，需要进行排重
	    if(isset($data['username'])) {
	        $resCheck = $service->fetchOne($modelName, "manager_id!={$managerId} AND username={$data['username']}");
	        if(!empty($resCheck['data'])) {
	            return $this->export(FALSE, '该名称已被占用，请更换其他名称');
	        }
	    }
	    // 将数据更新到数据库
	    $res = $service->updateRecord($modelName, ['manager_id'=>$managerId], $data);
	    return $res;
	}
	/**
	 * search
	 * 查询管理员列表
	 * @param string|array $where      查询条件
	 * @param string $args['order']    结果排序条件
	 * @param number $args['page']     查询页码
	 * @param number $args['pagesize'] 每页记录条数
	 * @return array
	 */
	public function search($where, $args = []) {
	    $service = new CurdService();
	    return $service->fetchAll('app\models\Manager', $where, $args);
	}
	/**
	 * find
	 * 查询指定管理员基本信息
	 * @param string|array $where  查询条件
	 */
	public function find($where) {
	    $service = new CurdService();
	    return $service->fetchOne('app\models\Manager', $where);
	}
	/**
	 * login
	 * 验证登录名及密码的正确性
	 * @param string $username
	 * @param string $password
	 * @return Ambigous <multitype:, multitype:unknown string >
	 */
	public function login($username, $password) {
	    $curd = new CurdService();
	    $res = $curd->fetchOne('app\models\Manager', ['username'=>$username]);
	    if(empty($res['data'])) {
	        return $this->export(false, '登录名不存在');
	    }
	    $manager = $res['data'];
	    if(!$manager->validatePassword($password)) {
	        return $this->export(false, '登录名或密码错误');
	    }
	    // 更新最新登录时间
	    $manager->login_time = time();
	    $manager->save();
	    
	    return $this->export(true, '登录成功', $manager);
	}
	
}