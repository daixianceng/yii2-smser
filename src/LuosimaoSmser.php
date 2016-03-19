<?php

namespace daixianceng\smser;

use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;

/**
 * 螺丝帽
 * 
 * @author Cosmo <daixianceng@gmail.com>
 * @property string $password write-only password
 * @property string $state read-only state
 * @property string $message read-only message
 */
class LuosimaoSmser extends Smser
{
    /**
     * @inheritdoc
     */
    public $username = 'api';
    
    /**
     * @var string
     */
    public $dataType = 'json';
    
    /**
     * @var string
     */
    public $signature = '';
    
    /**
     * @var string
     */
    protected $urlJson = 'http://sms-api.luosimao.com/v1/send.json';
    
    /**
     * @var string
     */
    protected $urlXml = 'http://sms-api.luosimao.com/v1/send.xml';
    
    /**
     * @inheritdoc
     */
    public function send($mobile, $content)
    {
        if (parent::send($mobile, $content)) {
            return true;
        }
        
        $data = [
            'mobile' => $mobile,
            'message' => $content . $this->signature
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getUrl());
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->username . ':' . $this->password);
        
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        
        $result = curl_exec($ch);
        curl_close($ch);
        
        if (empty($result)) {
            $this->message = '网络错误';
        } else {
            if ($this->dataType === 'json') {
                $json = json_decode($result);
                if ($json && is_object($json)) {
                    $this->state = isset($json->error) ? (string) $json->error : null;
                    $this->message = isset($json->msg) ? (string) $json->msg : null;
                }
            } else {
                $xml = simplexml_load_string(trim($result, " \t\n\r"));
                if ($xml && is_object($xml)) {
                    $this->state = isset($xml->error) ? (string) $xml->error : null;
                    $this->message = isset($xml->msg) ? (string) $xml->msg : null;
                }
            }
        }
        
        return $this->state === '0';
    }
    
    /**
     * @inheritdoc
     */
    public function setPassword($password)
    {
        $this->password = 'key-' . $password;
    }
    
    /**
     * @inheritdoc
     */
    public function sendByTemplate($mobile, $data, $id)
    {
        throw new NotSupportedException('螺丝帽不支持发送模板短信！');
    }
    
    /**
     * 获取请求地址
     * 
     * @return string
     * @throws InvalidConfigException
     */
    public function getUrl()
    {
        if ($this->url === null) {
            switch ($this->dataType) {
                case 'json':
                    $this->url = $this->urlJson;
                    break;
                case 'xml':
                    $this->url = $this->urlXml;
                    break;
                default:
                    throw new InvalidConfigException('“dataType”配置错误！');
            }
        }
        
        return $this->url;
    }
}