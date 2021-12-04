# yii2-task
yii2实现组件:角色管理([用户]->角色->菜单->api)

# 使用
## 一、配置
### 1.1 登录的默认账号类型 : params -> defaultAccountType，默认 "email"
```php
'params' => [
    'defaultAccountType' => 'email',
]
```

### 1.2 支持添加的菜单类型 : params -> permissionMenuTypes，默认
```php
'permissionMenuTypes' => [
    'menu'   => '菜单',
    'help'   => '帮助中心',
    'top'    => '顶端菜单',
    'footer' => '底部菜单',
    'button' => '按钮',
    'custom' => '自定义',
]
```

### 1.3 配置控制器 web.php
```php
'controllerMap' => [
    // 权限管理
    'api-path'  => \YiiPermission\controllers\ApiPathController::class,
    'menu-path' => \YiiPermission\controllers\MenuPathController::class,
    'role'      => \YiiPermission\controllers\RoleController::class,
]
```