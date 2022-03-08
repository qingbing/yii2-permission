<?php
/**
 * @link        http://www.phpcorner.net
 * @author      qingbing<780042175@qq.com>
 * @copyright   Chengdu Qb Technology Co., Ltd.
 */

namespace YiiPermission\controllers;


use Exception;
use YiiHelper\abstracts\RestController;
use YiiHelper\validators\JsonValidator;
use YiiPermission\interfaces\IMenuPathService;
use YiiPermission\models\PermissionApi;
use YiiPermission\models\PermissionMenu;
use YiiPermission\services\MenuPathService;
use Zf\Helper\Traits\Models\TLabelEnable;
use Zf\Helper\Traits\Models\TLabelYesNo;

/**
 * 控制器 : 菜单管理
 *
 * Class MenuController
 * @package YiiPermission\controllers
 *
 * @property-read IMenuPathService $service
 */
class MenuPathController extends RestController
{
    public $serviceInterface = IMenuPathService::class;
    public $serviceClass     = MenuPathService::class;

    /**
     * 菜单类型映射关系
     *
     * @return array
     * @throws Exception
     */
    public function actionTypeMap()
    {
        // 业务处理
        $res = $this->service->typeMap();
        // 渲染结果
        return $this->success($res, '菜单类型映射关系');
    }

    /**
     * 菜单树映射关系
     *
     * @return array
     * @throws Exception
     */
    public function actionTreeMap()
    {
        // 业务处理
        $res = $this->service->treeMap();
        // 渲染结果
        return $this->success($res, '菜单型映射关系');
    }

    /**
     * 树形结构
     *
     * @return array
     * @throws Exception
     */
    public function actionTree()
    {
        // 参数验证和获取
        $params = $this->validateParams([
            [['type', 'containButton', 'onlyDisable'], 'required'],
            ['type', 'in', 'label' => '菜单类型', 'default' => PermissionMenu::TYPE_MENU, 'range' => array_keys(PermissionMenu::treeMap())],
            ['containButton', 'in', 'label' => '包含按钮', 'range' => array_keys(TLabelYesNo::yesNoLabels())],
            ['onlyDisable', 'in', 'label' => '仅启用菜单', 'range' => array_keys(TLabelYesNo::yesNoLabels())],
        ]);
        // 业务处理
        $res = $this->service->tree($params);
        // 渲染结果
        return $this->success($res, '树形结构');
    }

    /**
     * 菜单列表
     *
     * @return array
     * @throws Exception
     */
    public function actionList()
    {
        // 参数验证和获取
        $params = $this->validateParams([
            ['type', 'in', 'label' => '菜单类型', 'range' => array_keys(PermissionMenu::typeMap())],
            ['parent_code', 'exist', 'label' => '上级标识', 'targetClass' => PermissionMenu::class, 'targetAttribute' => 'code'],
            ['path', 'string', 'label' => '菜单路径'],
            ['name', 'string', 'label' => '菜单名'],
            ['is_public', 'boolean', 'label' => '公共路径'],
            ['is_enable', 'in', 'label' => '启用状态', 'range' => array_keys(TLabelEnable::enableLabels())],
        ], null, true);

        // 业务处理
        $res = $this->service->list($params);
        // 渲染结果
        return $this->success($res, '菜单列表');
    }

    /**
     * 添加菜单
     *
     * @return array
     * @throws Exception
     */
    public function actionAdd()
    {
        // 参数验证和获取
        $type       = $this->getParam('type');
        $parentCode = $this->getParam('parent_code', '');
        $rules      = [
            [['type', 'name'], 'required'],
            ['type', 'in', 'label' => '菜单类型', 'range' => array_keys(PermissionMenu::typeMap())],
            ['path', 'unique', 'label' => '菜单路径', 'targetClass' => PermissionMenu::class, 'targetAttribute' => 'path', 'filter' => ['=', 'type', $type]],
            ['code', 'unique', 'label' => '菜单标识', 'targetClass' => PermissionMenu::class, 'targetAttribute' => 'code', 'filter' => ['=', 'type', $type]],
            ['parent_code', 'exist', 'label' => '上级菜单', 'targetClass' => PermissionMenu::class, 'targetAttribute' => 'code', 'filter' => ['=', 'type', $type]],
            ['name', 'unique', 'label' => '菜单名称', 'targetClass' => PermissionMenu::class, 'targetAttribute' => 'name', 'filter' => ['=', 'parent_code', $parentCode]],
            ['remark', 'string', 'label' => '菜单描述'],
            ['icon', 'string', 'label' => '菜单图标'],
            ['sort_order', 'integer', 'label' => '排序', 'default' => 0],
            ['exts', JsonValidator::class, 'label' => '扩展信息'],
            ['is_public', 'boolean', 'label' => '公共路径'],
            ['is_enable', 'in', 'label' => '启用状态', 'range' => array_keys(TLabelEnable::enableLabels())],
        ];
        $params     = $this->validateParams($rules, null);
        // 业务处理
        $res = $this->service->add($params);
        // 渲染结果
        return $this->success($res, '添加菜单');
    }

