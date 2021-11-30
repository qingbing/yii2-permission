<?php
/**
 * @link        http://www.phpcorner.net
 * @author      qingbing<780042175@qq.com>
 * @copyright   Chengdu Qb Technology Co., Ltd.
 */

namespace YiiPermission\interfaces;


use YiiHelper\services\interfaces\ICurdService;

/**
 * 接口 : 菜单管理
 *
 * Interface IMenuService
 * @package YiiPermission\interfaces
 */
interface IMenuPathService extends ICurdService
{
    /**
     * 为菜单分配api后端接口
     *
     * @param array|null $params
     * @return bool
     */
    public function assignApiPath(array $params = []): bool;
}