<?php
namespace XackiGiFF\TapToDoNew;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;

use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\world\Position;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\Server;
use pocketmine\world\World;
use pocketmine\utils\TextFormat;
use function mkdir;

class Main extends PluginBase implements CommandExecutor, Listener{
	public $prefix;
    public $sessions;
    /** @var  Block[] */
    public $blocks;
    /** @var  Config */
    private $blocksCfg;

	public function onEnable() : void{
		$this->prefix = TextFormat::BLUE . "[" . TextFormat::AQUA . "TapToDo" . TextFormat::BLUE . "] ";
        $this->getLogger()->info($this->prefix . TextFormat::GREEN . "Loading...");
		$this->sessions = [];
        $this->blocks = [];
        $this->saveResource("blocks.yml");
		$this->blocksCfg = (new ConfigUpdater(new Config($this->getDataFolder() . "blocks.yml", Config::YAML, array()), $this))->checkConfig();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
		if (!$this->blocksCfg->exists("blocks")) {
            $this->blocksCfg->set("blocks", []);
        }
        $this->getLogger()->info($this->prefix . TextFormat::GREEN . "Reloading blocks due to world...");
		$this->parseBlockData();

	}

    public function onCommand(CommandSender $player, Command $command, string $label, array $args) : bool {
        switch($command->getName()) {
            case "t":
                if (isset($args[0])) {
                    switch($args[0]){
                        default:
                            if($player->hasPermission("taptodo.command")){
                                $player->sendMessage($this->prefix . TextFormat::YELLOW . "Help:");
                                $player->sendMessage("§d × §a/t add §d- §fAdd a macros");
                                $player->sendMessage("§d × §a/t del  §d- §fDelete a last macros");
                                $player->sendMessage("§d × §a/t delall  §d- §fDelete all macros");
                                $player->sendMessage("§d × §a/t list §d- §fshow macros on block");
                            } else {
                                $player->sendMessage($this->prefix . TextFormat::RED . "You don`t have permissions!");
                            }
                            break;
                        case "a":
                        case "add":
                            if($player->hasPermission("taptodo.command.add")){
                                if ($player instanceof Player) {
                                    if(isset($args[1])){
                                        $player->sendMessage($this->prefix . TextFormat::YELLOW . "Tap a block to add macros.");
                                        $this->sessions[$player->getName()] = $args;
                                } else {
                                        $player->sendMessage($this->prefix . TextFormat::RED . "You need input a command!");
                                    }
                                } else {
                                    $player->sendMessage($this->prefix . TextFormat::RED . "Use only in game!");
                                }
                            } else {
                                $player->sendMessage($this->prefix . TextFormat::RED . "You don`t have permissions!");
                                }
                            break;
                        case "d":
                        case "del":
                            if($player->hasPermission("taptodo.command.del")){
                                if ($player instanceof Player) {
                                        $player->sendMessage($this->prefix . TextFormat::YELLOW . "Tap a block to delete last macros.");
                                        $this->sessions[$player->getName()] = $args;
                                } else {
                                    $player->sendMessage($this->prefix . TextFormat::RED . "Use only in game!");
                                }
                            } else {
                                $player->sendMessage($this->prefix . TextFormat::RED . "You don`t have permissions!");
                                }
                            break;
                        case "da":
                        case "delall":
                            if($player->hasPermission("taptodo.command.delall")){
                                if ($player instanceof Player) {
                                        $player->sendMessage($this->prefix . TextFormat::YELLOW . "Tap a block to delete all macros.");
                                        $this->sessions[$player->getName()] = $args;
                                } else {
                                    $player->sendMessage($this->prefix . TextFormat::RED . "Use only in game!");
                                }
                            } else {
                                $player->sendMessage($this->prefix . TextFormat::RED . "You don`t have permissions!");
                                }
                            break;
                        case "l":
                        case "ls":
                        case "list":
                           if($player->hasPermission("taptodo.command.del")){
                                if ($player instanceof Player) {
                                    $player->sendMessage($this->prefix . TextFormat::YELLOW . "Tap a block to show macros.");
                                   $this->sessions[$player->getName()] = $args;
                                } else {
                                    $player->sendMessage($this->prefix . TextFormat::RED . "Use only in game!");
                                }
                            } else {
                                  $player->sendMessage($this->prefix . TextFormat::RED . "You don`t have permissions!");
                                }
                            break;
                    }
                } else {
                    if($player->hasPermission("taptodo.command")){
                        $player->sendMessage($this->prefix . TextFormat::YELLOW . "Help:");
                        $player->sendMessage("§d × §a/t add §d- §fAdd a macros");
                        $player->sendMessage("§d × §a/t del  §d- §fDelete a last macros");
                        $player->sendMessage("§d × §a/t delall  §d- §fDelete all macros");
                        $player->sendMessage("§d × §a/t list §d- §fshow macros on block");
                    } else {
                        $player->sendMessage($this->prefix . TextFormat::RED . "You don`t have permissions!");
                    }
                }
                break;
        }
        return true;
    }

