<?php

namespace Refaltor\ProtectYourSpawn;

use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\plugin\PluginBase;
use Refaltor\ProtectYourSpawn\Commands\AreaCreate;
use Refaltor\ProtectYourSpawn\Commands\AreaList;
use Refaltor\ProtectYourSpawn\Events\Listeners\BlockListeners;
use Refaltor\ProtectYourSpawn\Events\Listeners\EntityListeners;
use Refaltor\ProtectYourSpawn\Events\Listeners\PlayerListeners;
use Refaltor\ProtectYourSpawn\Manager\AreaManager;

class ProtectYourSpawn extends PluginBase
{
    /** @var array  */
    public array $notification = [];

    /** @var AreaManager  */
    private AreaManager $api;

    protected function onEnable(): void
    {
        $this->saveDefaultConfig();
        @mkdir($this->getDataFolder() . 'temp/');
        $this->notification = $this->getConfig()->get('notification');

        $permissions = [
            'protectyourspawn.create.cmd',
            'protectyourspawn.list.cmd',

            'protectyourspawn.dropitem.event',
            'protectyourspawn.chat.event',
            'protectyourspawn.cmd.event',
            'protectyourspawn.breakblock.event',
            'protectyourspawn.placeblock.event',
            'protectyourspawn.consume.event'
        ];
        foreach ($permissions as $permission)PermissionManager::getInstance()->addPermission(new Permission($permission));

        $events = [
            new BlockListeners($this),
            new EntityListeners($this),
            new PlayerListeners($this)
        ];
        foreach ($events as $event) $this->getServer()->getPluginManager()->registerEvents($event, $this);

        $this->getServer()->getCommandMap()->registerAll('ProtectYourSpawn', [
            new AreaCreate($this, 'areacreate', 'Creates a protective zone.', '/areacreate', ['areac']),
            new AreaList($this, 'arealist', 'List of protected areas.', '/arealist', ['al'])
        ]);


        $this->api = new AreaManager($this);
        parent::onEnable();
    }

    protected function onDisable(): void
    {
        $this->getApi()->saveAllData();
        parent::onDisable();
    }


    /**
     * @return AreaManager
     */
    public function getApi(): AreaManager
    {
        return $this->api;
    }
}