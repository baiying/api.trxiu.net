<?php
/**
 * Created by PhpStorm.
 * User: Maple
 * Date: 16/5/27
 * Time: 10:06
 */

namespace app\service;

use Yii;
use app\service\BaseService;
use app\models\Fans;

class FansService extends BaseService
{
    public function Test($select = '*',$where = array(),$ext = array()){
        $fans = new Fans;
        return $fans->getListAndLimit($select,$where,$ext);
    }

}