<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;

class NotifyController extends Controller {

	private $token = "13651006864";

	public function actionHub() {
		$signature = Yii::$app->request->get('signature');
        $timestamp = Yii::$app->request->get('timestamp');
        $nonce = Yii::$app->request->get('nonce');
        $echostr = Yii::$app->request->get('echostr');
        		
		$token = $this->token;
		$tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			echo $echostr;
		}else{
			echo "";
		}
	}
}