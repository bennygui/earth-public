<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * earth implementation : © Guillaume Benny bennygui@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 * 
 * earth.action.php
 *
 * earth main action entry point
 *
 */

require_once("modules/EA/php/Globals.php");

class action_earth extends APP_GameAction
{
  // Constructor: please do not modify
  public function __default()
  {
    if (self::isArg('notifwindow')) {
      $this->view = "common_notifwindow";
      $this->viewArgs['table'] = $this->getArg("table", AT_posint, true);
    } else {
      $this->view = "earth_earth";
      self::trace("Complete reinitialization of board game");
    }
  }

  public function undoLast()
  {
    self::setAjaxMode();
    $this->game->undoLast();
    self::ajaxResponse();
  }

  public function undoAll()
  {
    self::setAjaxMode();
    $this->game->undoAll();
    self::ajaxResponse();
  }

  public function playerSetupChoose()
  {
    self::setAjaxMode();

    $cardIds = explode(',', $this->getArg("cardIds", AT_numberlist, true));
    $this->game->playerSetupChoose($cardIds);

    self::ajaxResponse();
  }

  public function playerSetupCompost()
  {
    self::setAjaxMode();

    $cardIds = explode(',', $this->getArg("cardIds", AT_numberlist, true));
    $this->game->playerSetupCompost($cardIds);

    self::ajaxResponse();
  }

  public function mainActionChoose()
  {
    self::setAjaxMode();

    $mainActionId = $this->getArg("mainActionId", AT_int, true);
    $this->game->mainActionChoose($mainActionId);

    self::ajaxResponse();
  }

  public function compostActionChooseGainSoil()
  {
    self::setAjaxMode();

    $this->game->compostActionChooseGainSoil();

    self::ajaxResponse();
  }

  public function compostActionChooseCompostFromDeck()
  {
    self::setAjaxMode();

    $this->game->compostActionChooseCompostFromDeck();

    self::ajaxResponse();
  }

  public function plantActionPlanCard()
  {
    self::setAjaxMode();

    $cardId = $this->getArg("cardId", AT_posint, true);
    $posX = $this->getArg("posX", AT_int, true);
    $posY = $this->getArg("posY", AT_int, true);
    $this->game->plantActionPlanCard($cardId, $posX, $posY);

    self::ajaxResponse();
  }

  public function planActionKeepOneDrawnCard()
  {
    self::setAjaxMode();

    $cardId = $this->getArg("cardId", AT_posint, true);
    $this->game->planActionKeepOneDrawnCard($cardId);

    self::ajaxResponse();
  }

  public function plantActionSkipPlanting()
  {
    self::setAjaxMode();

    $this->game->plantActionSkipPlanting();

    self::ajaxResponse();
  }

  public function plantActionGain()
  {
    self::setAjaxMode();

    $placedSproutList = null;
    $placedGrowthList = null;
    $selectedCompostFromHandCardIds = null;
    $selectedHandChoosingCardIds = null;
    $this->parseGainArgs($placedSproutList, $placedGrowthList, $selectedCompostFromHandCardIds, $selectedHandChoosingCardIds);
    if (count($selectedHandChoosingCardIds) > 0) {
      throw new \BgaSystemException('BUG! Cannot select hand choosing in this action');
    }

    $this->game->plantActionGain($placedSproutList, $placedGrowthList, $selectedCompostFromHandCardIds);

    self::ajaxResponse();
  }

  public function plantActionPlanCardWithPayment()
  {
    self::setAjaxMode();

    $payedSproutList = null;
    $payedGrowthList = null;
    $payedCompostFromHandCardIds = null;
    $this->parsePayArgs($payedSproutList, $payedGrowthList, $payedCompostFromHandCardIds);

    $this->game->plantActionPlanCardWithPayment($payedSproutList, $payedGrowthList, $payedCompostFromHandCardIds);

    self::ajaxResponse();
  }

  public function waterActionChooseGainSoil()
  {
    self::setAjaxMode();

    $this->game->waterActionChooseGainSoil();

    self::ajaxResponse();
  }

