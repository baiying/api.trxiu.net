<?php
/**
 * Created by PhpStorm.
 * User: Maple
 * Date: 16/5/27
 * Time: 10:06
 */

namespace app\service;

use app\components\ApiCode;
use Yii;
use app\service\BaseService;
use app\models\Fans;

class FansService extends BaseService
{

    /**
     * @param string $select
     * @param array $where
     * @param array $ext
     * @return 获取列表
     */
    public function getList($select = '*',$where = array(),$ext = array()){
        $fans = new Fans;
        $result = $fans->getListAndLimit($select,$where,$ext);
        if(!$result){
            return $this->export(false,'获取失败',$result);
        }
        return $this->export(true,'成功',$result);
    }

    /**
     * @param array $data
     * @return 添加粉丝
     */
    public function addFans($data = array()){
        $modelName = "app\models\Fans";
        $curd = new CurdService();
        $res = $curd->fetchOne($modelName, ['wx_openid'=>$data['wx_openid']]);
        if(!empty($res['data'])) {
            return $this->export(false, '该用户已注册');
        }
        $data['create_time'] = time();
        return $curd->createRecord($modelName, $data);
    }

    /**
     * @param array $where
     * @return 获取粉丝(按条件获取一行)
     */
    public function getFans($where = array()){
        $fans = new Fans();
        $result = $fans->getRow('*',$where);
        if(!$result){
            return $this->export(false,'获取失败',$result);
        }
        return $this->export(true,'成功',$result);
    }

    /**
     * @param array $data
     * @param array $where
     * @return 更新粉丝信息
     */
    public function upFans($data = array(),$where = array()){
        $fans = new Fans();
        $fans->attributes = $data;
        if(!$fans->validate()) {
            return $this->export(false,'属性验证失败',$fans->errors);
        }
        $result = $fans->updateData($data,$where);
        if(!$result){
            return $this->export(false,'更新失败',$result);
        }
        return $this->export(true,'成功',$result);
    }

    /**
     * @param array $where
     * @return 删除粉丝
     */
    public function delFans($where = array()){
        $fans = new Fans();
        $result = $fans->delRow($where);
        if($result!=true){
            return $this->export(false,'操作失败',$result);
        }
        if($result == 404){
            return $this->export(false,'找不到数据',$result);
        }
        return $this->export(true,'成功',$result);
    }
    /**
     * 注册粉丝
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
    public function register($data = []) {
        if(!isset($data['openid'])) return $this->export(false, '缺少openid');
        $modelName = "app\models\Fans";
        $params = [];
        $params['wx_openid']        = $data['openid'];
        $params['wx_name']          = $data['nickname'];
        $params['wx_sex']           = $data['sex'];
        $params['wx_country']       = $data['country'];
        $params['wx_province']      = $data['province'];
        $params['wx_city']          = $data['city'];
        $params['wx_thumb']         = $data['headimgurl'];
        $params['wx_unionid']       = $data['unionid'];
        $params['wx_access_token']  = $data['access_token'];
        $params['wx_refresh_token'] = $data['refresh_token'];
        $params['wx_access_token_expire'] = time() + $data['expires_in'];
        $params['create_time']      = time();
        $curd = new CurdService();
        return $curd->createRecord($modelName, $params);
    }

}