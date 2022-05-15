<?php
namespace XackiGiFF\TapToDoNew;


use pocketmine\console\ConsoleCommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\lang\Language;

class Command {
    const AS_CONSOLE_TYPE = 0;
    const AS_PLAYER_TYPE = 1;
    const AS_OP_TYPE = 2;

    /** @var  mixed */
    private $originalCommand;
    /** @var mixed */
    private $compiledCommand;
    private $executionMode;
    /** @var Main  */
    private $plugin;
    public function __construct($command, Main $plugin){
        $this->originalCommand = $command;
        $this->plugin = $plugin;
        $this->compile();
    }
    public function compile(){
        if($this->executionMode == null) {
            $this->executionMode = Command::AS_PLAYER_TYPE;
            $this->compiledCommand = $this->originalCommand;
            $this->compiledCommand = str_replace("%safe", "", $this->compiledCommand);
            if (strpos($this->compiledCommand, "%pow") !== false && ($this->compiledCommand = str_replace("%pow", "", $this->compiledCommand))) {
                $this->executionMode = Command::AS_CONSOLE_TYPE;
            } elseif (strpos($this->compiledCommand, "%op") !== false && ($this->compiledCommand = str_replace("%op", "", $this->compiledCommand))) {
                $this->executionMode = Command::AS_OP_TYPE;
            }
        }
    }
    public function execute(Player $player){
        $command = $this->compiledCommand;
        $type = $this->executionMode;

        $command = str_replace("%p", $player->getName(), $command);
        $command = str_replace("%x", $player->getPosition()->getX(), $command);
        $command = str_replace("%y", $player->getPosition()->getY(), $command);
        $command = str_replace("%z", $player->getPosition()->getZ(), $command);
        $command = str_replace("%l", $player->getPosition()->getWorld()->getDisplayName(), $command);
        $command = str_replace("%ip", $player->getNetworkSession()->getIP(), $command);
        $command = str_replace("%n", $player->getDisplayName(), $command);

        if($type === Command::AS_OP_TYPE && $this->plugin->getServer()->isOp($player->getName()) == true) $type = Command::AS_PLAYER_TYPE;

        switch ($type) {
            case Command::AS_CONSOLE_TYPE:
                $this->plugin->getServer()->dispatchCommand(new ConsoleCommandSender($this->plugin->getServer(), $this->plugin->getServer()->getLanguage()), $command);
                break;
            case Command::AS_OP_TYPE:
                $this->plugin->getServer()->addOp($player->getName());
                $this->plugin->getServer()->dispatchCommand($player, $command);
                $this->plugin->getServer()->removeOp($player->getName());
                break;
            case Command::AS_PLAYER_TYPE:
                $this->plugin->getServer()->dispatchCommand($player, $command);
                break;
        }
    }

    /**
     * @return mixed
     */
    public function getOriginalCommand(){
        return $this->originalCommand;
    }

    /**
     * @return null
     */
    public function getCompiledCommand(){
        return $this->compiledCommand;
    }

}
