<?php

declare(strict_types=1);

namespace XackiGiFF\TapToDoNew;

use pocketmine\utils\Config;

class ConfigUpdater {
	public const CONFIG_VERSION = 1;
	private Config $config;
	private Main $tapToDo;
	private int $version;

	public function __construct(Config $config, Main $tapToDo) {
		$this->config = $config;
		$this->tapToDo = $tapToDo;
		$this->version = $this->config->get("version", 0);
	}

	public function checkConfig() : Config {
		if ($this->version > ConfigUpdater::CONFIG_VERSION) {
			$this->tapToDo->getLogger()->warning("The config loaded is not supported. It may not function correctly. ");
		}
		while ($this->version < ConfigUpdater::CONFIG_VERSION) {
			if ($this->version == 0) {
				$this->tapToDo->getLogger()->info("Updating config from version 0 to 1...");
				$blocks = $this->config->getAll();
				foreach ($blocks as $id => $block) {
					foreach ($block["commands"] as $i => $command) {
						if (!str_contains($command, "%safe") && !str_contains($command, "%op")) {
							$command .= "%pow";
						}
						$block["commands"][$i] = str_replace("%safe", "", $command);
					}
					$blocks[$id] = $block;
				}
				unlink($this->tapToDo->getDataFolder() . "blocks.yml");
				$this->tapToDo->saveResource("blocks.yml");
				$this->config = new Config($this->tapToDo->getDataFolder() . "blocks.yml", Config::YAML);
				$this->config->set("version", 1);
				$this->config->set("blocks", $blocks);
				$this->config->save();
				$this->version = 1;
			}
		}
		return $this->config;
	}
}
