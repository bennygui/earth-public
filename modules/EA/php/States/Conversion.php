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

namespace EA\State\Conversion;

require_once(__DIR__ . '/.././../../BX/php/Action.php');
require_once(__DIR__ . '/.././../../BX/php/MultiActiveState.php');
require_once(__DIR__ . '/../Actions/Conversion.php');

trait GameStatesTrait
{
    public function convertPlay()
    {
        \BX\Lock\Locker::lock();
        $playerId = $this->getCurrentPlayerId();
        if ($this->gamestate->isPlayerActive($playerId)) {
            $this->checkAction('convertPlay');
        } else {
            $this->gamestate->checkPossibleAction('convertPlay');
        }
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        if (!$cardMgr->playerCanPlayConversion($playerId)) {
            throw new \BgaUserException($this->translate(clienttranslate('You do not have enough sprouts to convert')));
        }

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \EA\Actions\Conversion\KeepReturnFromConversionState($playerId));
        $creator->add(new \BX\MultiActiveState\JumpPrivateStateActionCommand($playerId, STATE_CONVERT_SELECT_PAYMENT_ID));
        $creator->save();
    }

    public function argsConvertPayment(int $playerId)
    {
        return \BX\MultiActiveState\argsMultiActive($playerId, function ($playerId) {
            $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
            return $this->argsMergeEarthBasic(
                [
                    'sproutCount' => $cardMgr->getPlayerSproutCount($playerId),
                    'sproutCards' => $cardMgr->getPlayerTableauCountSprout($playerId),
                    'sproutIcon' => clienttranslate('sprouts'),
                    'soilIcon' => clienttranslate('soil'),
                ]
            );
        });
    }

    public function convertSelectPayment(array $payedSproutList)
    {
        \BX\Lock\Locker::lock();
        $playerId = $this->getCurrentPlayerId();
        $this->checkAction('convertSelectPayment');
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $convert = new \EA\Actions\Conversion\ConvertSprout($playerId, $payedSproutList);
        $creator->add($convert);
        $creator->add(new \EA\Actions\Ability\GainSoil($playerId, $convert->getNbGainedSoil(), null));
        $this->addCommonActions($creator);
        $this->addCommonActions($creator, true);
        $creator->add(new \BX\MultiActiveState\JumpPrivateStateActionCommand($playerId, $playerStateMgr->getPlayerReturnFromConversionStateId($playerId)));
        $creator->save();
    }
}
