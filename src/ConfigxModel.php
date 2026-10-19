<?php

namespace Ichynul\Configx;

use Encore\Admin\Auth\Database\Permission;
use Encore\Admin\Config\ConfigModel;

class ConfigxModel extends ConfigModel
{
    protected $fillable = array('name', 'value', 'description');

    /**
     * [Security] Save-side whitelist guard, same source as the loading
     * whitelist of laravel-admin-ext/config (v1.1.1+).
     *
     * The admin_config table is the classic backdoor target: an attacker who
     * stole an admin account (or got SQLi) could otherwise persist keys like
     * `view.compiled` / `session.driver` here and hijack runtime behaviour.
     * Every write path of configx funnels through save(), so a name check
     * here closes the whole surface. Internal metadata rows (__configx*) are
     * exempt - they never reach config injection.
     *
     * Prefixes are configurable via config('admin.extensions.config.allowed_prefixes').
     *
     * @param array $options
     * @return bool
     * @throws \Exception
     */
    public function save(array $options = [])
    {
        $name = (string) $this->name;

        $isInternal = strpos($name, '__configx') === 0;

        if (!$isInternal && ! static::isAllowedName($name)) {
            \Illuminate\Support\Facades\Log::warning('[Configx] blocked dangerous config write', [
                'name' => $name,
            ]);

            throw new \Exception("Configx: config name [{$name}] is not in the allowed prefixes, saving refused.");
        }

        return parent::save($options);
    }

    /**
     * @param string $name
     * @return bool
     */
    protected static function isAllowedName($name)
    {
        $allowed = (array) config('admin.extensions.config.allowed_prefixes', ['base.']);

        foreach ($allowed as $prefix) {
            if ((string) $prefix !== '' && stripos($name, $prefix) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Group configs by prefix
     *
     * @param [string] $prefix
     * @return void
     */
    public static function group($prefix)
    {
        return self::where('name', 'like', "{$prefix}.%")->get()->toArray();
    }

    /**
     * Get name prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        $arr = explode('.', $this->name);
        return count($arr) ? $arr[0] : '';
    }

    /**
     * Create prefix permission
     *
     * @param [string] $prefix
     * @return void
     */
    public static function createPermission($prefix, $name)
    {
        $slug = 'confix.tab.' . $prefix;
        $name = trans('admin.configx.header') . '-' . $name;
        if ($pm = Permission::where('slug', $slug)->first()) {
            if ($pm->name != $name) {
                $pm->update(['name' => $name]);
            }
            return;
        }
        Permission::create([
            'name' => $name,
            'slug' => $slug,
            'http_path' => "/configx/tab-{$prefix}",
        ]);
    }
}
