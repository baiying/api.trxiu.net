<?php
/**
 * Created by PhpStorm.
 * User: Maple
 * Date: 16/6/2
 * Time: 13:51
 */

namespace app\service;

use Yii;
use app\components\ApiCode;
use app\service\BaseService;
use app\models\Anchor;
use app\models\AnchorNews;
use app\models\AnchorComment;
use app\models\Fans;

class AnchorService extends BaseService
{
    private $anchor;
    private $anchorNews;
    private $anchorComment;
    private $fans;

    /**
     * 添加主播
     */
    public function addAnchor($data){

        $this->anchor = new Anchor();
        $this->anchor->attributes = $data;
        if(!$this->anchor->validate()) {
            return $this->export(false,'属性验证失败',$this->anchor->errors);
        }
        $data = (object)$data;
        $data->create_time = time();
        $where['anchor_name'] = $data->anchor_name;
        $result = $this->anchor->getRow('*',$where);
        if($result) return $this->export(false,'主播昵称已被注册',$result);
        $result = $this->anchor->insertData($data);
        if(!$result){
            return $this->export(false,'插入失败',$result);
        }
        return $this->export(true,'成功',$result);
    }

    /**
     * 修改主播资料
     */
    public function updateAnchor($data,$where){

        $this->anchor = new Anchor();
        $this->anchor->attributes = $data;
        if(!$this->anchor->validate()) {
            return $this->export(false,'属性验证失败',$this->anchor->errors);
        }
        $data = (object)$data;
        $data->create_time = time();
        $result = $this->anchor->getRow('*',$where);
        if(!$result) return $this->export(false,'该主播不存在',$result);
        $result = $this->anchor->updateData($data,$where);
        if(!$result){
            return $this->export(false,'无变化',$result);
        }
        return $this->export(true,'成功',$result);
    }

    /**
     * 获取主播列表
     */
    public function getAnchorList($where,$ext){
        $this->anchor = new Anchor();
        $this->fans = new Fans();
        if(isset($ext['limit']['size']) && $ext['limit']['size']=='max'){
            unset($ext['limit']);
            $anchorList = $this->anchor->getList('*',$where,$ext);
            $fansList = $this->fans->getList('*',$where);
            foreach ($anchorList as $key=>$value){
                $anchorList[$key]['anchor_name'] = '';
                $anchorList[$key]['thumb'] = '';
                foreach ($fansList as $item){
                    if($item['anchor_id'] == $value['anchor_id']){
                        $anchorList[$key]['anchor_name'] = $item['wx_name'];
                        $anchorList[$key]['thumb'] = $item['wx_thumb'];
                    }
                }
            }
            if(!$anchorList){
                return $this->export(false,'获取失败',$anchorList);
            }
            return $this->export(true,'成功',$anchorList);
        }
        $anchorList = $this->anchor->getListAndLimit('*',$where,$ext);
        $fansList = $this->fans->getList('*',$where);
        foreach ($anchorList['list'] as $key=>$value){
            $anchorList['list'][$key]['anchor_name'] = '';
            $anchorList['list'][$key]['thumb'] = '';
            foreach ($fansList as $item){
                if($item['anchor_id'] == $value['anchor_id']){
                    $anchorList['list'][$key]['anchor_name'] = $item['wx_name'];
                    $anchorList['list'][$key]['thumb'] = $item['wx_thumb'];
                }
            }
        }
        if(!$anchorList){
            return $this->export(false,'获取失败',$anchorList);
        }
        return $this->export(true,'成功',$anchorList);
    }

    /**
     * 主播发布动态
     */
    public function addAnchorNews($data){

        $this->anchor = new Anchor();
        $this->anchorNews = new AnchorNews();
        $anchorWhere['anchor_id'] = $data['anchor_id'];
        $anchor = $this->anchor->getRow('*',$anchorWhere);
        if(!$anchor) {
            return $this->export(false,'主播ID无效',$anchor);
        }
        $this->anchorNews->attributes = $data;
        if(!$this->anchorNews->validate()) {
            return $this->export(false,'属性验证失败',$this->anchorNews->errors);
        }
        $data = (object)$data;
        $data->create_time = time();
        $result = $this->anchorNews->insertData($data);
        if(!$result){
            return $this->export(false,'发布动态失败',$result);
        }
        return $this->export(true,'成功',$result);
    }

