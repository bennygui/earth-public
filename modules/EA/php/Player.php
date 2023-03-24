<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * earth implementation : © Guillaume Benny bennygui@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 */

namespace EA;

require_once(__DIR__ . '/../../BX/php/Player.php');

const GAIA_PLAYER_ID = 1;

const COLOR_TO_COLOR_NAME = [
    '61b3e3' => 'blue',
    'f5895b' => 'red',
    '71b17f' => 'green',
    'fbff00' => 'yellow',
    'a66eaf' => 'purple',
];

class Player extends \BX\Player\Player
{
    public function jsonSerialize()
    {
        $ret = parent::jsonSerialize();
        $playerColorName = '';
        if (array_key_exists($ret['player_color'], COLOR_TO_COLOR_NAME)) {
            $playerColorName = COLOR_TO_COLOR_NAME[$ret['player_color']];
        }
        $ret['player_color_name'] = $playerColorName;
        return $ret;
    }
}

class PlayerMgr extends \BX\Player\PlayerMgr
{
    public function __construct()
    {
        parent::__construct(\EA\Player::class);
    }

    public function getAllForUI(bool $gameHasEnded)
    {
        $playerArray = \BX\UI\deepCopyToArray($this->getAllRowsByKey());
        if (!$gameHasEnded) {
            foreach ($playerArray as &$player) {
                $player['score'] = null;
            }
        }
        return $playerArray;
    }

    public function getFirstUnusedColorName()
    {
        $usedColors = array_flip(array_map(fn ($p) => $p['player_color_name'], $this->getAllForUI(false)));
        foreach (COLOR_TO_COLOR_NAME as $colorName) {
            if (!array_key_exists($colorName, $usedColors)) {
                return $colorName;
            }
        }
        return null;
    }
}
