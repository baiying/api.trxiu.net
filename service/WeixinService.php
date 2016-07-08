<?php
namespace app\service;
/**
 * 微信服务类
 * @bai
 */
use Yii;
use app\service\BaseService;
use app\service\CurdService;

class WeixinService extends BaseService {
    /**
     * sendRedPackage
     * 发送红包
     * @param string $openid    接收红包的微信openid
     * @param string $company   公司名称
     * @param string $sender    发送者名称
     * @param string $wish      红包附言
     * @param string $actname   活动名称
     * @param string $remark    红包备注
     * @param number $amount    红包金额，单位“分”
     * @return Ambigous <multitype:, multitype:unknown string >
     */
    public function sendRedPackage($data = []) {
        if(!isset($data['openid'])) {
            return $this->export(false, '缺少openid');
        }
        if(!isset($data['company'])) {
            return $this->export(false, '缺少company');
        }
        if(!isset($data['sender'])) {
            return $this->export(false, '缺少sender');
        }
        if(!isset($data['wish'])) {
            return $this->export(false, '缺少wish');
        }
        if(!isset($data['amount']) || $data['amount'] < 100) {
            return $this->export(false, '缺少红包金额或红包金额小于1元');
        }
        !isset($data['actname']) && $data['actname'] = "";
        !isset($data['remark']) && $data['remark'] = "";
        
        require_once "../components/WXHongBao.php";
        $usrWXOpenId = $data['openid']; 
        $hb = new \WXHongBao();
        $hb->newhb($usrWXOpenId, $data['amount']); 
        $hb->setNickName($data['company']);
        $hb->setSendName($data['sender']);
        $hb->setWishing($data['wish']);
        $hb->setActName($data['actname']);
        $hb->setRemark($data['remark']);
        //发送红包
        if(!$hb->send()){ //发送错误
            return $this->export(false, $hb->err());
        }else{
            return $this->export(true, '红包发送成功');
        }
    }
}