  public function waterActionChoosePlaceSprout()
  {
    self::setAjaxMode();

    $this->game->waterActionChoosePlaceSprout();

    self::ajaxResponse();
  }

  public function waterActionPlaceSprout()
  {
    self::setAjaxMode();

    $placedSproutList = null;
    $placedGrowthList = null;
    $selectedCompostFromHandCardIds = null;
    $selectedHandChoosingCardIds = null;
    $this->parseGainArgs($placedSproutList, $placedGrowthList, $selectedCompostFromHandCardIds, $selectedHandChoosingCardIds);
    if (count($placedGrowthList) > 0 || count($selectedCompostFromHandCardIds) > 0 || count($selectedHandChoosingCardIds) > 0) {
      throw new \BgaSystemException('BUG! Can only place sprout in this action');
    }

    $this->game->waterActionPlaceSprout($placedSproutList);

    self::ajaxResponse();
  }

  public function growActionChooseDrawCard()
  {
    self::setAjaxMode();

    $this->game->growActionChooseDrawCard();

    self::ajaxResponse();
  }

  public function growActionChoosePlaceGrowth()
  {
    self::setAjaxMode();

    $this->game->growActionChoosePlaceGrowth();

    self::ajaxResponse();
  }

  public function growActionPlaceGrowth()
  {
    self::setAjaxMode();

    $placedSproutList = null;
    $placedGrowthList = null;
    $selectedCompostFromHandCardIds = null;
    $selectedHandChoosingCardIds = null;
    $this->parseGainArgs($placedSproutList, $placedGrowthList, $selectedCompostFromHandCardIds, $selectedHandChoosingCardIds);
    if (count($placedSproutList) > 0 || count($selectedCompostFromHandCardIds) > 0 || count($selectedHandChoosingCardIds) > 0) {
      throw new \BgaSystemException('BUG! Can only place growth in this action');
    }

    $this->game->growActionPlaceGrowth($placedGrowthList);

    self::ajaxResponse();
  }

  public function activationChooseActivationDirection()
  {
    self::setAjaxMode();

    $activationDirection = $this->getArg("activationDirection", AT_posint, true);
    $this->game->activationChooseActivationDirection($activationDirection);

    self::ajaxResponse();
  }

  public function activationSkipCard()
  {
    self::setAjaxMode();

    $this->game->activationSkipCard();

    self::ajaxResponse();
  }

  public function activationActivateCard()
  {
    self::setAjaxMode();

    $this->game->activationActivateCard();

    self::ajaxResponse();
  }

  public function activationGain()
  {
    self::setAjaxMode();

    $placedSproutList = null;
    $placedGrowthList = null;
    $selectedCompostFromHandCardIds = null;
    $selectedHandChoosingCardIds = null;
    $this->parseGainArgs($placedSproutList, $placedGrowthList, $selectedCompostFromHandCardIds, $selectedHandChoosingCardIds);
    if (count($selectedHandChoosingCardIds) > 0) {
      throw new \BgaSystemException('BUG! Cannot select hand choosing in this action');
    }

    $this->game->activationGain($placedSproutList, $placedGrowthList, $selectedCompostFromHandCardIds);

    self::ajaxResponse();
  }

  public function activationPay()
  {
    self::setAjaxMode();

    $payedSproutList = null;
    $payedGrowthList = null;
    $payedCompostFromHandCardIds = null;
    $this->parsePayArgs($payedSproutList, $payedGrowthList, $payedCompostFromHandCardIds);

    $this->game->activationPay($payedSproutList, $payedGrowthList, $payedCompostFromHandCardIds);

    self::ajaxResponse();
  }

  public function activationSelectCardToCopy()
  {
    self::setAjaxMode();

    $cardId = $this->getArg("cardId", AT_posint, true);

    $this->game->activationSelectCardToCopy($cardId);

    self::ajaxResponse();
  }

  public function eventPlay()
  {
    self::setAjaxMode();

    $this->game->eventPlay();

    self::ajaxResponse();
  }

