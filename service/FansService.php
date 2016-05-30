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

    public function getList($select = '*',$where = array(),$ext = array()){
        $fans = new Fans;
        $result = $fans->getListAndLimit($select,$where,$ext);
        if(!$result){
            return $this->export(false,'获取失败',$result);
        }
        return $this->export(true,'成功',$result);
    }

    public function addFans($data = array()){

        $fans = new Fans();
        $fans->attributes = $data;
        if(!$fans->validate()) {
            return $this->export(false,'属性验证失败',$fans->errors);
        }
        $data = (object)$data;
        $where['wx_name'] = $data->wx_name;
        $result = $fans->getRow('*',$where);
        if($result) return $this->export(false,'数据已存在',$result);
        $result = $fans->insertData($data);
        if(!$result){
            return $this->export(false,'插入失败',$result);
        }
        return $this->export(true,'成功',$result);
    }

    public function getFans($where = array()){
        $fans = new Fans();
        $result = $fans->getRow('*',$where);
        if(!$result){
            return $this->export(false,'获取失败',$result);
        }
        return $this->export(true,'成功',$result);
    }

    public function upFans($data = array(),$where = array()){
        $fans = new Fans();
        $result = $fans->updateData($data,$where);
        if(!$result){
            return $this->export(false,'更新失败',$result);
        }
        return $this->export(true,'成功',$result);
    }

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

}