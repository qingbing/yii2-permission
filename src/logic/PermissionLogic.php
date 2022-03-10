<?php
/**
 * @link        http://www.phpcorner.net
 * @author      qingbing<780042175@qq.com>
 * @copyright   Chengdu Qb Technology Co., Ltd.
 */

namespace YiiPermission\logic;

use Yii;
use YiiHelper\helpers\AppHelper;
use YiiHelper\traits\TLoginRequired;
use YiiPermission\models\PermissionApi;
use YiiPermission\models\PermissionMenu;
use YiiPermission\models\PermissionUserRole;
use Zf\Helper\Exceptions\CustomException;

/**
 * 工具 : 分配用户权限
 *
 * Class PermissionLogic
 * @package YiiPermission\logic
 */
class PermissionLogic
{
    use TLoginRequired;

    /**
     * @param int $uid
     * @param array $roleCodes
     * @param int $isEnable
     * @return bool
     * @throws CustomException
     * @throws \Throwable
     */
    public static function assignUserRole($uid, array $roleCodes, $isEnable = 1)
    {
        // 登录检查
        self::loginRequired();
        if (self::isLoginUser($uid)) {
            // 不能是当前登录用户
            throw new CustomException("不能修改自己的角色");
        }

        Yii::$app->db->transaction(function () use ($roleCodes, $uid, $isEnable) {
            // 登录这拥有的权限
            $myRoles = AppHelper::app()->getUser()->getPermissions()['roles'];
            foreach ($roleCodes as $roleCode) {
                if (!isset($myRoles[$roleCode])) {
                    throw new CustomException("分配角色越权, 请联系管理员");
                }
                $dbData = [
                    'uid'       => $uid,
                    'role_code' => $roleCode,
                ];
                if (!$isEnable) {
                    // 取消角色，直接删除
                    PermissionUserRole::deleteAll($dbData);
                    continue;
                }
                $viaModel = PermissionUserRole::findOne($dbData);
                if ($viaModel) {
                    // 已经存在，不处理
                    continue;
                }
                $viaModel = new PermissionUserRole();
                $viaModel->setAttributes($dbData);
                $viaModel->saveOrException();
            }
            return true;
        });
        return true;
    }

    /**
     * 获取所有公共权限，包括菜单、路径
     *
     * @return array
     */
    public static function getPublicPermission()
    {
        // 公共的api后端路径
        $paths = PermissionApi::getPublicApi(true, 1);
        // 公共的菜单
        $menus = PermissionMenu::getPublicPath(true, 1);
        return [
            'roles' => [],
            'menus' => $menus,
            'paths' => $paths,
        ];
    }
}