# aisino-invoice

## desc

上海航天电子发票对接平台，PHP版本的SDK

## 安装

```
composer require yeyour/aisino-invoice
```

## 使用方法



生成发票
```
InvoiceSDK::create($params,$config);
```

下载发票
```
InvoiceSDK::download($params,$config);
```

邮件发送发票
```
InvoiceSDK::email($params,$config);
```


参数说明
```
$params = []; //参考航信接口文档传入对应的业务参数

$config = [
    'DSPTBM' => '电商平台编码',
    'NSRSBH' => '纳税人识别码',
    'NSRMC'  => '纳税人名称',
    'XHFMC'  => '销货方名称',
    'XHF_DZ' => '销货方地址',
    'XHF_DH' => '销货方电话',
    'XHF_YHZH' => '销货方银行账号',
    'KPY' => '开票员',
    'SKY' => '可选',
    'HSBZ' => '1',
    'TERMINALCODE' => '0',
    'APPID' => 'ZZS_PT_DZFP',
    'TAXPAYWERID' => '税号',
    'AUTHORIZATIONCODE' => '认证码',
    'ENCRYPTCODE' =>'加密码',
    'INTERFACE_FPKJ' => 'ECXML.FPKJ.BC.E_INV',
    'INTERFACE_FPXZ' => 'ECXML.FPXZ.CX.E_INV',
    'INTERFACE_FPYX' => 'ECXML.EMAILPHONEFPTS.TS.E.INV',
    'REQUESTCODE' => '请求码',
    'RESPONSECODE' => '响应码',
    'PASSWORD' => '密码',
    'DATAEXCHANGEID' => '交互码',
    'KJFP' => 'ECXML.FPKJ.BC.E_INV',
    'DOWNLOAD' => 'ECXML.FPXZ.CX.E_INV',
    'EMAIL' => 'ECXML.EMAILPHONEFPTS.TS.E.INV',
    'REGISTERCODE' => '注册码',
];
```

## license

MIT



