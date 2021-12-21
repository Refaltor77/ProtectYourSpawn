<?php

namespace Refaltor\ProtectYourSpawn\Events\Listeners;

use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\Listener;
use Refaltor\ProtectYourSpawn\ProtectYourSpawn;

class EntityListeners implements Listener
{
    /** @var ProtectYourSpawn  */
    private ProtectYourSpawn $plugin;


    public function __construct(ProtectYourSpawn $plugin)
    {
        $this->plugin = $plugin;
    }

    private function getPlugin(): ProtectYourSpawn
    {
        return $this->plugin;
    }

    public function onEntityExplode(EntityExplodeEvent $event): void
    {
        $api = $this->getPlugin()->getApi();
        if ($api->isInArea($event->getPosition())) {
            $flags = $api->getFlagsAreaByPosition($event->getPosition());
            if (!$flags['tnt']) $event->cancel();
        }
    }

}