  public function eventChooseCard()
  {
    self::setAjaxMode();

    $cardId = $this->getArg("cardId", AT_posint, true);

    $this->game->eventChooseCard($cardId);

    self::ajaxResponse();
  }

  public function eventGain()
  {
    self::setAjaxMode();

    $placedSproutList = null;
    $placedGrowthList = null;
    $selectedCompostFromHandCardIds = null;
    $selectedHandChoosingCardIds = null;
    $this->parseGainArgs($placedSproutList, $placedGrowthList, $selectedCompostFromHandCardIds, $selectedHandChoosingCardIds);

    $this->game->eventGain($placedSproutList, $placedGrowthList, $selectedCompostFromHandCardIds, $selectedHandChoosingCardIds);

    self::ajaxResponse();
  }

  public function eventPay()
  {
    self::setAjaxMode();

    $payedSproutList = null;
    $payedGrowthList = null;
    $payedCompostFromHandCardIds = null;
    $this->parsePayArgs($payedSproutList, $payedGrowthList, $payedCompostFromHandCardIds);

    $this->game->eventPay($payedSproutList, $payedGrowthList, $payedCompostFromHandCardIds);

    self::ajaxResponse();
  }

  public function convertPlay()
  {
    self::setAjaxMode();

    $this->game->convertPlay();

    self::ajaxResponse();
  }

  public function convertSelectPayment()
  {
    self::setAjaxMode();

    $payedSproutList = null;
    $payedGrowthList = null;
    $payedCompostFromHandCardIds = null;
    $this->parsePayArgs($payedSproutList, $payedGrowthList, $payedCompostFromHandCardIds);
    if (count($payedGrowthList) > 0 || count($payedCompostFromHandCardIds) > 0) {
      throw new \BgaSystemException('BUG! Can only convert sprouts');
    }

    $this->game->convertSelectPayment($payedSproutList);

    self::ajaxResponse();
  }

  public function confirmEndPhase()
  {
    self::setAjaxMode();

    $this->game->confirmEndPhase();

    self::ajaxResponse();
  }

  public function confirmEndGame()
  {
    self::setAjaxMode();

    $this->game->confirmEndGame();

    self::ajaxResponse();
  }

  public function tagHandCard()
  {
    self::setAjaxMode();

    $cardId = $this->getArg("cardId", AT_posint, true);
    $cardTag = $this->getArg("cardTag", AT_posint, true);
    $this->game->tagHandCard($cardId, $cardTag);

    self::ajaxResponse();
  }

  public function soloFaunaChoose()
  {
    self::setAjaxMode();

    $x = $this->getArg("x", AT_posint, true);
    $y = $this->getArg("y", AT_posint, true);
    $this->game->soloFaunaChoose($x, $y);

    self::ajaxResponse();
  }

  public function seeFaunaObjective()
  {
    self::setAjaxMode();

    $this->game->seeFaunaObjective();

    self::ajaxResponse();
  }

  private function parseGainArgs(?array &$placedSproutList, ?array &$placedGrowthList, ?array &$selectedCompostFromHandCardIds, ?array &$selectedHandChoosingCardIds)
  {
    $placedSproutList = $this->getNumberListArgs('placedSproutList');
    $placedGrowthList = $this->getNumberListArgs('placedGrowthList');
    $selectedCompostFromHandCardIds = $this->getNumberListArgs('selectedCompostFromHandCardIds');
    $selectedHandChoosingCardIds = $this->getNumberListArgs('selectedHandChoosingCardIds');
  }

  private function parsePayArgs(?array &$payedSproutList, ?array &$payedGrowthList, ?array &$payedCompostFromHandCardIds)
  {
    $payedSproutList = $this->getNumberListArgs('payedSproutList');
    $payedGrowthList = $this->getNumberListArgs('payedGrowthList');
    $payedCompostFromHandCardIds = $this->getNumberListArgs('payedCompostFromHandCardIds');
  }

  private function getNumberListArgs(string $argName)
  {
    $argStr = $this->getArg($argName, AT_numberlist, true);
    if (strlen($argStr) > 0) {
      return explode(',', $argStr);
    } else {
      return [];
    }
  }
}
