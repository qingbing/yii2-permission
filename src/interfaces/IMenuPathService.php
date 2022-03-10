<?php
/**
 * @link        http://www.phpcorner.net
 * @author      qingbing<780042175@qq.com>
 * @copyright   Chengdu Qb Technology Co., Ltd.
 */

namespace YiiPermission\interfaces;

/**
 * 接口 : 菜单管理
 *
 * Interface IMenuService
 * @package YiiPermission\interfaces
 */
interface IMenuPathService
{
    /**
     * 菜单类型映射关系
     *
     * @return array
     */
    public function typeMap(): array;

    /**
     * 菜单树映射关系
     *
     * @return array
     */
    public function treeMap(): array;

    /**
     * 树形结构
     *
     * @param array $params
     * @return array
     */
    public function tree(array $params = []): array;

    /**
     * 菜单列表
     *
     * @param array|null $params
     * @return array
     */
    public function list(array $params = []): array;

    /**
     * 添加菜单
     *
     * @param array $params
     * @return array
     */
    public function add(array $params): array;

    /**
     * 编辑菜单
     *
     * @param array $params
     * @return array
     */
    public function edit(array $params): array;

    /**
     * 删除菜单
     *
     * @param array $params
     * @return bool
     */
    public function del(array $params): bool;

    /**
     * 查看菜单详情
     *
     * @param array $params
     * @return mixed
     */
    public function view(array $params);

    /**
     * 获取菜单已分配的api-codes
     *
     * @param array $params
     * @return array
     */
    public function getAssignedApiPath(array $params);

    /**
     * 为菜单分配api后端接口
     *
     * @param array|null $params
     * @return bool
     */
    public function assignApiPath(array $params): bool;
}