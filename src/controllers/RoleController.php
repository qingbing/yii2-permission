<?php
/**
 * @link        http://www.phpcorner.net
 * @author      qingbing<780042175@qq.com>
 * @copyright   Chengdu Qb Technology Co., Ltd.
 */

namespace YiiPermission\controllers;


use Exception;
use YiiHelper\abstracts\RestController;
use YiiPermission\interfaces\IRoleService;
use YiiPermission\models\PermissionMenu;
use YiiPermission\models\PermissionRole;
use YiiPermission\services\RoleService;
use Zf\Helper\Traits\Models\TLabelEnable;
use Zf\Helper\Traits\Models\TLabelYesNo;

/**
 * 控制器 : 角色管理
 *
 * Class RoleController
 * @package YiiPermission\controllers
 *
 * @property-read IRoleService $service
 */
class RoleController extends RestController
{
    public $serviceInterface = IRoleService::class;
    public $serviceClass     = RoleService::class;

    /**
     * 角色列表
     *
     * @return array
     * @throws Exception
     */
    public function actionList()
    {
        // 参数验证和获取
        $params = $this->validateParams([
            ['id', 'integer', 'label' => '角色ID'],
            ['code', 'string', 'label' => '角色标识'],
            ['name', 'string', 'label' => '角色描述'],
            ['is_enable', 'in', 'label' => '启用状态', 'range' => array_keys(TLabelEnable::enableLabels())],
        ], null, true);

        // 业务处理
        $res = $this->service->list($params);
        // 渲染结果
        return $this->success($res, '角色列表');
    }

    /**
     * 添加角色
     *
     * @return array
     * @throws Exception
     */
    public function actionAdd()
    {
        // 参数验证和获取
        $params = $this->validateParams([
            [['name'], 'required'],
            ['name', 'unique', 'label' => '角色名称', 'targetClass' => PermissionRole::class, 'targetAttribute' => 'remark'],
            ['code', 'unique', 'label' => '角色代码', 'targetClass' => PermissionRole::class, 'targetAttribute' => 'code'],
            ['remark', 'string', 'label' => '描述'],
            ['sort_order', 'integer', 'label' => '排序', 'default' => 0],
            ['is_enable', 'in', 'label' => '启用状态', 'range' => array_keys(TLabelEnable::enableLabels())],
        ], null);

        // 业务处理
        $res = $this->service->add($params);
        // 渲染结果
        return $this->success($res, '添加角色');
    }

    /**
     * 编辑角色
     *
     * @return array
     * @throws Exception
     */
    public function actionEdit()
    {
        // 参数验证和获取
        $id     = $this->getParam('id');
        $params = $this->validateParams([
            [['id', 'name'], 'required'],
            ['id', 'exist', 'label' => '角色ID', 'targetClass' => PermissionRole::class, 'targetAttribute' => 'id'],
            ['name', 'unique', 'label' => '角色名称', 'targetClass' => PermissionRole::class, 'targetAttribute' => 'name', 'filter' => ['!=', 'id', $id]],
            ['remark', 'string', 'label' => '描述'],
            ['sort_order', 'integer', 'label' => '排序', 'default' => 0],
            ['is_enable', 'in', 'label' => '启用状态', 'range' => array_keys(TLabelEnable::enableLabels())],
        ], null);

        // 业务处理
        $res = $this->service->edit($params);
        // 渲染结果
        return $this->success($res, '编辑角色');
    }

    /**
     * 删除角色
     *
     * @return array
     * @throws Exception
     */
    public function actionDel()
    {
        // 参数验证和获取
        $params = $this->validateParams([
            ['id', 'required'],
            ['id', 'exist', 'label' => '角色ID', 'targetClass' => PermissionRole::class, 'targetAttribute' => 'id'],
        ], null);

        // 业务处理
        $res = $this->service->del($params);
        // 渲染结果
        return $this->success($res, '删除角色');
    }

    /**
     * 查看角色详情
     *
     * @return array
     * @throws Exception
     */
    public function actionView()
    {
        // 参数验证和获取
        $params = $this->validateParams([
            ['id', 'required'],
            ['id', 'exist', 'label' => '角色ID', 'targetClass' => PermissionRole::class, 'targetAttribute' => 'id'],
        ], null);

        // 业务处理
        $res = $this->service->view($params);
        // 渲染结果
        return $this->success($res, '查看角色详情');
    }

    /**
     * 为角色分配菜单
     *
     * @return array
     * @throws Exception
     */
    public function actionAssignMenu()
    {
        // 参数验证和获取
        $params = $this->validateParams([
            [['id', 'is_enable', 'menu_codes'], 'required'],
            ['is_enable', 'in', 'label' => '是否有效', 'range' => array_keys(TLabelYesNo::isLabels())],
            ['id', 'exist', 'label' => '角色ID', 'targetClass' => PermissionRole::class, 'targetAttribute' => 'id'],
            [
                'menu_codes',
                'each',
                'rule' => [
                    'exist',
                    'message'         => '菜单标记不存在',
                    'targetClass'     => PermissionMenu::class,
                    'targetAttribute' => 'code',
                    'filter'          => ['=', 'is_enable', 1]
                ]
            ]
        ], null, false, ['menu_codes'], ',');

        // 业务处理
        $res = $this->service->assignMenu($params);
        // 渲染结果
        return $this->success($res, '为角色分配菜单');
    }
}