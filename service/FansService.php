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
    public function Test($select = '*',$where = array(),$ext = array()){
        $fans = new Fans;
//        return $fans->getListAndLimit($select,$where,$ext);
        $data['fans_id'] = 1;
        $data['wx_openid'] = 1;
        $data['wx_name'] = 1;
        $data['wx_thumb'] = 5;
        $data['create_time'] = time();
//        echo json_encode($data);exit;
        return $fans->insertOrUpdate($data,'fans_id',['wx_thumb']);
    }

    public function getList($select = '*',$where = array(),$ext = array()){
        $fans = new Fans;
        return $fans->getListAndLimit($select,$where,$ext);
    }

    public function addFans($data = array()){
        $fans = new Fans();
        $data = (object)$data;
        $where['wx_name'] = $data->wx_name;
        $result = $fans->getRow('*',$where);
        if($result) return ApiCode::ERROR_API_FAILED;
        return $fans->insertData($data);
    }

    public function getFans($where = array()){
        $fans = new Fans();
        return $fans->getRow('*',$where);
    }

    public function upFans($data = array(),$where = array()){
        $fans = new Fans();
        return $fans->updateData($data,$where);
    }

    public function delFans($where = array()){
        $fans = new Fans();
        return $fans->delRow($where);
    }

}