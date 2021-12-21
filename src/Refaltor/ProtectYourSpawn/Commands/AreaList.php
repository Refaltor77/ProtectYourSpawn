<?php

namespace Refaltor\ProtectYourSpawn\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use Refaltor\ProtectYourSpawn\Forms\CustomForm;
use Refaltor\ProtectYourSpawn\Forms\CustomFormResponse;
use Refaltor\ProtectYourSpawn\Forms\Elements\Button;
use Refaltor\ProtectYourSpawn\Forms\Elements\Image;
use Refaltor\ProtectYourSpawn\Forms\Elements\Input;
use Refaltor\ProtectYourSpawn\Forms\Elements\Toggle;
use Refaltor\ProtectYourSpawn\Forms\MenuForm;
use Refaltor\ProtectYourSpawn\Forms\ModalForm;
use Refaltor\ProtectYourSpawn\Manager\Area;
use Refaltor\ProtectYourSpawn\ProtectYourSpawn;

class AreaList extends Command implements PluginOwned
{
    /** @var ProtectYourSpawn  */
    private ProtectYourSpawn $plugin;

    /** @var array  */
    public static array $fastCache = [];

    /**
     * AreaCreate constructor.
     * @param ProtectYourSpawn $plugin
     * @param string $name
     * @param Translatable|string $description
     * @param Translatable|string|null $usageMessage
     * @param array $aliases
     */
    public function __construct(ProtectYourSpawn $plugin, string $name, Translatable|string $description = "", Translatable|string|null $usageMessage = null, array $aliases = [])
    {
        $this->plugin = $plugin;
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed|void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$this->getOwningPlugin()->getServer()->isOp($sender->getName())) {
            if (!$sender->hasPermission('protectyourspawn.list.cmd')) {
                $sender->sendMessage("§6[§eProtectYourSpawn§6] §cYou don't have permission.");
            }
        }

        if (!$sender instanceof Player) {
            $sender->sendMessage("§6[§eProtectYourSpawn§6] §cExecutable command only in game.");
            return;
        }

        $array = $this->getOwningPlugin()->getApi()->cache;
        $buttons = [];
        $arrayValue = [];
        $i = 0;
        foreach ($array as $name => $values) {
            $buttons[] = new Button('§c- §6' . $name . ' §c-', new Image('textures/items/apple'));
            $arrayValue[$i] = $name;
            $i++;
        }

        $sender->sendForm(new MenuForm(
            '§6- §elist of areas§6-',
            '§7Here is the list of areas present on the server, you can modify their atributs!',
            $buttons,
            function (Player $player, Button $button) use($arrayValue) : void {
                $value = $button->getValue();
                $name = $arrayValue[$value];
                $player->sendForm(new MenuForm(
                    "§6- §eCarateristic of the area §c{$name} §6-",
                    '§7You can change the attributes of this area if you wish.',
                    [
                        new Button('§aChange the attributes', new Image('textures/items/carrot')),
                        new Button('§eRedefine the coordinates', new Image('textures/items/compass_item')),
                        new Button('§cDelete the area [§4/!\§c]', new Image('textures/items/flint_and_steel'))
                    ],
                    function (Player $player, Button $button) use ($name): void
                    {
                        $value = $button->getValue();
                        switch ($value) {
                            case 0:
                                $player->sendForm(new CustomForm(
                                    "§6- §eModification of the zone §c{$name} §6-",
                                    [
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
                                    function (Player $player, CustomFormResponse $response) use ($name): void
                                    {
                                        list($pvp, $place, $break, $hunger, $drop, $tnt, $cmd, $chat, $consume) = $response->getValues();
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
                                        $this->getOwningPlugin()->getApi()->setFlagsByName($name, $flags);
                                        $player->sendMessage("§6[§eProtectYourSpawn§6]§a The area §6$name §ahas been modified with success !");
                                    }
                                ));
                                break;
                            case 1:
                                $uuid = $player->getUniqueId()->getBytes();
                                if (!isset(self::$fastCache[$uuid])) {
                                    self::$fastCache[$uuid] = ['1' => null, '2' => null, 'name' => $name];
                                } else {
                                    unset(self::$fastCache[$uuid]);
                                    $player->sendMessage("§6[§eProtectYourSpawn§6]§c You canceled your action !");
                                }
                                break;
                            case 2:
                                $player->sendForm(new ModalForm(
                                    '§4- §cCareful !§c -',
                                    "§cIf you accept the deletion of your zone, there is no way to recover the data from it !",
                                    function (Player $player, bool $response) use ($name) : void
                                    {
                                        if ($response) {
                                            $this->getOwningPlugin()->getApi()->deleteAreaByName($name);
                                            $player->sendMessage("§6[§eProtectYourSpawn§6]§a You have deleted the area §6{$name} §a!");
                                        } else $player->sendMessage("§6[§eProtectYourSpawn§6]§a You cancelled the suppression of the zone 6{$name} §a!");
                                    }
                                ));
                                break;
                        }
                    }
                ));
            }
        ));
    }

    /**
     * @return Plugin
     */
    public function getOwningPlugin(): Plugin
    {
        return $this->plugin;
    }
}