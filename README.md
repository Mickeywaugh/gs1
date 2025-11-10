# Gs1Epc

#### 介绍
PHP版本自用的GS1规范下的EPC编码解码库;
#### 使用方法:

 引用包
 `composer require Mickeywaugh/Gs1;`

 样例

```
<?php 

  use Mickeywaugh\Gs1\Gs1;
    //创建实例
  $gs1Epc= Gs1::Stgin($companyPrefixLength,$tagSize,$filterValue,$schemeParameters);
  //编码   
  $gs1Epc->encode();
  //获取编码后各种数据
  $gs1Epc->getEpcBinary();
  $gs1Epc->getURI();
  $gs1Epc->getTagUri();
  $gs1Epc->getEpcRawURI();
  //解码
  $gs1Epc = Gs1::Stgin()::decode();
```