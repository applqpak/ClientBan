<?php

  namespace ClientBan;

  use pocketmine\plugin\PluginBase;
  use pocketmine\event\Listener;
  use pocketmine\event\player\PlayerPreLoginEvent;
  use pocketmine\command\Command;
  use pocketmine\command\CommandSender;
  use pocketmine\utils\Config;
  use pocketmine\utils\TextFormat as TF;
  use pocketmine\Player;

  class Main extends PluginBase implements Listener
  {

    public function dataPath()
    {

      return $this->getDataFolder();

    }

    public function onEnable()
    {

      $this->getServer()->getPluginManager()->registerEvents($this, $this);

      @mkdir($this->dataPath());

      $this->cfg = new Config($this->dataPath() . "banned-users.yml", Config::YAML, array("banned_uuids" => array()));

    }

    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args)
    {

      if(strtolower($cmd->getName()) === "clientban")
      {

        if(!(isset($args[0])))
        {

          $sender->sendMessage(TF::RED . "Error: not enough args. Usage: /clientban <player> < reason >");

          return true;

        }
        else
        {

          $name = $args[0];

          $player = $this->getServer()->getPlayer($name);

          if($player === null)
          {

            $sender->sendMessage(TF::RED . "Player " . $name . " could not be found.");

            return true;

          }
          else
          {

            $player_name = $player->getName();

            $banned_uuids = $this->cfg->get("banned_uuids");

            $player_uuid = $player->getClientSecret();

            if(in_array($player_uuid, $banned_uuids))
            {

              $sender->sendMessage(TF::RED . "Player " . $player_name . " is already banned.");

              return true;

            }
            else
            {

              unset($args[0]);

              $reason = implode(" ", $args);

              array_push($banned_uuids, $player_uuid);

              $this->cfg->set("banned_uuids", $banned_uuids);

              $this->cfg->save();

              $player->close("", $reason);

              $sender->sendMessage(TF::GREEN . "Successfully banned " . $player_uuid . " belonging to " . $player_name . ".");

              return true;

            }

          }

        }

      }
      else if(strtolower($cmd->getName()) === "clientpardon")
      {

        if(!(isset($args[0])))
        {

          $sender->sendMessage(TF::RED . "Error: not enough args. Usage: /clientpardon <UUID>");

          return true;

        }
        else
        {

          $uuid = $args[0];

          $banned_uuids = $this->cfg->get("banned_uuids");

          if(!(in_array($uuid, $banned_uuids)))
          {

            $sender->sendMessage(TF::RED . $uuid . " is not banned.");

            return true;

          }
          else
          {

            $banned_uuids = array_diff($banned_uuids, array($uuid));

            $this->cfg->set("banned_uuids", $banned_uuids);

            $sender->sendMessage(TF::GREEN . "Successfully pardoned " . $uuid . ".");

            return true;

          }

        }

      }

    }

    public function onPreLogin(PlayerPreLoginEvent $event)
    {

      $player = $event->getPlayer();

      $player_uuid = $player->getClientSecret();

      $banned_uuids = $this->cfg->get("banned_uuids");

      if(in_array($player_uuid, $banned_uuids))
      {

        $player->close("", "You are still Client Banned.");

      }

    }

  }

?>
