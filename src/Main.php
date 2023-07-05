<?php

declare(strict_types=1);

namespace XackiGiFF\TapToDoNew;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use pocketmine\world\World;

class Main extends PluginBase implements CommandExecutor, Listener {
	public string $prefix;
	public array $sessions;
	/** @var Block[] */
	public array $blocks;
	private Config $blocksCfg;

	public function onEnable() : void {
		$this->prefix = TextFormat::BLUE . '[' . TextFormat::AQUA . 'TapToDo' . TextFormat::BLUE . "] ";
		$this->sessions = [];
		$this->blocks = [];
		$this->saveResource('blocks.yml');
		$this->blocksCfg = (new ConfigUpdater(new Config($this->getDataFolder() . 'blocks.yml', Config::YAML, array()), $this))->checkConfig();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		if (!$this->blocksCfg->exists('blocks')) {
			$this->blocksCfg->set('blocks', []);
		}
		$this->parseBlockData();
	}

	public function sessionManager(&$event){
		$p = $event->getPlayer();
		$pos = $event->getBlock()->getPosition();

		if (isset($this->sessions[$p->getName()])) {
			$args = $this->sessions[$p->getName()];
			switch ($args[0]) {
				case 'a':
				case 'add':
						array_shift($args);
						if (($b = $this->getBlock($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $pos->getWorld())) instanceof Block) {
							$b->addCommand(implode(' ', $args));
							$p->sendMessage($this->prefix . TextFormat::GREEN . 'Command was added to list.');
						} else {
							$this->addBlock($pos, implode(" ", $args));
							$p->sendMessage($this->prefix . TextFormat::GREEN . 'The first command was added.');
						}
					unset($this->sessions[$p->getName()]);
					break;
				case 'd':
				case 'del':
					if (($b = $this->getBlock($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $pos->getWorld())) instanceof Block) {
						$cmds = $b->getCommands();
						$cmd = end($cmds);
						$cmd = $this->toArray($cmd);
						if (($b->deleteCommand(implode(" ", $cmd))) !== false) {
							$p->sendMessage($this->prefix . TextFormat::GREEN . 'Command removed. Try again for delete macros block info');
						} else {
							$this->deleteBlock($b);
							$p->sendMessage($this->prefix . TextFormat::RED . 'Couldn`t find command. Delete block');
						}
					} else {
						$p->sendMessage($this->prefix . TextFormat::RED . 'Block does not exist!');
					}
					unset($this->sessions[$p->getName()]);
					break;
				case 'da':
				case 'delall':
					array_shift($args);
					if (($b = $this->getBlock($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $pos->getWorld())) instanceof Block) {
						$this->deleteBlock($b);
						$p->sendMessage($this->prefix . TextFormat::GREEN . 'All marco commands have been successfully deleted.');
					} else {
						$p->sendMessage($this->prefix . TextFormat::RED . 'No macro commands!');
					}
					unset($this->sessions[$p->getName()]);
					break;
				case 'l':
				case 'ls':
				case 'list':
					if (($b = $this->getBlock($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $pos->getWorld())) instanceof Block) {
						$p->sendMessage($this->prefix . TextFormat::YELLOW . 'Macros:');
						foreach ($b->getCommands() as $cmd) {
							$p->sendMessage(TextFormat::GREEN . '- ' . TextFormat::WHITE . $cmd);
						}
					} else {
						$p->sendMessage($this->prefix . TextFormat::RED . 'Couldn`t find command on this block!');
					}
					unset($this->sessions[$p->getName()]);
					break;
			}
			return true;
		}
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
		if (!isset($args[0])) {
			if ($sender->hasPermission('taptodo.command')) {
				$sender->sendMessage($this->prefix . TextFormat::YELLOW . "Help:\n"
					. "§d × §a/t add §d- §fAdd a macros\n"
					. "§d × §a/t del §d- §fDelete a last macros\n"
					. "§d × §a/t delall §d- §fDelete all macros\n"
					. "§d × §a/t list §d- §fShow macros on block");
				return true;
			}
			$sender->sendMessage($this->prefix . TextFormat::RED . 'You don`t have permissions!');
			return true;
		}

		if (!($sender instanceof Player)) {
			$sender->sendMessage($this->prefix . TextFormat::RED . 'Use only in game!');
			return true;
		}

		switch ($args[0]) {
			case 'a':
			case 'add':
				if (!$sender->hasPermission('taptodo.command.add')) {
					$sender->sendMessage($this->prefix . TextFormat::RED . 'You don`t have permissions!');
					break;
				}

				if (isset($args[1])) {
					$sender->sendMessage($this->prefix . TextFormat::YELLOW . 'Tap a block to add macros.');
					$this->sessions[$sender->getName()] = $args;
					break;
				}

				$sender->sendMessage($this->prefix . TextFormat::RED . 'You need input a command!');
				break;
			case 'd':
			case 'del':
				if (!$sender->hasPermission('taptodo.command.del')) {
					$sender->sendMessage($this->prefix . TextFormat::RED . 'You don`t have permissions!');
					break;
				}

				$sender->sendMessage($this->prefix . TextFormat::YELLOW . 'Tap a block to delete last macros.');
				$this->sessions[$sender->getName()] = $args;
				break;
			case 'da':
			case 'delall':
				if (!$sender->hasPermission('taptodo.command.delall')) {
					$sender->sendMessage($this->prefix . TextFormat::RED . 'You don`t have permissions!');
					break;
				}

				$sender->sendMessage($this->prefix . TextFormat::YELLOW . 'Tap a block to delete all macros.');
				$this->sessions[$sender->getName()] = $args;
				break;
			case 'l':
			case 'ls':
			case 'list':
				if (!$sender->hasPermission('taptodo.command.list')) {
					$sender->sendMessage($this->prefix . TextFormat::RED . 'You don`t have permissions!');
					break;
				}

				$sender->sendMessage($this->prefix . TextFormat::YELLOW . 'Tap a block to show macros.');
				$this->sessions[$sender->getName()] = $args;
				break;
			default:
				if ($sender->hasPermission('taptodo.command')) {
					$sender->sendMessage($this->prefix . TextFormat::YELLOW . "Help:\n"
						. "§d × §a/t add §d- §fAdd a macros\n"
						. "§d × §a/t del §d- §fDelete a last macros\n"
						. "§d × §a/t delall §d- §fDelete all macros\n"
						. "§d × §a/t list §d- §fShow macros on block");
					return true;
				}
				$sender->sendMessage($this->prefix . TextFormat::RED . 'You don`t have permissions!');
				break;
		}
		return true;
	}

