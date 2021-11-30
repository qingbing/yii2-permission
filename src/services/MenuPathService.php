<?php
/**
 * @link        http://www.phpcorner.net
 * @author      qingbing<780042175@qq.com>
 * @copyright   Chengdu Qb Technology Co., Ltd.
 */

namespace YiiPermission\services;


use Exception;
use Throwable;
use YiiHelper\abstracts\Service;
use YiiHelper\helpers\Pager;
use YiiPermission\interfaces\IMenuPathService;
use YiiPermission\models\PermissionMenu;
use YiiPermission\models\PermissionMenuApi;
use Zf\Helper\Exceptions\BusinessException;
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
     * 菜单列表
     *
     * @param array|null $params
     * @return array
     */
    public function list(array $params = []): array
    {
        $query = PermissionMenu::find()
            ->orderBy('type ASC, path ASC');
        // 等于查询
        $this->attributeWhere($query, $params, [
            'type',
            'parent_code',
            'is_public',
            'is_enable',
        ]);
        // like 查询
        $this->likeWhere($query, $params, ['path', 'remark']);
        return Pager::getInstance()->pagination($query, $params['pageNo'], $params['pageSize']);
    }

    /**
     * 添加菜单
     *
     * @param array $params
     * @return bool
     * @throws Exception
     */
    public function add(array $params): bool
    {
        $model = new PermissionMenu();
        $model->setFilterAttributes($params);
        if (!isset($params['code']) || empty($params['code'])) {
            $model->code = $params['type'] . '_' . Util::uniqid();
        }
        return $model->saveOrException();
    }

    /**
     * 编辑菜单
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
        return $this->getModel($params);
    }

    /**
     * 为菜单分配api后端接口
     *
     * @param array $params
     * @return bool
     * @throws Exception
     */
    public function assignApiPath(array $params = []): bool
    {
        $model = $this->getModel($params);
        if ($params['is_enable']) {
            foreach ($params['api_codes'] as $api_code) {
                $dbData   = [
                    'menu_code' => $model->code,
                    'api_code'  => $api_code,
                ];
                $viaModel = PermissionMenuApi::findOne($dbData);
                $viaModel = $viaModel ?: new PermissionMenuApi();
                $viaModel->setAttributes($dbData);
                $viaModel->saveOrException();
            }
            return true;
        } else {
            return PermissionMenuApi::deleteAll([
                'menu_code' => $model->code,
                'api_code'  => $params['api_codes'],
            ]);
        }
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
}