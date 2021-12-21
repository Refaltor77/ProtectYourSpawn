<?php

namespace Refaltor\ProtectYourSpawn\Manager;

use pocketmine\utils\Config;
use pocketmine\world\Position;
use Refaltor\ProtectYourSpawn\ProtectYourSpawn;

class AreaManager
{
    /** @var ProtectYourSpawn  */
    private ProtectYourSpawn $plugin;

    /** @var array  */
    public array $cache;

    /** @var Config  */
    private Config $microDatabase;

    /**
     * AreaManager constructor.
     * @param ProtectYourSpawn $plugin
     */
    public function __construct(ProtectYourSpawn $plugin)
    {
        $this->plugin = $plugin;
        $path = $plugin->getDataFolder();
        $this->microDatabase = new Config($path . '/temp/db.json', Config::JSON);;
        $this->cache = $this->microDatabase->getAll();
    }


    public function saveAllData(): void
    {
        $this->microDatabase->setAll($this->cache);
        $this->microDatabase->save();
    }

    /**
     * @param string $name
     * @return array
     */
    public function getFlagsByName(string $name): array
    {
        if (isset($this->cache[$name])) {
            return $this->cache[$name]['flags'];
        }
        return Area::createBaseFlags();
    }

    /**
     * @param string $name
     * @param array $flags
     */
    public function setFlagsByName(string $name, array $flags): void
    {
        if (isset($this->cache[$name])) {
            $this->cache[$name]['flags'] = $flags;
        }
    }

    /**
     * @param string $name
     * @param Position $pos1
     * @param Position $pos2
     */
    public function setPositionByName(string $name, Position $pos1, Position $pos2): void
    {
        if (isset($this->cache[$name])) {
            $minimumX = intval(min($pos1->getX(), $pos2->getX()));
            $maximumX = intval(max($pos1->getX(), $pos2->getX()));
            $minimumZ = intval(min($pos1->getZ(), $pos2->getZ()));
            $maximumZ = intval(max($pos1->getZ(), $pos2->getZ()));
            $string = $minimumX.':'.$maximumX.':'.$minimumZ.':'.$maximumZ;
            $this->cache[$name]['positions'] = $string;
        }
    }

    public function deleteAreaByName(string $name): void
    {
        if (isset($this->cache[$name])) {
            unset($this->cache[$name]);
        }
    }

    /**
     * @param Area $area
     */
    public function createArea(Area $area): void
    {
        $name = $area->getName();
        $positions = $area->getStringPosition();
        $flags= $area->getFlags();
        $this->cache[$name] = ['positions' => $positions, 'flags' => $flags];
    }

    /**
     * @param Position $position
     * @return bool
     */
    public function isInArea(Position $position): bool
    {
        $x = $position->getX();
        $z = $position->getZ();
        foreach ($this->cache as $name => $value) {
            $stringExplode = explode(':', $value['positions']);
            if ($x >= $stringExplode[0] && $x <= $stringExplode[1]) {
                if ($z >= $stringExplode[2] && $z <= $stringExplode[3]) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getFlagsAreaByPosition(Position $position): array
    {
        $x = $position->getX();
        $z = $position->getZ();
        foreach ($this->cache as $name => $value) {
            $stringExplode = explode(':', $value['positions']);
            if ($x >= $stringExplode[0] && $x <= $stringExplode[1]) {
                if ($z >= $stringExplode[2] && $z <= $stringExplode[3]) {
                    return $value['flags'];
                }
            }
        }
        return  [
            'pvp' => true,
            'break' => true,
            'place' => true,
            'hunger' => true,
            'dropItem' => true,
            'chat' => true,
            'cmd' => true,
            'tnt' => true
        ];
    }

    public function getNameAreaByPosition(Position $position): string
    {
        $x = $position->getX();
        $z = $position->getZ();
        foreach ($this->cache as $name => $value) {
            $stringExplode = explode(':', $value['positions']);
            if ($x >= $stringExplode[0] && $x <= $stringExplode[1]) {
                if ($z >= $stringExplode[2] && $z <= $stringExplode[3]) {
                    return $name;
                }
            }
        }
        return  '404';
    }
}