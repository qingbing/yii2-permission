<?php
/**
 * @link        http://www.phpcorner.net
 * @author      qingbing<780042175@qq.com>
 * @copyright   Chengdu Qb Technology Co., Ltd.
 */

namespace YiiPermission\interfaces;


use YiiHelper\services\interfaces\ICurdService;

/**
 * 接口 : api后端路径管理
 *
 * Interface IApiPathService
 * @package YiiPermission\interfaces
 */
interface IApiPathService extends ICurdService
{
    /**
     * 所有API接口，为菜单分配api提供
     *
     * @param array|null $params
     * @return array
     */
    public function allForTransfer(array $params = []): array;
}