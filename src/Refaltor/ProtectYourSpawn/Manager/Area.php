<?php

namespace Refaltor\ProtectYourSpawn\Manager;

use pocketmine\world\Position;

class Area
{
    /** @var array  */
    private array $flags;

    /** @var string  */
    private string $positions;

    /** @var string  */
    private string $name;

    /**
     * Area constructor.
     * @param Position $pos1
     * @param Position $pos2
     * @param array $flags
     * @param string $name
     */
    public function __construct(Position $pos1, Position $pos2, array $flags, string $name)
    {
        $this->flags = $flags;
        $minimumX = intval(min($pos1->getX(), $pos2->getX()));
        $maximumX = intval(max($pos1->getX(), $pos2->getX()));
        $minimumZ = intval(min($pos1->getZ(), $pos2->getZ()));
        $maximumZ = intval(max($pos1->getZ(), $pos2->getZ()));
        $string = $minimumX.':'.$maximumX.':'.$minimumZ.':'.$maximumZ;
        $this->positions = $string;
        $this->name = $name;
    }

    /**
     * @return array
     */
    public static function createBaseFlags(): array
    {
        return  [
            'pvp' => false,
            'break' => false,
            'place' => false,
            'hunger' => true,
            'dropItem' => true,
            'chat' => true,
            'cmd' => true,
            'tnt' => false,
            'consume' => true,
        ];
    }

    /**
     * @return array
     */
    public function getFlags(): array
    {
        return $this->flags;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return  $this->name;
    }

    /**
     * @return string
     */
    public function getStringPosition(): string
    {
        return $this->positions;
    }
}