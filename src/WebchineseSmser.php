<?php

namespace daixianceng\smser;

use yii\base\NotSupportedException;

/**
 * 中国网建
 * 
 * @author Cosmo <daixianceng@gmail.com>
 * @property string $password write-only password
 * @property string $state read-only state
 * @property string $message read-only message
 */
class WebchineseSmser extends Smser
{
    /**
     * @inheritdoc
     */
    public $url = 'http://utf8.sms.webchinese.cn/';
    
    /**
     * @inheritdoc
     */
    public function send($mobile, $content)
    {
        if (parent::send($mobile, $content)) {
            return true;
        }
        
        $data = [
            'uid' => $this->username,
            'key' => $this->password,
            'smsMob' => $mobile,
            'smsText' => $content
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        
        $this->state = (string) curl_exec($ch);
        curl_close($ch);
        
        $success = false;
        switch ($this->state) {
            case '' :
            case '-1' :
                $this->message = '没有该用户账户';
                break;
            case '-2' :
                $this->message = '接口密钥不正确';
                break;
            case '-21' :
                $this->message = 'MD5接口密钥加密不正确';
                break;
            case '-3' :
                $this->message = '短信数量不足';
                break;
            case '-11' :
                $this->message = '该用户被禁用';
                break;
            case '-14' :
                $this->message = '短信内容出现非法字符';
                break;
            case '-4' :
                $this->message = '手机号格式不正确';
                break;
            case '-41' :
                $this->message = '手机号码为空';
                break;
            case '-42' :
                $this->message = '短信内容为空';
                break;
            case '-51' :
                $this->message = '短信签名格式不正确';
                break;
            case '-6' :
                $this->message = 'IP限制';
                break;
            default :
                $this->message = '短信发送成功';
                $success = true;
                break;
        }
        
        return $success;
    }
    
    /**
     * @inheritdoc
     */
    public function sendByTemplate($mobile, $data, $id)
    {
        throw new NotSupportedException('中国网建不支持发送模板短信！');
    }
}