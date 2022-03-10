<?php

namespace YiiPermission\models;

use YiiHelper\abstracts\Model;
use YiiPermission\models\traits\TPermissionModelBehavior;
use Zf\Helper\Exceptions\BusinessException;

/**
 * This is the model class for table "portal_permission_role".
 *
 * @property int $id 自增ID
 * @property string $code 角色代码
 * @property string $name 角色名称
 * @property string $remark 角色描述
 * @property int $sort_order 显示排序
 * @property int $is_enable 是否启用
 * @property string $operate_ip 操作IP
 * @property int $operate_uid 操作UID
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 *
 * @property-read PermissionMenu[] $menus
 * @property-read int $menuCount
 * @property-read int $viaUserCount 有效的角色用户关联
 */
class PermissionRole extends Model
{
    use TPermissionModelBehavior;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%permission_role}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'name'], 'required'],
            [['sort_order', 'is_enable', 'operate_uid'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['code', 'name'], 'string', 'max' => 50],
            [['remark'], 'string', 'max' => 200],
            [['operate_ip'], 'string', 'max' => 15],
            [['code'], 'unique'],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'          => '自增ID',
            'code'        => '角色代码',
            'name'        => '角色名称',
            'remark'      => '角色描述',
            'sort_order'  => '显示排序',
            'is_enable'   => '是否启用',
            'operate_ip'  => '操作IP',
            'operate_uid' => '操作UID',
            'created_at'  => '创建时间',
            'updated_at'  => '更新时间',
        ];
    }

    /**
     * 关联 : 获取角色下配置的所有菜单
     *
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getMenus()
    {
        return $this->hasMany(PermissionMenu::class, [
            'code' => 'menu_code'
        ])
            ->alias('menu')
            ->viaTable(PermissionRoleMenu::tableName(), [
                'role_code' => 'code'
            ]);
    }

    /**
     * 获取该角色配置的菜单数量
     *
     * @return int|string|null
     * @throws \yii\base\InvalidConfigException
     */
    public function getMenuCount()
    {
        return $this->getMenus()->count();
    }

    /**
     * 获取有效的用户、角色关联
     *
     * @return \yii\db\ActiveQuery
     */
    public function getViaUserRole()
    {
        return $this->hasMany(PermissionUserRole::class, [
            'role_code' => 'code'
        ]);
    }

    /**
     * 获取角色关联中的有效用户
     *
     * @return int|string|null
     */
    public function getViaUserCount()
    {
        return $this->getViaUserRole()->count();
    }

    /**
     * 检查是否可以删除
     *
     * @return bool
     * @throws BusinessException
     */
    public function beforeDelete()
    {
        if ($this->is_enable && $this->viaUserCount) {
            throw new BusinessException("启用角色中还有用户绑定关系，不能删除");
        }
        // 删除 role-menu 的关联关系
        PermissionRoleMenu::deleteAll(['role_code' => $this->code]);
        // 删除 user-role 的关联关系
        PermissionUserRole::deleteAll(['role_code' => $this->code]);
        return parent::beforeDelete();
    }
}
