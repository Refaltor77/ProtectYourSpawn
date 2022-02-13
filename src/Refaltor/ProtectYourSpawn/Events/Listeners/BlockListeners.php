<?php

namespace Refaltor\ProtectYourSpawn\Events\Listeners;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use Refaltor\ProtectYourSpawn\Commands\AreaCreate;
use Refaltor\ProtectYourSpawn\Commands\AreaList;
use Refaltor\ProtectYourSpawn\Forms\CustomForm;
use Refaltor\ProtectYourSpawn\Forms\CustomFormResponse;
use Refaltor\ProtectYourSpawn\Forms\Elements\Input;
use Refaltor\ProtectYourSpawn\Forms\Elements\Toggle;
use Refaltor\ProtectYourSpawn\Manager\Area;
use Refaltor\ProtectYourSpawn\ProtectYourSpawn;

class BlockListeners implements Listener
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

    public function onBreak(BlockBreakEvent $event): void
    {
        $block = $event->getBlock();
        $player = $event->getPlayer();
        $api = $this->getPlugin()->getApi();

        if ($api->isInArea($block->getPosition())) {
            $flags = $api->getFlagsAreaByPosition($block->getPosition());
            if (!$flags['break'])  {
                if (!$this->getPlugin()->getServer()->isOp($player->getName()) || $player->hasPermission('protectyourspawn.breakblock.event')) {
                    $event->cancel();
                    $notifyBool = $this->getPlugin()->notification['block_break'];
                    if ($notifyBool) {
                        $name = $api->getNameAreaByPosition($block->getPosition());
                        $player->sendMessage("§6[§eProtectYourSpawn§6] §cYou cannot break a block in the area §6$name §c!");
                    }
                }
            }
        } else {
            $uuid = $player->getUniqueId()->getBytes();
            if (isset(AreaCreate::$fastCache[$uuid])) {
                if (is_null(AreaCreate::$fastCache[$uuid]['1'])) {
                    AreaCreate::$fastCache[$uuid]['1'] = $block->getPosition();
                    $player->sendMessage("§6[§eProtectYourSpawn§6]§6 [1] §aThe first position of your zone is set.");
                    $event->cancel();
                } elseif (is_null(AreaCreate::$fastCache[$uuid]['2'])) {
                    AreaCreate::$fastCache[$uuid]['2'] = $block->getPosition();
                    $player->sendMessage("§6[§eProtectYourSpawn§6]§6 [2] §aThe second position of your zone is created, go to the creation of your zone !");
                    $event->cancel();
                    $player->sendForm(new CustomForm(
                        '§6- §eCreation of the zone §6-',
                        [
                            new Input('§6» §eName of the area', 'Spawn'),
                            new Toggle('§6» §ePVP', false),
                            new Toggle('§6» §ePlacing blocks', false),
                            new Toggle('§6» §eBreaking blocks', false),
                            new Toggle('§6» §eStarving', true),
                            new Toggle('§6» §eDrop items', true),
                            new Toggle('§6» §eThe tnt explodes', false),
                            new Toggle('§6» §eCommand [/]', true),
                            new Toggle('§6» §eMessage send in chat', true),
                            new Toggle('§6» §eConsume item', false),
                        ],
                        function (Player $player, CustomFormResponse $response) use ($uuid, $api): void
                        {
                            unset(AreaCreate::$fastCache[$uuid]);
                            list($name, $pvp, $place, $break, $hunger, $drop, $tnt, $cmd, $chat, $consume) = $response->getValues();
                            if (isset($api->cache[$name])) {
                                $player->sendMessage("§6[§eProtectYourSpawn§6]§c The name of the area already exists !");
                                return;
                            }
                            $flags = Area::createBaseFlags();
                            $flags['pvp'] = $pvp;
                            $flags['place'] = $place;
                            $flags['break'] = $break;
                            $flags['hunger'] = $hunger;
                            $flags['dropItem'] = $drop;
                            $flags['tnt'] = $tnt;
                            $flags['cmd'] = $cmd;
                            $flags['chat'] = $chat;
                            $flags['consume'] = $consume;
                            $area = new Area(AreaCreate::$fastCache[$uuid]['1'], AreaCreate::$fastCache[$uuid]['2'], $flags, $name);
                            $this->getPlugin()->getApi()->createArea($area);
                            $player->sendMessage("§6[§eProtectYourSpawn§6]§a The area §6$name §ahas been created with success !");
                        }
                    ));
                }
            } elseif (isset(AreaList::$fastCache[$uuid])) {
                if (is_null(AreaList::$fastCache[$uuid]['1'])) {
                    AreaList::$fastCache[$uuid]['1'] = $block->getPosition();
                    $player->sendMessage("§6[§eProtectYourSpawn§6]§6 [1 (modified)] §aThe first position of your zone is set.");
                    $event->cancel();
                } elseif (is_null(AreaList::$fastCache[$uuid]['2'])) {
                    AreaList::$fastCache[$uuid]['2'] = $block->getPosition();
                    $player->sendMessage("§6[§eProtectYourSpawn§6]§6 [2 (modified)] §aThe second position of your zone is set.");
                    $this->getPlugin()->getApi()->setPositionByName(AreaList::$fastCache[$uuid]['name'], AreaList::$fastCache[$uuid]['1'], AreaList::$fastCache[$uuid]['2']);
                    $player->sendMessage("§6[§eProtectYourSpawn§6]§a The new positions of the zone §6" . AreaList::$fastCache[$uuid]['name'] . '§a has created !');
                    $event->cancel();
                    unset(AreaList::$fastCache[$uuid]);
                }
            }
        }
    }

    public function onPlace(BlockPlaceEvent $event): void
    {
        $block = $event->getBlock();
        $player = $event->getPlayer();
        $api = $this->getPlugin()->getApi();

        if ($api->isInArea($block->getPosition())) {
            $flags = $api->getFlagsAreaByPosition($block->getPosition());
            if (!$flags['place'])  {
                if ($this->getPlugin()->getServer()->isOp($player->getName()) || $player->hasPermission('protectyourspawn.placeblock.event')) return;
                $event->cancel();
                $notifyBool = $this->getPlugin()->notification['block_place'];
                if ($notifyBool) {
                    $name = $api->getNameAreaByPosition($block->getPosition());
                    $player->sendMessage("§6[§eProtectYourSpawn§6] §cYou cannot place a block in the area §6$name §c!");
                }
            }
        }
    }
}