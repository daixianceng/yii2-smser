<?php

namespace daixianceng\smser;

use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;

/**
 * 云通讯
 * 
 * @author Cosmo <daixianceng@gmail.com>
 * @property string $state read-only state
 * @property string $message read-only message
 */
class YuntongxunSmser extends Smser
{
    /**
     * @var string
     */
    public $accountSid;
    
    /**
     * @var string
     */
    public $accountToken;
    
    /**
     * @var string
     */
    public $appId;
    
    /**
     * @var string
     */
    public $serverIp;
    
    /**
     * @var string
     */
    public $serverPort;
    
    /**
     * @var string
     */
    public $softVersion;
    
    /**
     * @var string
     */
    public $dataType = 'json';
    
    /**
     * @var string|null
     */
    private $_batch;
    
    /**
     * @inheritdoc
     */
    public function send($mobile, $content)
    {
        throw new NotSupportedException('云通讯不支持直接发送文本！');
    }
    
    /**
     * @inheritdoc
     */
    public function sendByTemplate($mobile, $data, $id)
    {
        if (parent::sendByTemplate($mobile, $data, $id)) {
            return true;
        }
        
        if ($this->dataType === 'json') {
            $body = json_encode([
                'to' => $mobile,
                'templateId' => $id,
                'appId' => $this->appId,
                'datas' => array_values($data)
            ]);
        } elseif ($this->dataType === 'xml') {
            
            $dataStr = '';
            foreach ($data as $val) {
                $dataStr .= "<data>{$val}</data>";
            }
            
            $body = <<<XML
<TemplateSMS>
    <to>{$mobile}</to> 
    <appId>{$this->appId}</appId>
    <templateId>{$id}</templateId>
    <datas>{$dataStr}</datas>
</TemplateSMS>
XML;
        } else {
            throw new InvalidConfigException('“dataType” 配置不正确。');
        }
        
        $sig = strtoupper(md5($this->accountSid . $this->accountToken . $this->getBatch()));
        $this->url = "https://{$this->serverIp}:{$this->serverPort}/{$this->softVersion}/Accounts/{$this->accountSid}/SMS/TemplateSMS?sig={$sig}";
        $authen = base64_encode($this->accountSid . ':' . $this->getBatch());
        $header = ["Accept:application/{$this->dataType}", "Content-Type:application/{$this->dataType};charset=utf-8", "Authorization:{$authen}"];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        
        $result = curl_exec($ch);
        curl_close($ch);
        
        if (empty($result)) {
            $this->state = '172001';
            $this->message = '网络错误';
        } else {
            if ($this->dataType === 'json') {
                $json = json_decode($result);
                if ($json && is_object($json)) {
                    $this->state = isset($json->statusCode) ? (string) $json->statusCode : null;
                    $this->message = isset($json->statusMsg) ? (string) $json->statusMsg : null;
                }
            } else {
                $xml = simplexml_load_string(trim($result, " \t\n\r"));
                if ($xml && is_object($xml)) {
                    $this->state = isset($xml->statusCode) ? (string) $xml->statusCode : null;
                    $this->message = isset($xml->statusMsg) ? (string) $xml->statusMsg : null;
                }
            }
        }
        
        return $this->state === '000000';
    }
    
    /**
     * 时间戳
     * 
     * @return string
     */
    public function getBatch()
    {
        if ($this->_batch === null) {
            $this->_batch = date('YmdHis');
        }
        
        return $this->_batch;
    }
}
