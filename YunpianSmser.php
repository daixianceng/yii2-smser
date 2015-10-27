<?php

namespace xfstudio\smser;

use yii\base\NotSupportedException;

/**
 * 云片网
 * 
 * @author Cosmo <xfstudio@gmail.com>
 * @property string $state read-only state
 * @property string $message read-only message
 * Yii::$app->smser->send('15000000000', '短信内容');
 * Yii::$app->smser->send('15000000000', '短信内容', 'extend可传入回复号码，需申请');
 * 发送模板短信
 * Yii::$app->smser->sendByTemplate('15000000000', ['123456'], 1);
 */

class YunpianSmser extends Smser
{
    /**
     * @var string
     */
    public $apikey;

    /**
     * @inheritdoc
     */
    public $url = 'http://yunpian.com/v1/sms/send.json';

    /**
     * @inheritdoc
     */
    public function send($mobile, $content, $extend='', $uid='', $callback_url='')
    {
        if (parent::send($mobile, $content)) {
            return true;
        }

        $data = [
            'apikey' => $this->apikey,
            'mobile' => $mobile,
            'text' => $content
        ];

        if ($extend) $data['extend'] = $extend;
        if ($uid) $data['uid'] = $uid;
        if ($callback_url) $data['callback_url'] = $callback_url;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        $result = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($result);
        if ($json && is_object($json)) {
            $this->state = isset($json->code) ? (string) $json->code : null;
            $this->message = isset($json->msg) ? (string) $json->msg : null;
        }
        return $this->state === '0';
    }

    /**
     * @inheritdoc
     */
    public function sendByTemplate($mobile, $data, $id)
    {
        $this->tpl_send_sms($id,$data,$mobile);
        throw new NotSupportedException('云片网不支持发送此模板短信！');

    }

    /**
    * 智能匹配模版接口发短信
    * apikey 为云片分配的apikey
    * text 为短信内容
    * mobile 为接受短信的手机号
    */
    function send_sms($text, $mobile, $extend='', $uid='', $callback_url=''){
        $url="http://yunpian.com/v1/sms/send.json";
        $data['apikey'] = $this->apikey;
        $data['text'] = urlencode("$text");
        $data['mobile'] = urlencode("$mobile");
        if ($extend) $data['extend'] = $extend;
        if ($uid) $data['uid'] = $uid;
        if ($callback_url) $data['callback_url'] = $callback_url;
        $post_string = http_build_query($data);
        // $post_string="apikey=$apikey&text=$encoded_text&mobile=$mobile";
        
        return $this->sock_post($url, $post_string);
    }

    /**
    * 模板接口发短信
    * apikey 为云片分配的apikey
    * tpl_id 为模板id
    * tpl_value 为模板值
    * mobile 为接受短信的手机号
    */
    function tpl_send_sms($tpl_id, $tpl_value, $mobile){
        $apikey=$this->apikey;
        $url="http://yunpian.com/v1/sms/tpl_send.json";
        $encoded_tpl_value = urlencode("$tpl_value");  //tpl_value需整体转义
        $mobile = urlencode("$mobile");
        // $post_string="apikey=$apikey&tpl_id=$tpl_id&tpl_value=$encoded_tpl_value&mobile=$mobile";
        return $this->sock_post($url, $post_string);
    }

    /**
    * 获取回复短信
    * apikey 为云片分配的apikey
    * page_size 为每页个数，最大100个，默认20个
    */
    function pull_replay($page_size){
        $apikey=$this->apikey;
        $url="http://yunpian.com/v1/sms/pull_reply.json";
        $data['apikey'] = $this->apikey;
        $data['page_size'] = urlencode("$page_size");
        // $data['mobile'] = urlencode("$mobile");
        // if ($extend) $data['extend'] = $extend;
        // if ($uid) $data['uid'] = $uid;
        // if ($callback_url) $data['callback_url'] = $callback_url;
        $post_string = http_build_query($data);
        // $post_string="apikey=$apikey&tpl_id=$tpl_id&tpl_value=$encoded_tpl_value&mobile=$mobile";
        return $this->sock_post($url, $post_string);
    }

    /**
    * url 为服务的url地址
    * query 为请求串
    */
    function sock_post($url,$query){
        $data = "";
        $info=parse_url($url);
        $fp=fsockopen($info["host"],80,$errno,$errstr,30);
        if(!$fp){
            return $data;
        }
        $head="POST ".$info['path']." HTTP/1.0\r\n";
        $head.="Host: ".$info['host']."\r\n";
        $head.="Referer: http://".$info['host'].$info['path']."\r\n";
        $head.="Content-type: application/x-www-form-urlencoded\r\n";
        $head.="Content-Length: ".strlen(trim($query))."\r\n";
        $head.="\r\n";
        $head.=trim($query);
        $write=fputs($fp,$head);
        $header = "";
        while ($str = trim(fgets($fp,4096))) {
            $header.=$str;
        }
        while (!feof($fp)) {
            $data .= fgets($fp,4096);
        }
        return $data;
    }
}
