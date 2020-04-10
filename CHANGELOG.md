# v1.0.0 - 2020-04-08

## Added

- Added Zookeeper组件库
- Added 服务治理组件支持Zookeeper
- Added rpc客户端组件支持Zookeeper

## Fixed

- Fixed  `Jsonrpc`同一个服务同时注册`jsonrpc`和`jsonrpc-http`时，在调用该服务时没有区分具体协议类型
