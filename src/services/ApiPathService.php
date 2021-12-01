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
use YiiPermission\interfaces\IApiPathService;
use YiiPermission\models\PermissionApi;
use Zf\Helper\Exceptions\BusinessException;
use Zf\Helper\Util;

/**
 * 服务 : api后端路径管理
 *
 * Class ApiPathService
 * @package YiiPermission\services
 */
class ApiPathService extends Service implements IApiPathService
{
    /**
     * api后端路径列表
     *
     * @param array|null $params
     * @return array
     */
    public function list(array $params = []): array
    {
        $query = PermissionApi::find()
            ->orderBy('sort_order DESC, path ASC');
        // 等于查询
        $this->attributeWhere($query, $params, [
            'is_public',
            'is_enable',
        ]);
        // like 查询
        $this->likeWhere($query, $params, ['path', 'remark']);
        return Pager::getInstance()->pagination($query, $params['pageNo'], $params['pageSize']);
    }

    /**
     * 添加api后端路径
     *
     * @param array $params
     * @return bool
     * @throws Exception
     */
    public function add(array $params): bool
    {
        $model = new PermissionApi();
        $model->setFilterAttributes($params);
        $model->code = Util::uniqid();
        return $model->saveOrException();
    }

    /**
     * 编辑api后端路径
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
     * 删除api后端路径
     *
     * @param array $params
     * @return bool
     * @throws \Throwable
     * @throws Exception
     */
    public function del(array $params): bool
    {
        $model = $this->getModel($params);
        return $model->delete();
    }

    /**
     * 查看api后端路径详情
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
     * @return PermissionApi
     * @throws Exception
     */
    protected function getModel(array $params): PermissionApi
    {
        $model = PermissionApi::findOne([
            'id' => $params['id'] ?? null
        ]);
        if (null === $model) {
            throw new BusinessException("api后端路径不存在");
        }
        return $model;
    }
}