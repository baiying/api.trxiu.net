<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "anchor_news".
 *
 * @property integer $news_id
 * @property integer $anchor_id
 * @property string $content
 * @property string $images
 * @property integer $create_time
 * @property integer $comments
 * @property integer $unreadcoments
 * @property integer $status
 */
class AnchorNews extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'anchor_news';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['anchor_id', 'create_time', 'comments', 'unreadcoments', 'status'], 'integer'],
            [['content'], 'string', 'max' => 512],
            [['images'], 'string', 'max' => 1024],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'news_id' => 'News ID',
            'anchor_id' => 'Anchor ID',
            'content' => 'Content',
            'images' => 'Images',
            'create_time' => 'Create Time',
            'comments' => 'Comments',
            'unreadcoments' => 'Unreadcoments',
            'status' => 'Status',
        ];
    }
}
