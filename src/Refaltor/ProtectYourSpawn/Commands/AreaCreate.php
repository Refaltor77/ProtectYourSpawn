<?php

namespace Refaltor\ProtectYourSpawn\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use Refaltor\ProtectYourSpawn\ProtectYourSpawn;

class AreaCreate extends Command implements PluginOwned
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
            if (!$sender->hasPermission('protectyourspawn.create.cmd')) {
                $sender->sendMessage("§6[§eProtectYourSpawn§6] §cYou don't have permission.");
            }
        }

        if (!$sender instanceof Player) {
            $sender->sendMessage("§6[§eProtectYourSpawn§6] §cExecutable command only in game.");
            return;
        }

        $uuid = $sender->getUniqueId()->getBytes();
        if (!isset(self::$fastCache[$uuid])) {
            self::$fastCache[$uuid] = ['1' => null, '2' => null];
            $sender->sendMessage("§6[§eProtectYourSpawn§6] §aPlease break two blocks to define the coordinates of the protected area.");
        } else {
            unset(self::$fastCache[$uuid]);
            $sender->sendMessage("§6[§eProtectYourSpawn§6] §cYou canceled the creation of the protected area.");
        }
    }

    /**
     * @return Plugin
     */
    public function getOwningPlugin(): Plugin
    {
        return $this->plugin;
    }
}