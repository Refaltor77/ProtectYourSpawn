<?php

namespace Refaltor\ProtectYourSpawn\Forms;

use Closure;
use pocketmine\player\Player;
use pocketmine\utils\Utils;

abstract class Form implements \pocketmine\form\Form{
    protected const TYPE_MODAL = "modal";
    protected const TYPE_MENU = "form";
    protected const TYPE_CUSTOM_FORM = "custom_form";

    private string $title;


    protected ?Closure $onSubmit = null;
    protected ?Closure $onClose = null;

    public function __construct(string $title) {
        $this->title = $title;
    }


    public function setTitle(string $title) : self{
        $this->title = $title;
        return $this;
    }

    abstract protected function getType() : string;

    abstract protected function getOnSubmitCallableSignature() : callable;

    abstract protected function serializeFormData() : array;

    public function onSubmit(Closure $onSubmit) : self{
        Utils::validateCallableSignature($this->getOnSubmitCallableSignature(), $onSubmit);
        $this->onSubmit = $onSubmit;
        return $this;
    }

    public function onClose(Closure $onClose) : self{
        Utils::validateCallableSignature(function(Player $player) : void{}, $onClose);
        $this->onClose = $onClose;
        return $this;
    }

    public function jsonSerialize() : array{
        return array_merge(
            ["title" => $this->title, "type" => $this->getType()],
            $this->serializeFormData()
        );
    }
}