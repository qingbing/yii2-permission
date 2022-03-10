<?php
/**
 * @link        http://www.phpcorner.net
 * @author      qingbing<780042175@qq.com>
 * @copyright   Chengdu Qb Technology Co., Ltd.
 */

namespace YiiPermission\services;


use Exception;
use Throwable;
use yii\db\Query;
use yii\helpers\Console;
use YiiHelper\abstracts\Service;
use YiiHelper\helpers\Pager;
use YiiPermission\interfaces\IMenuPathService;
use YiiPermission\models\PermissionMenu;
use YiiPermission\models\PermissionMenuApi;
use Zf\Helper\Business\ObjectTree;
use Zf\Helper\Exceptions\BusinessException;
use Zf\Helper\Traits\Models\TLabelYesNo;
use Zf\Helper\Util;

/**
 * 服务 : 菜单管理
 *
 * Class MenuPathService
 * @package YiiPermission\services
 */
class MenuPathService extends Service implements IMenuPathService
{
    /**
     * 菜单类型映射关系
     *
     * @return array
     */
    public function typeMap(): array
    {
        return PermissionMenu::typeMap();
    }

    /**
     * 菜单树映射关系
     *
     * @return array
     */
    public function treeMap(): array
    {
        return PermissionMenu::treeMap();
    }

    /**
     * 树形结构
     *
     * @param array $params
     * @return array
     */
    public function tree(array $params = []): array
    {
        $query = PermissionMenu::find()
            ->andWhere(['=', 'type', $params['type']])
            ->orWhere('parent_code!=:parent_code AND type=:type', [
                ':parent_code' => '',
                ':type'        => PermissionMenu::TYPE_BUTTON,
            ]);
        if ($params['onlyEnable']) {
            $query->andWhere(['=', 'is_enable', IS_ENABLE_YES]);
        }
        if (!$params['containButton']) {
            $query->andWhere(['!=', 'type', PermissionMenu::TYPE_BUTTON]);
        }
        $data = $query->asArray()
            ->all();

        return ObjectTree::getInstance()
            ->setSourceData($data)
            ->setTopTag('')
            ->setPid('parent_code')
            ->setId('code')
            ->setReturnArray(true)
            ->setSubDataName('children')
            ->getTreeData();
    }

    /**
     * 菜单列表
     *
     * @param array|null $params
     * @return array
     */
    public function list(array $params = []): array
    {
        $query = PermissionMenu::find()
            ->alias('pm')
            ->select('pm.*, parent.name AS parent_name')
            ->leftJoin(PermissionMenu::tableName() . ' AS parent', 'pm.parent_code=parent.code')
            ->orderBy('pm.type ASC, pm.sort_order DESC, pm.path ASC');
        // 等于查询
        $this->attributeWhere($query, $params, [
            'pm.type'        => 'type',
            'pm.parent_code' => 'parent_code',
            'pm.is_public'   => 'is_public',
            'pm.is_enable'   => 'is_enable',
        ]);
        // like 查询
        $this->likeWhere($query, $params, [
            'pm.path' => 'path',
            'pm.name' => 'name',
        ]);
        return Pager::getInstance()
            ->setAsArray(true)
            ->pagination($query, $params['pageNo'], $params['pageSize']);
    }

    /**
     * 添加菜单
     *
     * @param array $params
     * @return array
     * @throws Exception
     */
    public function add(array $params): array
    {
        $model = new PermissionMenu();
        $model->setFilterAttributes($params);
        if (!isset($params['code']) || empty($params['code'])) {
            $model->code = $params['type'] . '_' . Util::uniqid();
        }
        $model->saveOrException();
        return $model->attributes;
    }

    /**
     * 编辑菜单
     *
     * @param array $params
     * @return array
     * @throws Exception
     */
    public function edit(array $params): array
    {
        $model = $this->getModel($params);
        unset($params['id']);
        $model->setFilterAttributes($params);
        $model->saveOrException();
        return $model->attributes;
    }

    /**
     * 删除菜单
     *
     * @param array $params
     * @return bool
     * @throws Throwable
     */
    public function del(array $params): bool
    {
        $model = $this->getModel($params);
        return $model->delete();
    }

    /**
     * 查看菜单详情
     *
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function view(array $params)
    {
        $model                     = $this->getModel($params);
        $attributes                = $model->getAttributes();
        $attributes['parent_code'] = $model->parent ? $model->parent->parent_code : '';
        $attributes['parent_name'] = $model->parent ? $model->parent->name : '';
        return $attributes;
    }

    /**
     * 获取当前操作模型
     *
     * @param array $params
     * @return PermissionMenu
     * @throws Exception
     */
    protected function getModel(array $params): PermissionMenu
    {
        $model = PermissionMenu::findOne([
            'id' => $params['id'] ?? null
        ]);
        if (null === $model) {
            throw new BusinessException("菜单不存在");
        }
        return $model;
    }

    /**
     * 获取菜单已分配的api-codes
     *
     * @param array $params
     * @return array
     * @throws Exception
     */
    public function getAssignedApiPath(array $params)
    {
        $model = $this->getModelByCode($params);
        return array_column($model->apis, 'code');
    }

    /**
     * 为菜单分配api后端接口
     *
     * @param array $params
     * @return bool
     * @throws Throwable
     * @throws Exception
     */
    public function assignApiPath(array $params): bool
    {
        $model            = $this->getModelByCode($params);
        $menuCode         = $params['code'];
        $assignedCodes    = array_column($model->apis, 'code');
        $newAssignedCodes = $params['api_codes'];
        $delCodes         = array_diff($assignedCodes, $newAssignedCodes);
        $addCodes         = array_diff($newAssignedCodes, $assignedCodes);
        return PermissionMenu::getDb()->transaction(function () use ($menuCode, $delCodes, $addCodes) {
            if (!empty($delCodes)) {
                // 删除的api
                $status = PermissionMenuApi::deleteAll([
                    'menu_code' => $menuCode,
                    'api_code'  => $delCodes,
                ]);
                if (!$status) {
                    throw new BusinessException("删除api-menu关联失败");
                }
            }
            if (!empty($addCodes)) {
                // 添加的api
                foreach ($addCodes as $apiCode) {
                    $dbData   = [
                        'menu_code' => $menuCode,
                        'api_code'  => $apiCode,
                    ];
                    $viaModel = PermissionMenuApi::findOne($dbData);
                    $viaModel = $viaModel ?: new PermissionMenuApi();
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
     * @return PermissionMenu
     * @throws Exception
     */
    protected function getModelByCode(array $params): PermissionMenu
    {
        $model = PermissionMenu::findOne([
            'code' => $params['code'] ?? null
        ]);
        if (null === $model) {
            throw new BusinessException("菜单不存在");
        }
        return $model;
    }
}