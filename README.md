# Gs1Epc

#### 介绍
自用的GS1规范下的EPC编码解码库;

使用方法:
1. composer require mickeywaugh/gs1;

2.Eg:
<?php 

  use Mickeywaugh\Gs1;
    //创建实例
  $gs1Epc= Gs1::Stgin($companyPrefixLength,$tagSize,$filterValue,$schemeParameters);
  //编码   
  $gs1Epc->encode();
  //获取编码后各种数据
  $gs1Epc->getEpcBinary();
  $gs1Epc->getURI();
  $gs1Epc->getTagUri();
  $gs1Epc->getEpcRawURI();