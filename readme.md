人文场地数据接口文档
===

## 本项目地址
- 界面：http://www.scauwlb.top/renwen/
- 接口：http://139.199.79.172/renwen/public/login

## 接口说明

> 统一前缀：http://139.199.79.172/renwen/public/

接口链接 | 访问方式 | 传递参数 | 成功 | 失败 | 备注 | 是否需要登录状态
| :---: | :---: | :---: | :---: | :---: | :---: | :---: |
 login | get | - | 跳转使用界面 | 提示“授权失败，请重新授权” | -
logout | get | - | 1 | -1 | - | 否
 create | post | classroom，personName，personId，phone，org，reason，year，month，date，start_hour，start_minute，end_hour，end_minute，pass | 1 | 0 | - | 是
 show | get | - | json array | - | - | 是
 check | get | id | json array | - | - | 是
 judge | post | id，pass | 1 | - | - | 是
