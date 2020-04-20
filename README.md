# 基于Zookeeper驱动的Hyperf服务注册组件(改进版)

## 组件库说明

### 1、官方组件库


- [rpc-client](./src/rpc-client/) ： 官方RPC客户端组件库（来源于Hyperf官方组件）
    - 新增：支持Zookeeper(2020.04.08)

- [service-governance](./src/service-governance/) ： 官方服务治理（来源于Hyperf官方组件） 
    - 新增： 支持zookeeper(2020.04.08)
    - 修复： 同一个服务不同协议获取混乱的问题(2020.04.08)

### 2. 新增组件库

- [zookeeper](./src/zookeeper/) ： 服务治理Zookeeper驱动库
    - 新增组件库(2020.04.08)
    
## 使用说明

### 1. 克隆项目到Hyperf项目根目录下，以Hyperf项目`hyperf-skeleton`为例

```
# cd hyperf-skeleton/
# git clone git@github.com:myziyue/zookeeper-service-governance-hyperf.git
```

### 2. 修改Hyperf项目的`composer.json`
增加如下内容
```json
    "repositories": {
        "hyperf/rpc-client": {
            "type": "path",
            "url": "zookeeper-service-governance-hyperf/src/*"
        },
        "hyperf/service-governance": {
            "type": "path",
            "url": "zookeeper-service-governance-hyperf/src/*"
        },
        "hyperf/zookeeper": {
            "type": "path",
            "url": "zookeeper-service-governance-hyperf/src/*"
        }
    }
```

### 3. 删除`composer.lock`文件和`vendor`目录

### 4. 更新项目

```
# composer update
```


