<?php
namespace app\controllers;
/**
 * 投票接口控制器类
 */
use Yii;
use app\controllers\BaseController;
use app\components\ApiCode;
use app\service\VoteService;

class VoteController extends BaseController {
    /**
     * add
     * 投一票
     * @param number $data['ballot_id']     活动ID，必填
     * @param number $data['anchor_id']     主播ID，必填
     * @param number $data['fans_id']       粉丝ID，必填
     * @param string $data['canvass_id']    拉票ID，选填
     * @param number $data['earn]           抽取拉票红包金额，选填
     * @param number $data['votes']         票数
     */
    public function actionAdd() {
        $this->checkMethod('post');
        $rule = [
            'ballot_id' => ['type'=>'int', 'required'=>TRUE],
            'anchor_id' => ['type'=>'int', 'required'=>TRUE],
            'fans_id'   => ['type'=>'int', 'required'=>TRUE],
            'canvass_id'=> ['type'=>'string', 'required'=>FALSE, 'default'=>''],
            'earn'      => ['type'=>'float', 'required'=>FALSE, 'default'=>0.00],
            'votes'     => ['type'=>'int', 'required'=>FALSE, 'default'=>1],
        ];
        $data = $this->getRequestData($rule, Yii::$app->request->post());
        $service = new VoteService();
        $res = $service->addOne($data);
        if($res['status']) {
            $this->renderJson(ApiCode::SUCCESS, $res['message'], $res['data']);
        } else {
            $this->renderJson(ApiCode::ERROR_API_FAILED, $res['message'], $res['data']);
        }
    }
    /**
     * search
     * 查询投票明细
     * @param number $data['ballot_id']         活动ID
     * @param number $data['anchor_id']         主播ID
     * @param number|string $data['fans_id']    粉丝ID
     * @param string $data['canvass_id']        拉票ID
     * @param string $data['order']             排序
     * @param number $data['page']              页码
     * @param number $data['pagesize']          页长
     * @param string $data['type']              记录类型， free 表示免费投票，pay 表示拉票投票
     */
    public function actionSearch() {
        $this->checkMethod('get');
        $rule = [
            'ballot_id' => ['type'=>'int', 'required'=>TRUE],
            'anchor_id' => ['type'=>'int', 'required'=>FALSE],
            'fans_id'   => ['type'=>'string', 'required'=>FALSE],
            'canvass_id'=> ['type'=>'string', 'required'=>FALSE],
            'page'      => ['type'=>'int', 'required'=>FALSE, 'default'=>1],
            'pagesize'  => ['type'=>'int', 'required'=>FALSE, 'default'=>20],
            'order'     => ['type'=>'string', 'required'=>FALSE, 'default'=>'vote_id DESC'],
            'type'      => ['type'=>'string', 'required'=>FALSE, 'default'=>'']
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());
        $type = $args['type'];
        unset($args['type']);
        $service = new VoteService();
        $res = $service->search($args, $type);
        if($res['status']) {
            // 补充查询结果中的粉丝姓名及头像
            $result = [];
            foreach($res['data']['data'] as $item) {
                $arr = $item->attributes;
                $arr['thumb'] = $item->fans->wx_thumb;
                $arr['name'] = $item->fans->wx_name;
                $result[] = $arr;
            }
            $this->renderJson(ApiCode::SUCCESS, $res['message'], $result, $res['data']['count']);
        } else {
            $this->renderJson(ApiCode::ERROR_API_FAILED, $res['message'], $res['data']);
        }
    }
}