    /**
     * 编辑菜单
     *
     * @return array
     * @throws Exception
     */
    public function actionEdit()
    {
        // 参数验证和获取
        $id     = $this->getParam('id');
        $model  = PermissionMenu::findOne([
            'id' => $this->getParam('id')
        ]);
        $params = $this->validateParams([
            [['id', 'name'], 'required'],
            ['id', 'exist', 'label' => '菜单ID', 'targetClass' => PermissionMenu::class, 'targetAttribute' => 'id'],
            [
                'path',
                'unique',
                'label'           => '菜单路径',
                'targetClass'     => PermissionMenu::class,
                'targetAttribute' => 'path',
                'filter'          => [
                    'and',
                    ['=', 'parent_code', $model->parent_code],
                    ['!=', 'id', $id],
                ]
            ],
            [
                'name',
                'unique',
                'label'           => '菜单名称',
                'targetClass'     => PermissionMenu::class,
                'targetAttribute' => 'name',
                'filter'          => [
                    'and',
                    ['=', 'parent_code', $model->parent_code],
                    ['!=', 'id', $id],
                ]
            ],
            ['remark', 'string', 'label' => '菜单描述'],
            ['icon', 'string', 'label' => '菜单图标'],
            ['exts', JsonValidator::class, 'label' => '扩展信息'],
            ['sort_order', 'integer', 'label' => '排序', 'default' => 0],
            ['is_public', 'boolean', 'label' => '公共路径'],
            ['is_enable', 'in', 'label' => '启用状态', 'range' => array_keys(TLabelEnable::enableLabels())],
        ], null);

        // 业务处理
        $res = $this->service->edit($params);
        // 渲染结果
        return $this->success($res, '编辑菜单');
    }

    /**
     * 删除菜单
     *
     * @return array
     * @throws Exception
     */
    public function actionDel()
    {
        // 参数验证和获取
        $params = $this->validateParams([
            ['id', 'required'],
            ['id', 'exist', 'label' => '菜单ID', 'targetClass' => PermissionMenu::class, 'targetAttribute' => 'id'],
        ], null);

        // 业务处理
        $res = $this->service->del($params);
        // 渲染结果
        return $this->success($res, '删除菜单');
    }

    /**
     * 查看菜单详情
     *
     * @return array
     * @throws Exception
     */
    public function actionView()
    {
        // 参数验证和获取
        $params = $this->validateParams([
            ['id', 'required'],
            ['id', 'exist', 'label' => '菜单ID', 'targetClass' => PermissionMenu::class, 'targetAttribute' => 'id'],
        ], null);

        // 业务处理
        $res = $this->service->view($params);
        // 渲染结果
        return $this->success($res, '查看菜单详情');
    }

    /**
     * 为菜单分配api后端接口
     *
     * @return array
     * @throws Exception
     */
    public function actionAssignApiPath()
    {
        // 参数验证和获取
        $params = $this->validateParams([
            [['id', 'is_enable', 'api_codes'], 'required'],
            ['is_enable', 'in', 'label' => '是否有效', 'range' => array_keys(TLabelEnable::enableLabels())],
            ['id', 'exist', 'label' => '菜单ID', 'targetClass' => PermissionMenu::class, 'targetAttribute' => 'id'],
            [
                'api_codes',
                'each',
                'rule' => [
                    'exist',
                    'message'         => 'api标记不存在',
                    'targetClass'     => PermissionApi::class,
                    'targetAttribute' => 'code',
                    'filter'          => ['=', 'is_enable', 1]
                ]
            ]
        ], null, false, ['api_codes'], ',');

        // 业务处理
        $res = $this->service->assignApiPath($params);
        // 渲染结果
        return $this->success($res, '为菜单分配api后端接口');
    }
}