    /**
     * 动态评论
     */
    public function newComment($data){

        $this->anchor = new Anchor();
        $this->anchorNews = new AnchorNews();
        $this->anchorComment = new AnchorComment();
        $this->fans = new Fans();
        $fansWhere['fans_id'] = $data['fans_id'];
        $fans = $this->fans->getRow('*',$fansWhere);
        if(!$fans) {
            return $this->export(false,'用户不存在',$fans);
        }
        $newsWhere['news_id'] = $data['news_id'];
        $news = $this->anchorNews->getRow('*',$newsWhere);
        if(!$news) {
            return $this->export(false,'动态不存在',$news);
        }
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            $ballotEAnchor = AnchorNews::findOne($newsWhere);
            $ballotEAnchor->comments += 1;
            $result = $ballotEAnchor->save();
            if(!$result){
                return $this->export(false,'评论添加失败',$result);
            }
            $data = (object)$data;
            $data->create_time = time();
            $this->anchorComment->attributes = $data;
            if(!$this->anchorComment->validate()) {
                return $this->export(false,'属性验证失败',$this->anchorComment->errors);
            }
            $result = $this->anchorComment->insertData($data);
            if(!$result){
                return $this->export(false,'评论失败',$result);
            }
            // ... 执行其他 SQL 语句 ...
            $transaction->commit();
        } catch(\Exception $e) {
            $transaction->rollBack();
            $result = false;
        }
        return $this->export(true,'成功',$result);

    }

    /**
     * 获取主播列表并获取最新一条动态
     */
    public function getAnchorListAndNews($where,$ext){
        $this->anchor = new Anchor();
        $this->anchorNews = new AnchorNews();
        $this->fans = new Fans();
        $anchorList = $this->anchor->getListAndLimit('*',$where,$ext);
        $fansList = $this->fans->getList('*',$where);
        foreach ($anchorList['list'] as $key=>$value){
            $anchorList['list'][$key]['anchor_name'] = '';
            $anchorList['list'][$key]['thumb'] = '';
            foreach ($fansList as $item){
                if($item['anchor_id'] == $value['anchor_id']){
                    $anchorList['list'][$key]['anchor_name'] = $item['wx_name'];
                    $anchorList['list'][$key]['thumb'] = $item['wx_thumb'];
                }
            }
        }
        if(!$anchorList){
            return $this->export(false,'获取失败',$anchorList);
        }
        $anchoridList = $newsWhere = $newsExt = array();
        foreach ($anchorList['list'] as $item){
            $anchoridList[] = $item['anchor_id'];
        }
        foreach ($anchoridList as $anchor_id){
            $newsWhere['anchor_id'] = $anchor_id;
            $newsExt['orderBy'] = ['create_time'=>'desc'];
            //计算limit数据
            $newsExt['limit']['size'] = 3;
            $newsExt['limit']['start'] = 0;
            $news = $this->anchorNews->getListAndLimit('*',$newsWhere,$newsExt);
            $anchorNews[$anchor_id] = $news['list'];
        }
        if(isset($anchorNews) && $anchorNews!=null){
            foreach ($anchorNews as $anchor_id => $item){
                foreach ($anchorList['list'] as $key => $value){
                    if($anchor_id == $value['anchor_id']){
                        $anchorList['list'][$key]['news'] = $item;
                    }
                }
            }
        }
        return $this->export(true,'成功',$anchorList);
    }

    /**
     * 获取主播资料页
     */
    public function getAnchorInformation($where){
        $this->anchor = new Anchor();
        $this->fans = new Fans();
        $result = $this->anchor->getRow('*',$where);
        if(!$result){
            return $this->export(false,'获取失败',$result);
        }
        $fans = $this->fans->getRow('*',$result['anchor_id']);
        $result['anchor_name'] = $fans['wx_name'];
        $result['thumb'] = $fans['wx_thumb'];
        return $this->export(true,'成功',$result);
    }

    /**
     * 获取主播动态页
     */
    public function getAnchorNews($where,$ext){
        $this->anchorNews = new AnchorNews();
        $this->fans = new Fans();
        $result = $this->anchorNews->getListAndLimit('*',$where,$ext);
        if(!$result){
            return $this->export(false,'获取失败',$result);
        }
        foreach ($result['list'] as $key => $value){
            $commentWhere['news_id'] = $value['news_id'];
        //构建查询条件
            $commentExt['limit']['page'] = 1;
            $commentExt['limit']['size'] = 3;
            //计算limit数据
            $commentExt['limit']['start'] = ($commentExt['limit']['page'] - 1) * $commentExt['limit']['size'];
            $commentExt['orderBy'] = ['create_time'=>'desc'];
            $comment = $this->getNewsCommentList($commentWhere, $commentExt);
            if($comment['status']==true){
                $result['list'][$key]['comment'] = $comment['data']['list'];
                $result['list'][$key]['comment_total'] = $comment['data']['total'];
            }else{
                $result['list'][$key]['comment'] = null;
                $result['list'][$key]['comment_total'] = 0;
            }
        }
        return $this->export(true,'成功',$result);
    }

    /**
     *获取动态评论
     */
    public function getNewsCommentList($where,$ext){
        $this->anchorComment = new AnchorComment();
        $this->fans = new Fans();
        $result = $this->anchorComment->getListAndLimit('*',$where,$ext);
        if(!$result){
            return $this->export(false,'获取失败',$result);
        }
        foreach ($result['list'] as $key => $value){
            $fans = $this->fans->getRow('*',['fans_id'=>$value['fans_id']]);
            $result['list'][$key]['fans'] = $fans;
            if($value['parent_comment_id'] != 0){
                $parent_comment = $this->anchorComment->getRow('*',$value['parent_comment_id']);
                $parent_comment_fans =$this->fans->getRow('*',$parent_comment['fans_id']);
                $result['list'][$key]['parent_comment_fans'] = $parent_comment_fans;
            }
        }
        return $this->export(true,'成功',$result);
    }

    /**
     * 获取当前评论信息
     */
    public function getCommentAndFans($where){
        $this->anchorComment = new AnchorComment();
        $this->fans = new Fans();
        $result = $this->anchorComment->getRow('*',$where);
        if(!$result){
            return $this->export(false,'获取失败',$result);
        }
        $fansWhere['fans_id'] = $result['fans_id'];
        $fans = $this->fans->getRow('*',$fansWhere);
        if(!$fans){
            return $this->export(false,'获取用户信息失败',$fans);
        }
        $result['fansInformation'] = $fans;
        return $this->export(true,'成功',$result);
    }
}