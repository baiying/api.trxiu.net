<?php
namespace app\components;

use Yii;
use yii\base\Component;

class Utils extends Component {
    /**
     * 生成20位唯一ID
     * @param string $serverId  服务器ID，从config中读取id属性
     * @return string
     */
    public function createID($serverId) {
        list($msec, $sec) = explode(" ", microtime());
        return $sec . intval($msec * 1000) . mt_rand(1000, 9999) . Yii::$app->id;
    }
}