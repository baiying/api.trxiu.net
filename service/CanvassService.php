<?php
namespace app\service;
/**
 * 拉票服务类
 */
use Yii;
use app\service\BaseService;
use app\service\CurdService;
use app\models\Canvass;
use app\models\CanvassRed;
use app\models\Ballot;

class CanvassService extends BaseService {
    /**
     * createCanvass
     * 添加拉票
     * @param number $data['ballot_id']     活动ID
     * @param number $data['anchor_id']     主播ID
     * @param number $data['fans_id']       粉丝ID
     * @param number $data['charge']        充值金额
     * @param number $data['status']        拉票状态，1 有效，2 待支付，3 无效
     */
    public function createCanvass($data = []) {
        $modelName = "app\models\Canvass";
        $curd = new CurdService();
        // 获取活动信息
        $ballot = Ballot::findOne(['ballot_id'=>$data['ballot_id']]);
        // 生成拉票ID
        $data['canvass_id'] = Yii::$app->utils->createID(Yii::$app->id);
        // 计算充值手续费
        $service = new SettingService();
        $res = $service->setting();
        $setting = $res['data'];
        $data['fee'] = bcmul($data['charge'], $setting->fee, 0);
        $data['amount'] = $data['charge'] - $data['fee'];
        // 拉票时间
        $data['create_time'] = time();
        $data['active_time'] = time();
        $data['end_time'] = $ballot->end_time;
        $res = $curd->createRecord($modelName, $data);
        if($res['status']) {
            // 生成拉票红包
            $this->createRedPackage($data['canvass_id'], $data['amount']);
        } 
        return $res;
    }
    
    public function receiveRedpackage($ballotId, $canvassId, $fansId) {
        $canvass = Canvass::findOne(['ballot_id'=>$ballotId, 'canvass_id'=>$canvassId]);
        if(empty($canvass)) {
            $this->export(FALSE, '未查询到指定的拉票活动');
        }
        // 获取拉票活动中未被领取的红包
        $reds = $canvass->unreceiveReds;
        if(empty($reds)) {
            $this->export(FALSE, '红包已被抢光');
        }
        // 随机抽取一个红包
        $getRed = $reds[array_rand($reds)];
        // 将粉丝ID更新到红包中，表示该红包已被领取
        $getRed->fans_id = $fansId;
        $getRed->save();
        
    }
    /**
     * 生成红包
     * @param unknown $money
     * @return boolean
     */
    private function createRedPackage($canvassId, $money) {
        // 生成红包数组
        $min = 0.01;
        $remainMoney = $remainPackage = $money;
        $packages = [];
        for($i = 0; $i < $money; $i++) {
            if($remainPackage == 1) {
                $packageMoney = $remainMoney;
                $packages[] = $packageMoney;
                $remainPackage--;
                $remainMoney = 0;
            } else {
                $max = bcmul(bcdiv($remainMoney, $remainPackage, 2), 2, 2);
                $r = mt_rand(0, 100) / 100;
                $packageMoney = bcmul($max, $r, 2);
                bccomp($packageMoney, $min, 2) <= 0 && $packageMoney = 0.01;
                $packages[] = $packageMoney;
                $remainPackage--;
                $remainMoney -= $packageMoney;
            }
        }
        // 红包入库
        $curd = new CurdService();
        foreach($packages as $item) {
            $curd->createRecord("app\models\CanvassRed", ['amount'=>$item, 'canvass_id'=>$canvassId]);
        }
        return true;
    }
}