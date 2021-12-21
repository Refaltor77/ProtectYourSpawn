<?php

namespace Refaltor\ProtectYourSpawn\Events\Listeners;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\player\Player;
use Refaltor\ProtectYourSpawn\ProtectYourSpawn;

class PlayerListeners implements Listener
{
    /** @var ProtectYourSpawn  */
    private ProtectYourSpawn $plugin;

    /** @var array  */
    private array $microCache;

    public function __construct(ProtectYourSpawn $plugin)
    {
        $this->plugin = $plugin;
        $this->microCache = ['name' => []];
    }

    private function getPlugin(): ProtectYourSpawn
    {
        return $this->plugin;
    }

    public function onHunger(PlayerExhaustEvent $event): void
    {
        $player = $event->getPlayer();
        if (!$player instanceof Player) return;
        $api = $this->getPlugin()->getApi();

        if ($api->isInArea($player->getPosition())) {
            $flags = $api->getFlagsAreaByPosition($player->getPosition());
            if (!$flags['hunger']) {
                $event->cancel();
            }
        }
    }

    public function onDrop(PlayerDropItemEvent $event): void
    {
        $player = $event->getPlayer();
        $notifyBool = $this->getPlugin()->notification['player_drop_item'];
        $api = $this->getPlugin()->getApi();

        if ($api->isInArea($player->getPosition())) {
            $flags = $api->getFlagsAreaByPosition($player->getPosition());
            if (!$flags['dropItem']) {
                if ($this->getPlugin()->getServer()->isOp($player->getName()) || $player->hasPermission('protectyourspawn.dropitem.event')) return;
                $event->cancel();
                if ($notifyBool) {
                    $name = $api->getNameAreaByPosition($player->getPosition());
                    $player->sendMessage("§6[§eProtectYourSpawn§6] §cYou cannot drop an item in the zone §6$name §c!");
                }
            }
        }
    }


    public function onConsume(PlayerItemConsumeEvent $event): void
    {
        $player = $event->getPlayer();
        $api = $this->getPlugin()->getApi();

        if ($api->isInArea($player->getPosition())) {
            $flags = $api->getFlagsAreaByPosition($player->getPosition());
            if (!$flags['consume']) {
                if ($this->getPlugin()->getServer()->isOp($player->getName()) || $player->hasPermission('protectyourspawn.consume.event')) return;
                $event->cancel();
                $notifyBool = $this->getPlugin()->notification['player_consume_food'];
                if ($notifyBool) {
                    $name = $api->getNameAreaByPosition($player->getPosition());
                    $player->sendMessage("§6[§eProtectYourSpawn§6] §cYou can not eat in the area §6$name §c!");
                }
            }
        }
    }

    public function onCmd(PlayerCommandPreprocessEvent $event): void
    {
        $player = $event->getPlayer();
        $api = $this->getPlugin()->getApi();

        if ($api->isInArea($player->getPosition())) {
            $flags = $api->getFlagsAreaByPosition($player->getPosition());
            if (!$flags['cmd']) {
                if ($this->getPlugin()->getServer()->isOp($player->getName()) || $player->hasPermission('protectyourspawn.cmd.event')) return;
                $event->cancel();
                $notifyBool = $this->getPlugin()->notification['player_cmd'];
                if ($notifyBool) {
                    $name = $api->getNameAreaByPosition($player->getPosition());
                    $player->sendMessage("§6[§eProtectYourSpawn§6] §cYou cannot run commands in the §6$name §c!");
                }
            }
        }
    }

    public function onInteract(PlayerInteractEvent $event): void
    {
        $player = $event->getPlayer();
        $api = $this->getPlugin()->getApi();

        if ($api->isInArea($player->getPosition())) {
            $flags = $api->getFlagsAreaByPosition($player->getPosition());
            if (!$flags['place']) {
                if ($this->getPlugin()->getServer()->isOp($player->getName()) || $player->hasPermission('protectyourspawn.place.event')) return;
                $event->cancel();
                $notifyBool = $this->getPlugin()->notification['block_place'];
                if ($notifyBool) {
                    $name = $api->getNameAreaByPosition($player->getPosition());
                    $player->sendMessage("§6[§eProtectYourSpawn§6] §cYou cannot place block in the area §6$name §c!");
                }
            }
        }
    }

    public function onChatSend(PlayerChatEvent $event): void
    {
        $player = $event->getPlayer();
        $api = $this->getPlugin()->getApi();

        if ($api->isInArea($player->getPosition())) {
            $flags = $api->getFlagsAreaByPosition($player->getPosition());
            if (!$flags['chat']) {
                if ($this->getPlugin()->getServer()->isOp($player->getName()) || $player->hasPermission('protectyourspawn.chat.event')) return;
                $event->cancel();
                $notifyBool = $this->getPlugin()->notification['player_chat'];
                if ($notifyBool) {
                    $name = $api->getNameAreaByPosition($player->getPosition());
                    $player->sendMessage("§6[§eProtectYourSpawn§6] §cYou cannot send messages in the area §6$name §c!");
                }
            }
        }
    }

    public function onDamage(EntityDamageByEntityEvent $event): void
    {
        $victim = $event->getEntity();
        $damager = $event->getDamager();
        if ($victim instanceof Player && $damager instanceof Player) {
            $api = $this->getPlugin()->getApi();
            if ($api->isInArea($victim->getPosition())) {
                $flags = $api->getFlagsAreaByPosition($victim->getPosition());
                if (!$flags['pvp']) {
                    $event->cancel();
                    $notifyBool = $this->getPlugin()->notification['player_pvp'];
                    if ($notifyBool) {
                        $name = $api->getNameAreaByPosition($victim->getPosition());
                        $damager->sendMessage("§6[§eProtectYourSpawn§6] §cYou can not pvp in the zone §6$name §c!");
                    }
                }
            }
        }
    }

    public function onMove(PlayerMoveEvent $event): void
    {
        $player = $event->getPlayer();

        $notifyBool = $this->getPlugin()->notification['player_entering_a_zone'];
        if ($notifyBool) {
            $xuid = $player->getXuid();
            $api = $this->getPlugin()->getApi();
            if ($api->isInArea($player->getPosition())) {
                $name = $api->getNameAreaByPosition($player->getPosition());
                $this->microCache['name'][$xuid] = $name;
                if (!isset($this->microCache[$xuid])) {
                    $this->microCache[$xuid] = true;
                    $player->sendMessage("§6[§eProtectYourSpawn§6] §aYou enter the protected area: §6$name");
                }
            } elseif (isset($this->microCache[$xuid]) && $this->microCache[$xuid]) {
                unset($this->microCache[$xuid]);
                $notifyBool = $this->getPlugin()->notification['player_leave_a_zone'];
                if ($notifyBool) {
                    if (isset($this->microCache['name'][$xuid])) $player->sendMessage("§6[§eProtectYourSpawn§6] §aYou have left the area: §6" . $this->microCache['name'][$xuid]);
                }
            }
        }
    }
}