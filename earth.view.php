<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * earth implementation : © Guillaume Benny bennygui@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * earth.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in earth_earth.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */

require_once(APP_BASE_PATH . "view/common/game.view.php");

class view_earth_earth extends game_view
{
  public function getGameName()
  {
    return "earth";
  }

  private function insertPlayerAreaBlock($playerId, $playerInfo)
  {
    $this->page->insert_block(
      "player-area",
      [
        "PLAYER_ID" => $playerId,
        "PLAYER_NAME" => $playerInfo['player_name'],
        "PLAYER_COLOR" => $playerInfo['player_color'],
        "SHOW_ICON_HELP" => self::_('Show Icon Help'),
        "HIDE_ICON_HELP" => self::_('Hide Icon Help'),
        "DISPLAY_EMPTY_DECK" => self::_("The deck is empty: effects that draws won't do anything"),
      ]
    );
  }

  public function build_page($viewArgs)
  {
    $this->tpl['DISPLAY_LAST_ROUND'] = self::_('This is the last round!');
    $this->tpl['DECK'] = self::_('Deck:');
    $this->tpl['DISCARD'] = self::_('Discard:');
    $this->tpl['PROGRESS'] = self::_('Progress');
    $this->tpl['DISPLAY_EMPTY_DECK'] = self::_("The deck is empty: effects that draws won't do anything");

    $currentPlayerId = $this->game->currentPlayerId();
    $playersInfos = $this->game->loadPlayersBasicInfos();
    $this->page->begin_block("earth_earth", "player-area");
    if (array_key_exists($currentPlayerId, $playersInfos)) {
      $this->insertPlayerAreaBlock($currentPlayerId, $playersInfos[$currentPlayerId]);
    }

    $playerIdArray = array_keys($playersInfos);
    usort($playerIdArray, function ($p1, $p2) use (&$playersInfos) {
      return ($playersInfos[$p1]['player_no'] <=> $playersInfos[$p2]['player_no']);
    });

    $currentPlayerIndex = array_search($currentPlayerId, $playerIdArray);
    if ($currentPlayerIndex === false) {
      $currentPlayerIndex = -1;
    }

    // Insert players that are after the current player
    foreach ($playerIdArray as $i => $playerId) {
      if ($i > $currentPlayerIndex) {
        $this->insertPlayerAreaBlock($playerId, $playersInfos[$playerId]);
      }
    }

    // Insert players that are before the current player
    foreach ($playerIdArray as $i => $playerId) {
      if ($i < $currentPlayerIndex) {
        $this->insertPlayerAreaBlock($playerId, $playersInfos[$playerId]);
      }
    }
    /*********** Do not change anything below this line  ************/
  }
}
