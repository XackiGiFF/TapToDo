<?php

declare(strict_types=1);

namespace XackiGiFF\TapToDoNew;

use pocketmine\console\ConsoleCommandSender;
use pocketmine\player\Player;

class Command {
	public const AS_CONSOLE_TYPE = 0;
	public const AS_PLAYER_TYPE = 1;
	public const AS_OP_TYPE = 2;
	private mixed $originalCommand;
	private mixed $compiledCommand;
	private ?int $executionMode = NULL;
	private Main $plugin;

	public function __construct(mixed $command, Main $plugin) {
		$this->originalCommand = $command;
		$this->plugin = $plugin;
		$this->compile();
	}

	public function compile() : void {
		if ($this->executionMode == NULL) {
			$this->executionMode = Command::AS_PLAYER_TYPE;
			$this->compiledCommand = $this->originalCommand;
			$this->compiledCommand = str_replace("%safe", "", $this->compiledCommand);
			if (str_contains($this->compiledCommand, "%pow") && ($this->compiledCommand = str_replace("%pow", "", $this->compiledCommand))) {
				$this->executionMode = Command::AS_CONSOLE_TYPE;
			} elseif (str_contains($this->compiledCommand, "%op") && ($this->compiledCommand = str_replace("%op", "", $this->compiledCommand))) {
				$this->executionMode = Command::AS_OP_TYPE;
			}
		}
	}

	public function execute(Player $player) : void {
		$type = $this->executionMode;
		$pos = $player->getPosition();

		$command = str_replace(["%p", "%x", "%y", "%z", "%l", "%ip", "%n"], [
			$player->getName(),
			(string) $pos->getFloorX(),
			(string) $pos->getFloorY(),
			(string) $pos->getFloorZ(),
			$pos->getWorld()->getDisplayName(),
			$player->getNetworkSession()->getIP(),
			$player->getDisplayName()
		], $this->compiledCommand);

		if ($type === Command::AS_OP_TYPE && $this->plugin->getServer()->isOp($player->getName())) $type = Command::AS_PLAYER_TYPE;

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

	public function getOriginalCommand() : mixed {
		return $this->originalCommand;
	}

	public function getCompiledCommand() : mixed {
		return $this->compiledCommand;
	}
}
