<?php
/**
 * @link        http://www.phpcorner.net
 * @author      qingbing<780042175@qq.com>
 * @copyright   Chengdu Qb Technology Co., Ltd.
 */

namespace YiiPermission\interfaces;


use YiiHelper\services\interfaces\ICurdService;

/**
 * 接口 : 角色管理
 *
 * Interface IRoleService
 * @package YiiPermission\interfaces
 */
interface IRoleService extends ICurdService
{
    /**
     * 获取角色已分配的menu-codes
     *
     * @param array $params
     * @return array
     */
    public function getAssignedMenu(array $params);

    /**
     * 为角色分配菜单
     *
     * @param array $params
     * @return bool
     */
    public function assignMenu(array $params = []): bool;
}