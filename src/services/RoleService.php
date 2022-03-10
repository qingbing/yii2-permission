<?php
/**
 * @link        http://www.phpcorner.net
 * @author      qingbing<780042175@qq.com>
 * @copyright   Chengdu Qb Technology Co., Ltd.
 */

namespace YiiPermission\services;


use Exception;
use YiiHelper\abstracts\Service;
use YiiHelper\helpers\Pager;
use YiiPermission\interfaces\IRoleService;
use YiiPermission\models\PermissionRole;
use YiiPermission\models\PermissionRoleMenu;
use Zf\Helper\Exceptions\BusinessException;
use Zf\Helper\Util;

/**
 * 服务 : 角色管理
 *
 * Class RoleService
 * @package YiiPermission\services
 */
class RoleService extends Service implements IRoleService
{
    /**
     * 角色列表
     *
     * @param array|null $params
     * @return array
     */
    public function list(array $params = []): array
    {
        $query = PermissionRole::find()
            ->orderBy('sort_order ASC, id ASC');
        // 等于查询
        $this->attributeWhere($query, $params, [
            'id',
            'code',
            'is_enable',
        ]);
        // like 查询
        $this->likeWhere($query, $params, ['name']);
        return Pager::getInstance()->pagination($query, $params['pageNo'], $params['pageSize']);
    }

    /**
     * 添加角色
     *
     * @param array $params
     * @return bool
     * @throws Exception
     */
    public function add(array $params): bool
    {
        $model = new PermissionRole();
        $model->setFilterAttributes($params);
        if (!isset($params['code']) || empty($params['code'])) {
            $model->code = Util::uniqid();
        }
        return $model->saveOrException();
    }

    /**
     * 编辑角色
     *
     * @param array $params
     * @return bool
     * @throws Exception
     */
    public function edit(array $params): bool
    {
        $model = $this->getModel($params);
        unset($params['id']);
        $model->setFilterAttributes($params);
        return $model->saveOrException();
    }

    /**
     * 删除角色
     *
     * @param array $params
     * @return bool
     * @throws \Throwable
     * @throws Exception
     */
    public function del(array $params): bool
    {
        return $this->getModel($params)->delete();
    }

    /**
     * 查看角色详情
     *
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function view(array $params)
    {
        return $this->getModel($params);
    }

    /**
     * 获取当前操作模型
     *
     * @param array $params
     * @return PermissionRole
     * @throws Exception
     */
    protected function getModel(array $params): PermissionRole
    {
        $model = PermissionRole::findOne([
            'id' => $params['id'] ?? null
        ]);
        if (null === $model) {
            throw new BusinessException("角色不存在");
        }
        return $model;
    }

    /**
     * 获取角色已分配的menu-codes
     *
     * @param array $params
     * @return array
     * @throws Exception
     */
    public function getAssignedMenu(array $params)
    {
        $model = $this->getModelByCode($params);
        return array_column($model->menus, 'code');
    }

    /**
     * 为角色分配菜单
     *
     * @param array $params
     * @return bool
     * @throws Exception
     * @throws \Throwable
     */
    public function assignMenu(array $params = []): bool
    {
        $model            = $this->getModelByCode($params);
        $roleCode         = $params['code'];
        $assignedCodes    = array_column($model->menus, 'code');
        $newAssignedCodes = $params['menu_codes'];
        $delCodes         = array_diff($assignedCodes, $newAssignedCodes);
        $addCodes         = array_diff($newAssignedCodes, $assignedCodes);
        return PermissionRole::getDb()->transaction(function () use ($roleCode, $delCodes, $addCodes) {
            if (!empty($delCodes)) {
                // 删除的api
                $status = PermissionRoleMenu::deleteAll([
                    'role_code' => $roleCode,
                    'menu_code' => $delCodes,
                ]);
                if (!$status) {
                    throw new BusinessException("删除user-menu关联失败");
                }
            }
            if (!empty($addCodes)) {
                // 添加的api
                foreach ($addCodes as $menuCode) {
                    $dbData   = [
                        'role_code' => $roleCode,
                        'menu_code' => $menuCode,
                    ];
                    $viaModel = PermissionRoleMenu::findOne($dbData);
                    $viaModel = $viaModel ?: new PermissionRoleMenu();
                    $viaModel->setAttributes($dbData);
                    $viaModel->saveOrException();
                }
            }
            return true;
        });
    }

    /**
     * 通过code获取当前操作模型
     *
     * @param array $params
     * @return PermissionRole
     * @throws Exception
     */
    protected function getModelByCode(array $params): PermissionRole
    {
        $model = PermissionRole::findOne([
            'code' => $params['code'] ?? null
        ]);
        if (null === $model) {
            throw new BusinessException("角色不存在");
        }
        return $model;
    }
}