    public function onInteract(PlayerInteractEvent $event){
        $p = $event->getPlayer();
        if(isset($this->sessions[$event->getPlayer()->getName()])){
            $args = $this->sessions[$event->getPlayer()->getName()];
            switch($args[0]){
                case "a":
                case "add":
                    $event->cancel();
                    if(isset($args[1])){
                        if(($b = $this->getBlock($event->getBlock()->getPosition(), null, null, null)) instanceof Block){
                            array_shift($args);
                            $b->addCommand(implode(" ", $args));
                            $event->getPlayer()->sendMessage($this->prefix . TextFormat::GREEN . "Command was added to list.");
                        }
                        else{
                            array_shift($args);
                            $event->getPlayer()->sendMessage($event->getBlock()->getPosition());
                            $this->addBlock($event->getBlock()->getPosition(), implode(" ", $args));
                            $event->getPlayer()->sendMessage($this->prefix . TextFormat::GREEN . "The first command was added.");
                        }
                    }
                    else{
                        $event->getPlayer()->sendMessage($this->prefix . TextFormat::RED . "You need input a command!");
                    }
                    break;
                case "d":
                case "del":
                    $event->cancel();
                    if(($b = $this->getBlock($event->getBlock()->getPosition(), null, null, null)) instanceof Block){
                        $cmds = $b->getCommands();
                        $cmd = end($cmds);
                        $cmd = $this->toArray($cmd);
                        if(($b->deleteCommand(implode(" ", $cmd))) !== false){
                            $event->getPlayer()->sendMessage($this->prefix . TextFormat::GREEN . "Command removed.");
                        } else {
                            $event->getPlayer()->sendMessage($this->prefix . TextFormat::RED . "Couldn't find command.");
                        }
                    }
                    else{
                        $event->getPlayer()->sendMessage($this->prefix . TextFormat::RED . "Block does not exist!");
                    }
                    break;
                case "da":
                case "delall":
                    $event->cancel();
                    $name = array_shift($args);
                    if(($b = $this->getBlock($event->getBlock()->getPosition(), null, null, null)) instanceof Block){
                        $this->deleteBlock($b);
                        $event->getPlayer()->sendMessage($this->prefix . TextFormat::GREEN . "All marco commands have been successfully deleted.");
                    }
                    else{
                        $event->getPlayer()->sendMessage($this->prefix . TextFormat::RED . "No macro commands!");
                    }
                    break;
                case "l":
                case "ls":
                case "list":
                    $event->cancel();
                    if(($b = $this->getBlock($event->getBlock()->getPosition(), null, null, null)) instanceof Block){
                        $event->getPlayer()->sendMessage($this->prefix . TextFormat::YELLOW . "Macros:");
                        foreach($b->getCommands() as $cmd){
                            $event->getPlayer()->sendMessage(TextFormat::GREEN . "- " . TextFormat::WHITE . $cmd);
                        }
                    }
                    else{
                        $event->getPlayer()->sendMessage($this->prefix . TextFormat::RED . "Couldn't find command on this block!");
                    }
                    break;
            }
            unset($this->sessions[$event->getPlayer()->getName()]);
        }
        else{
            if(($b = $this->getBlock($event->getBlock()->getPosition(), null, null, null)) instanceof Block && $event->getPlayer()->hasPermission("taptodo.tap")){
                $b->executeCommands($event->getPlayer());
            }
        }
    }

