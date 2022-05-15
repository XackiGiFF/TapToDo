<?php

declare(strict_types=1);

namespace XackiGiFF\TapToDoNew;

#use JetBrains\PhpStorm\ArrayShape;
use pocketmine\player\Player;
use pocketmine\world\Position;

class Block {
	public mixed $id;
	/** @var Command[] */
	private array $commands;
	private Position $position;
	private string|bool $name;
	private Main $plugin;

	public function __construct(Position $position, array $commands, Main $main, mixed $id, $name = FALSE) {
		$this->position = $position;
		$this->commands = [];
		$this->plugin = $main;
		$this->name = $name;
		$this->id = $id;

		$this->addCommands($commands);
	}

	public function addCommands($cmds) : void {
		if (!is_array($cmds)) {
			$cmds = [$cmds];
		}
		$this->commands = array_values($this->commands);
		foreach ($cmds as $c) {
			$this->commands[] = new Command($c, $this->plugin);
		}
		$this->plugin->saveBlock($this);
	}

	public function addCommand($cmd) : void {
		$this->addCommands([$cmd]);
	}

	public function deleteCommand($cmd) : bool {
		$ret = FALSE;
		$this->commands = array_values($this->commands);
		for ($i = count($this->commands) - 1; $i >= 0; $i--) {
			if ($this->commands[$i]->getOriginalCommand() === $cmd || $this->commands[$i]->getCompiledCommand() === $cmd) {
				unset($this->commands[$i]);
				$ret = TRUE;
			}
		}
		if ($ret) {
			$this->plugin->saveBlock($this);
		}
		return $ret;
	}

	public function executeCommands(Player $player) : void {
		foreach ($this->commands as $command) {
			$command->execute($player);
		}
	}

	public function getCommands() : array {
		$out = [];
		foreach ($this->commands as $command) $out[] = $command->getOriginalCommand();
		return $out;
	}

	public function getName() {
		return $this->name;
	}

	public function setName($name) : void {
		$this->name = $name;
	}

	public function getPosition() : Position {
		return $this->position;
	}

	#[ArrayShape(['x' => "float|int", 'y' => "float|int", 'z' => "float|int", 'world' => "string", 'commands' => "array", "name" => "bool|mixed|string"])]
	public function toArray() : array {
		$arr = [
			'x' => $this->getPosition()->getX(),
			'y' => $this->getPosition()->getY(),
			'z' => $this->getPosition()->getZ(),
			'world' => $this->getPosition()->getWorld()->getDisplayName(),
			'commands' => $this->getCommands()
		];
		if ($this->name !== FALSE) $arr["name"] = $this->name;
		return $arr;
	}
}
