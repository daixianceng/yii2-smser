<?php

namespace daixianceng\smser;

use yii\base\NotSupportedException;

/**
 * 商信通
 * 
 * @author Cosmo <daixianceng@gmail.com>
 * @property string $password write-only password
 * @property string $state read-only state
 * @property string $message read-only message
 */
class SxtSmser extends Smser
{
    /**
     * @inheritdoc
     */
    public $url = 'http://sxtjk.3a3g.cn:5000/sms/http/Sms3.aspx';
    
    /**
     * @inheritdoc
     */
    public function send($mobile, $content)
    {
        if (parent::send($mobile, $content)) {
            return true;
        }
        
        $data = [
            'action' => 'sendsms',
            'username' => $this->username,
            'userpwd' => $this->password,
            'mobiles' => $mobile,
            'content' => $content
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
                $this->message = '用户名或密码错误';
                break;
            case '-2' :
                $this->message = '余额不足';
                break;
            case '-3' :
                $this->message = '号码太长，不能超过1000条一次提交';
                break;
            case '-4' :
                $this->message = '无合法号码';
                break;
            case '-5' :
                $this->message = '内容包含关键字';
                break;
            case '-6' :
                $this->message = '内容太长，超过9条';
                break;
            case '-7' :
                $this->message = '内容为空';
                break;
            case '-8' :
                $this->message = '定时时间格式不对';
                break;
            case '-9' :
                $this->message = '修改密码失败';
                break;
            case '-10' :
                $this->message = '用户当前不能发送短信';
                break;
            case '-11' :
                $this->message = 'Action参数不正确';
                break;
            case '-100' :
                $this->message = '系统错误';
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
        throw new NotSupportedException('商信通不支持发送模板短信！');
    }
}