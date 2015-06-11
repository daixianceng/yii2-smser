# yii2-smser
[![Latest Stable Version](https://poser.pugx.org/daixianceng/yii2-smser/v/stable)](https://packagist.org/packages/daixianceng/yii2-smser) [![Total Downloads](https://poser.pugx.org/daixianceng/yii2-smser/downloads)](https://packagist.org/packages/daixianceng/yii2-smser) [![Latest Unstable Version](https://poser.pugx.org/daixianceng/yii2-smser/v/unstable)](https://packagist.org/packages/daixianceng/yii2-smser) [![License](https://poser.pugx.org/daixianceng/yii2-smser/license)](https://packagist.org/packages/daixianceng/yii2-smser)

Yii2 SMS extension （短信扩展）

包含[中国云信](http://www.sms.cn/)与[中国网建](http://www.smschinese.cn/)接口

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/). Check the [composer.json](https://github.com/daixianceng/yii2-smser/blob/master/composer.json) for this extension's requirements and dependencies.

To install, either run

```
$ php composer.phar require daixianceng/yii2-smser "*"
```

or add

```
"daixianceng/yii2-smser": "*"
```

to the ```require``` section of your `composer.json` file.

## Usage

```php
return [
    'components' => [
        'smser' => [
            // 中国云信
            'class' => 'daixianceng\smser\CloudSmser',
            'username' => 'username',
            'password' => 'password',
            'useFileTransport' => false
        ]
    ],
];
```

```php
Yii::$app->smser->send('15000000000', '短信内容');
```

## License

**yii2-smser** is released under the BSD 3-Clause License. See the bundled `LICENSE` for details.