	public function onInteract(PlayerInteractEvent $event): void{
		$p = $event->getPlayer();
		$pos = $event->getBlock()->getPosition();
		if($event->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK) return;
		if($this->sessionManager($event)){
			$event->cancel();
			return;
		}
		if( ( $b = $this->getBlock($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $pos->getWorld()) ) instanceof Block){
			$b->executeCommands($event->getPlayer());
			return;
		}
	}

	public function onBlockBreak(BlockBreakEvent $event): void {
		$pos = $event->getBlock()->getPosition();
		if($this->sessionManager($event)){
			$event->cancel();
			return;
		}
		if( ( $b = $this->getBlock($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $pos->getWorld()) ) instanceof Block){
			$b->executeCommands($event->getPlayer());
			$event->cancel();
			return;
		}
	}

	public function toArray($string) : array {
		return [$string];
	}

	public function getBlock(int $x, int $y, int $z, World $world) : Block|bool {
		return $this->blocks[$x . ':' . $y . ':' . $z . ':' . $world->getFolderName()] ?? false;
	}

	public function parseBlockData() {
		$this->blocks = [];
		if ($this->blocksCfg->get('blocks', true) !== NULL) {
			$count = 0;
			foreach ($this->blocksCfg->get('blocks') as $i => $block) {
				if (Server::getInstance()->getWorldManager()->isWorldLoaded($block['world'])) {
					$pos = new Position($block['x'], $block['y'], $block['z'], Server::getInstance()->getWorldManager()->getWorldByName($block['world']));
					if (isset($block['name'])) {
						$this->blocks[$pos->__toString()] = new Block($pos, $block['commands'], $this, $block['name']);
					} else $this->blocks[$block['x'] . ':' . $block['y'] . ':' . $block['z'] . ':' . $block['world']] = new Block($pos, $block['commands'], $this, $i);
					$count++;
				} else {
					$this->getLogger()->warning('Could not load block in world ' . $block['world'] . ' because that world is not loaded.');
				}
			}
		}
	}

	public function deleteBlock(Block $block) {
		$blocks = $this->blocksCfg->get('blocks');
		unset($blocks[$block->id]);
		$this->blocksCfg->set('blocks', $blocks);
		$this->blocksCfg->save();
		$this->parseBlockData();
	}

	public function addBlock(Position $p, $cmd) : Block {
		$block = new Block(new Position($p->getX(), $p->getY(), $p->getZ(), $p->getWorld()), [$cmd], $this, count($this->blocksCfg->get('blocks')));
		$this->saveBlock($block);
		$this->blocksCfg->save();
		return $block;
	}

	public function saveBlock(Block $block) {
		$this->blocks[$block->getPosition()->getX() . ':' . $block->getPosition()->getY() . ':' . $block->getPosition()->getZ() . ':' . $block->getPosition()->getWorld()->getDisplayName()] = $block;
		$blocks = $this->blocksCfg->get('blocks');
		$blocks[$block->id] = $block->toArray();
		$this->blocksCfg->set('blocks', $blocks);
		$this->blocksCfg->save();
	}

	public function onDisable() : void {
		foreach ($this->blocks as $block) {
			$this->saveBlock($block);
		}
		$this->blocksCfg->save();
	}
}
