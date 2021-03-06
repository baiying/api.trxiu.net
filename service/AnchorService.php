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
    public function addAnchor($data,$fans_id){

        $this->anchor = new Anchor();
        $this->fans = new Fans();
        $where['fans_id'] = $fans_id;
        $fans = Fans::findOne(['fans_id'=>$fans_id,'anchor_id'=>0]);
        if(!$fans){
            return $this->export(false,'粉丝已关联主播或粉丝信息不存在');
        }
        $this->anchor->attributes = $data;
        if(!$this->anchor->validate()) {
            return $this->export(false,'属性验证失败',$this->anchor->errors);
        }
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {

            $data['create_time'] = time();
            foreach ($data as $key => $value){
                $this->anchor->$key = $value;
            }
            $result = $this->anchor->save();
            if(!$result){
                return $this->export(false,'插入失败',$result);
            }
            $anchor_id = $this->anchor->attributes['anchor_id'];
            $fans->anchor_id = $anchor_id;
            $result = $fans->save();

            $transaction->commit();
        } catch(Exception $e) {
            $transaction->rollBack();
            $result = false;
        }
        if(!$result){
            return $this->export(false,'操作失败',$result);
        }
        return $this->export(true,'成功',$result);
    }

    /**
     * 修改主播资料
     */
    public function updateAnchor($anchor_id,$data){
        $anchorData = $fansData = array();
        $this->anchor = new Anchor();
        $this->fans = new Fans();
        //验证粉丝
        $this->fans = Fans::findOne(['anchor_id'=>$anchor_id]);
        if(!$this->fans){
            return $this->export(false,'粉丝已关联主播或粉丝信息不存在');
        }
        //验证主播
        $this->anchor = Anchor::findOne(['anchor_id'=>$anchor_id]);
        if(!$this->anchor){
            return $this->export(false,'该主播不存在');
        }
        isset($data['anchor_name']) && $fansData['wx_name'] = $data['anchor_name'];
        isset($data['thumb']) && $fansData['wx_thumb'] = $data['thumb'];
        isset($data['backimage']) && $anchorData['backimage'] = $data['backimage'];
        isset($data['qrcode']) && $anchorData['qrcode'] = $data['qrcode'];
        isset($data['platform']) && $anchorData['platform'] = $data['platform'];
        isset($data['broadcast']) && $anchorData['broadcast'] = $data['broadcast'];
        isset($data['description']) && $anchorData['description'] = $data['description'];
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            //更新主播表
            $anchorData['modify_time'] = time();
            foreach ($anchorData as $key => $value){
                $this->anchor->$key = $value;
            }
            $result = $this->anchor->save();
            if(!$result){
                return $this->export(false,'更新失败',$result);
            }
            //更新粉丝表
            foreach ($fansData as $key => $value){
                $this->fans->$key = $value;
            }
            $result = $this->fans->save();
            if(!$result){
                return $this->export(false,'更新失败',$result);
            }
            $transaction->commit();
        } catch(Exception $e) {
            $transaction->rollBack();
            $result = false;
        }
        if(!$result){
            return $this->export(false,'操作失败',$result);
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
            unset($ext['anchor']['limit']);
            $anchorList = $this->anchor->getList('*',$where['anchor'],$ext['anchor']);
            $fansList = $this->fans->getList('*',$where['fans']);
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
        unset($ext['fans']['limit']);
        $fansList = $this->fans->getList('*',$where['fans'],$ext['fans']);
        foreach ($fansList as $item){
            $where['anchor']['anchor_id'][] = $item['anchor_id'];
        }
        $anchorList = $this->anchor->getListAndLimit('*',$where['anchor'],$ext['anchor']);

        foreach ($anchorList['list'] as $key=>$value){
            $anchorList['list'][$key]['anchor_name'] = '';
            $anchorList['list'][$key]['thumb'] = '';
            foreach ($fansList as $item){
                if($item['anchor_id'] == $value['anchor_id']){
                    $anchorList['list'][$key]['anchor_name'] = $item['wx_name'];
                    $anchorList['list'][$key]['thumb'] = $item['wx_thumb'];
                    $anchorList['list'][$key]['last_time'] = $item['last_time'];
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
        $this->anchor = Anchor::findOne(['anchor_id'=>$data['anchor_id']]);
        if(!$this->anchor){
            return $this->export(false,'主播ID无效');
        }
        if(!$anchor) {
            return $this->export(false,'主播ID无效',$anchor);
        }
        $this->anchorNews->attributes = $data;
        if(!$this->anchorNews->validate()) {
            return $this->export(false,'属性验证失败',$this->anchorNews->errors);
        }
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            $data = (object)$data;
            $data->create_time = time();
            $result = $this->anchorNews->insertData($data);
            if(!$result){
                return $this->export(false,'发布动态失败',$result);
            }
            $this->anchor->news_time = time();
            $result = $this->anchor->save();
            if(!$result){
                return $this->export(false,'更新主播动态失败',$result);
            }
            $transaction->commit();
        } catch(Exception $e) {
            $transaction->rollBack();
            $result = false;
        }
        if(!$result){
            return $this->export(false,'操作失败',$result);
        }
        return $this->export(true,'成功',$result);
    }
    /**
     * 修改主播动态内容
     * @param unknown $newId
     * @param unknown $data
     */
    public function editAnchorNews($newsId, $data = []) {
        $curd = new CurdService();
        return $curd->updateRecord("app\models\AnchorNews", ['news_id'=>$newsId], $data);
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
        $newsWhere['status'] = 1;
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
            $newsWhere['status'] = 1;
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
        /*
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
        */
        $anchor = Anchor::findOne($where);
        $fans = $anchor->fans;
        $result = $anchor->attributes;
        $result['anchor_name'] = $fans->wx_name;
        $result['thumb'] = $fans->wx_thumb;
        $result['last_time'] = $fans->last_time;
        return $this->export(true,'成功',$result);
    }

    /**
     * 获取主播动态页
     */
    public function getAnchorNews($where,$ext){
        $this->anchorNews = new AnchorNews();
        $this->fans = new Fans();
        $where['status'] = 1;
        $result = $this->anchorNews->getListAndLimit('*',$where,$ext);
        if(!$result){
            return $this->export(false,'获取失败',$result);
        }
        foreach ($result['list'] as $key => $value){
            $commentWhere['news_id'] = $value['news_id'];
            //构建查询条件
            $commentExt['limit']['page'] = 1;
            $commentExt['limit']['size'] = 5;
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
     * 获取主播动态页
     */
    public function getNews($where){
        $this->anchorNews = new AnchorNews();
        $where['status'] = 1;
        $result = $this->anchorNews->getRow('*',$where);
        if(!$result){
            return $this->export(false,'获取动态失败',$result);
        }
        return $this->export(true,'成功',$result);
    }

    /**
     *获取动态评论
     */
    public function getNewsCommentList($where,$ext){
        $this->anchorComment = new AnchorComment();
        $this->fans = new Fans();
        $where['status'] = 1;
        $result = $this->anchorComment->getListAndLimit('*',$where,$ext);
        if(!$result){
            return $this->export(false,'获取失败',$result);
        }
        foreach ($result['list'] as $key => $value){
            $fans = $this->fans->getRow('*',['fans_id'=>$value['fans_id']]);
            $result['list'][$key]['fans'] = $fans;
            if($value['parent_comment_id'] != 0){
                $parent_comment = $this->anchorComment->getRow('*',['parent_comment_id'=>$value['parent_comment_id']]);
                $parent_comment_fans =$this->fans->getRow('*',['fans_id'=>$parent_comment['fans_id']]);
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
        if($result['parent_comment_id']){
            $parent_comment_where['comment_id'] = $result['parent_comment_id'];
            $parent_comment = $this->anchorComment->getRow('*',$parent_comment_where);
            if(!$parent_comment){
                return $this->export(false,'获取失败',$parent_comment);
            }
            $parent_fansWhere['fans_id'] = $parent_comment['fans_id'];
            $parent_fans = $this->fans->getRow('*',$parent_fansWhere);
            if(!$parent_fans){
                return $this->export(false,'获取用户信息失败',$parent_fans);
            }
            $result['parent_fansInformation'] = $parent_fans;
        }
        return $this->export(true,'成功',$result);
    }

    /**
     * 删除主播动态
     */
    public function delNews($news_id,$identity){
        $commentNum = 0;
        $this->anchorNews = new AnchorNews();
        $this->anchorComment = new AnchorComment();
        $news = $this->anchorNews->getRow('*',['news_id'=>$news_id,'status'=>1,'anchor_id'=>$identity]);
        if(!$news){
            return $this->export(false,'您没有权限操作该动态',$news);
        }
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            $result = $this->anchorNews->updateData(['status'=>2],['news_id'=>$news_id,'status'=>1]);
            if(!$result) return $this->export(false,'删除失败',$result);
            $commentNum = $this->anchorComment->updateData(['status'=>2],['news_id'=>$news_id,'status'=>1]);
            // ... 执行其他 SQL 语句 ...
            $transaction->commit();
        } catch(\Exception $e) {
            $transaction->rollBack();
            $result = false;
        }
        if(!$result){
            return $this->export(false,'操作失败',$news);
        }
        return $this->export(true,'操作成功，您已删除动态并删除了'.$commentNum.'条评论',$result);
    }


    /**
     * 删除动态评论
     */
    public function delNewsComment($comment_id,$identity){
        $parentCommentNum = 0;
        $this->anchorNews = new AnchorNews();
        $this->anchorComment = new AnchorComment();
        $comment = $this->anchorComment->getRow('*',['comment_id'=>$comment_id,'status'=>1,'fans_id'=>$identity]);
        if(!$comment){
            return $this->export(false,'您没有权限进行该操作',$comment);
        }
        $news_id = $comment['news_id'];
        $this->anchorNews = AnchorNews::findOne(['news_id'=>$news_id]);
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            $result = $this->anchorComment->updateData(['status'=>2],['comment_id'=>$comment_id,'status'=>1]);
            if(!$result) return $this->export(false,'删除失败',$result);
            if($this->anchorNews->comments > 0){
                $this->anchorNews->comments -= $result;
                $this->anchorNews->save();
            }
            // ... 执行其他 SQL 语句 ...
            $transaction->commit();
        } catch(\Exception $e) {
            $transaction->rollBack();
            $result = false;
        }
        if(!$result){
            return $this->export(false,'操作失败',$comment);
        }
        return $this->export(true,'操作成功',$result);
    }

    /**
     * 撤销主播
     */
    public function delAnchor($anchor_id){
        $this->fans = Fans::findOne(['anchor_id'=>$anchor_id]);
        if(!$this->fans){
            return $this->export(false,'该主播不存在');
        }
        $this->fans->anchor_id = 0;
        $result = $this->fans->save();
        if(!$result){
            return $this->export(false,'操作失败');
        }
        return $this->export(true,'操作成功',$result);
    }
}