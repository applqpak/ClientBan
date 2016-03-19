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

    private $bans = array();

    public function dataPath()
    {

      return $this->getDataFolder();

    }

    public function onEnable()
    {

      $this->getServer()->getPluginManager()->registerEvents($this, $this);

      @mkdir($this->dataPath());

      $this->cfg = new Config($this->dataPath() . "banned-users.txt", Config::ENUM, array("banned_uuids" => array()));

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

            $player_uuid = $player->getClientId();

            if(in_array($player_uuid, $this->bans))
            {

              $sender->sendMessage(TF::RED . "Player " . $player_name . " is already banned.");

              return true;

            }
            else
            {

              unset($args[0]);

              $reason = implode(" ", $args);

              $this->bans[$player_name] = $player_uuid;

              $b = "";

              foreach($this->bans as $key => $value)
              {

                $b .= $key . " => " . $value;

              }

              $this->cfg->set("banned_uuids", $b);

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

          $sender->sendMessage(TF::RED . "Error: not enough args. Usage: /clientpardon <player>");

          return true;

        }
        else
        {

          $name = $args[0];

          $banned_uuids = $this->cfg->get("banned_uuids");

          if(!(in_array($name, $this->bans)))
          {

            $sender->sendMessage(TF::RED . $name . " is not banned.");

            return true;

          }
          else
          {

            unset($this->bans[$name]);

            $b = "";

            foreach($this->bans as $key => $value)
            {

              $b .= $key . " => " . $value;

            }

            $this->cfg->set("banned_uuids", $b);

            $this->cfg->save();

            $sender->sendMessage(TF::GREEN . "Successfully pardoned " . $name . ".");

            return true;

          }

        }

      }

    }

    public function onPreLogin(PlayerPreLoginEvent $event)
    {

      $player = $event->getPlayer();

      $player_name = $player->getName();

      $player_uuid = $player->getClientId();

      $banned_uuids = $this->cfg->get("banned_uuids");

      if(in_array($player_uuid, $this->bans))
      {

        $player->close("", "You are still Client Banned.");

      }

    }

  }

?>