    /**
     * @param $array
     * @return string
     */
	public function array_to_string($array) {
		ob_start();
		var_dump($array);
		return ob_get_clean();
	}

    /**
     * @param $string
     * @return (array) $arr
     */
    public function toArray($string) {
        $arr = [
            $string
        ];
        return $arr;
    }
    /**
     * @param $name
     * @return Block[]
     */
    public function getBlocksByName($name){
        $ret = [];
        foreach($this->blocks as $block){
            if($block->getName() === $name) $ret[] = $block;
        }
        return $ret;
    }

    /**
     * @param $x
     * @param $y
     * @param $z
     * @param $world
     * @return Block
     */
    public function getBlock($x, $y, $z, $world){
        if($x instanceof Position) return (isset($this->blocks[$x->getX() . ":" . $x->getY() . ":" . $x->getZ() . ":" . $x->getWorld()->getDisplayName()]) ? $this->blocks[$x->getX() . ":" . $x->getY() . ":" . $x->getZ() . ":" . $x->getWorld()->getDisplayName()] : false);
        else return (isset($this->blocks[$x . ":" . $y . ":" . $z . ":" . $world]) ? $this->blocks[$x . ":" . $y . ":" . $z . ":" . $world] : false);

    }

    /**
     *
     */
    public function parseBlockData(){
    $this->blocks = [];
	$blocks = $this->blocks;
	if($this->blocksCfg->get("blocks", true) !== null) {
        $count = 0;
        foreach($this->blocksCfg->get("blocks") as $i => $block){
            if(Server::getInstance()->getWorldManager()->isWorldLoaded($block["world"])){
                $pos = new Position($block["x"], $block["y"], $block["z"], Server::getInstance()->getWorldManager()->getWorldByName($block["world"]));
                if(isset($block["name"])) $this->blocks[$pos->__toString()] = new Block($pos, $block["commands"], $this, $block["name"]);
                else $this->blocks[$block["x"] . ":" . $block["y"] . ":" . $block["z"] . ":" . $block["world"]] = new Block($pos, $block["commands"], $this, $i);
                $count++;
            }
            else{
                $this->getLogger()->warning("Could not load block in world " . $block["world"] . " because that world is not loaded.");
            }
        }
        $this->getLogger()->info($this->prefix . TextFormat::GREEN . " All blocks [" . $count . "] is loaded");
      }
    }

    /**
     * @param Block $block
     */
    public function deleteBlock(Block $block){
        $blocks = $this->blocksCfg->get("blocks");
        unset($blocks[$block->id]);
        $this->blocksCfg->set("blocks", $blocks);
        $this->blocksCfg->save();
        $this->parseBlockData();
    }
	
	/**
     * @param Position $p
     * @param $cmd
     * @return Block
     */
    public function addBlock(Position $p, $cmd){
        $block = new Block(new Position($p->getX(), $p->getY(), $p->getZ(), $p->getWorld()), [$cmd], $this, count($this->blocksCfg->get("blocks")));
        $this->saveBlock($block);
        $this->blocksCfg->save();
        return $block;
    }
	
	/**
     * @param Block $block
     */
	public function saveBlock(Block $block){
        $this->blocks[$block->getPosition()->getX() . ":" . $block->getPosition()->getY() . ":" . $block->getPosition()->getZ() . ":" . $block->getPosition()->getWorld()->getDisplayName()] = $block;
        $blocks = $this->blocksCfg->get("blocks");
        $blocks[$block->id] = $block->toArray();
        $this->blocksCfg->set("blocks", $blocks);
        $this->blocksCfg->save();
    }
	
    /**
     *
     */
	public function onDisable() : void{
        $this->getLogger()->info($this->prefix . TextFormat::RED . " Saving blocks...");
        foreach($this->blocks as $block){
            $this->saveBlock($block);
        }
        $this->blocksCfg->save();
    }
}
