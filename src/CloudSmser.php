<?php

namespace daixianceng\smser;

use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;

/**
 * 中国云信
 * 
 * @author Cosmo <daixianceng@gmail.com>
 * @property string $password write-only password
 * @property string $state read-only state
 * @property string $message read-only message
 */
class CloudSmser extends Smser
{
    /**
     * @inheritdoc
     */
    public $url = 'http://api.sms.cn/mtutf8/';
    
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
            'pwd' => $this->password,
            'mobile' => $mobile,
            'content' => $content
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        
        $result = curl_exec($ch);
        curl_close($ch);
        
        $resultArr = [];
        parse_str($result, $resultArr);
        
        $this->state = isset($resultArr['stat']) ? (string) $resultArr['stat'] : null;
        $this->message = isset($resultArr['message']) ? (string) $resultArr['message'] : null;
        
        return $this->state === '100';
    }
    
    /**
     * 设置密码
     * 
     * @param string $password
     * @throws InvalidConfigException
     */
    public function setPassword($password)
    {
        if ($this->username === null) {
            throw new InvalidConfigException('用户名不能为空');
        }
        
        $this->password = md5($password . $this->username);
    }
    
    /**
     * @inheritdoc
     */
    public function sendByTemplate($mobile, $data, $id)
    {
        throw new NotSupportedException('中国云信不支持发送模板短信！');
    }
}