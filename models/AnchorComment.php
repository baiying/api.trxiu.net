<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "anchor_comment".
 *
 * @property integer $comment_id
 * @property integer $fans_id
 * @property integer $news_id
 * @property integer $parent_comment_id
 * @property string $content
 * @property integer $create_time
 * @property integer $status
 */
class AnchorComment extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'anchor_comment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fans_id', 'news_id', 'parent_comment_id', 'create_time', 'status'], 'integer'],
            [['content'], 'string', 'max' => 256],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'comment_id' => 'Comment ID',
            'fans_id' => 'Fans ID',
            'news_id' => 'News ID',
            'parent_comment_id' => 'Parent Comment ID',
            'content' => 'Content',
            'create_time' => 'Create Time',
            'status' => 'Status',
        ];
    }
}
