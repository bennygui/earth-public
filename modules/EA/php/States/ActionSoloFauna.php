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

namespace EA\State\ActionSoloFauna;

require_once(__DIR__ . '/.././../../BX/php/Action.php');
require_once(__DIR__ . '/.././../../BX/php/MultiActiveState.php');

trait GameStatesTrait
{
    public function stActionSoloFauna()
    {
        \BX\Lock\Locker::lock();

        $this->gamestate->setAllPlayersMultiactive();
        $this->gamestate->initializePrivateStateForAllActivePlayers();
    }

    public function argsSoloFaunaChoose(int $playerId)
    {
        return \BX\MultiActiveState\argsMultiActive($playerId, function ($playerId) {
            $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
            $leafTokenMgr = \BX\Action\ActionRowMgrRegister::getMgr('leaf_token');
            $gaiaTopCard = $cardMgr->getGaiaDiscardTopCard();
            $checkPos = null;
            if ($gaiaTopCard->getCardDef()->getFirstAbility()->hasGaiaFaunaUpper()) {
                $checkPos = [[0, 0], [0, 1]];
            } else {
                $checkPos = [[1, 0], [1, 1]];
            }
            $validPos = [];
            foreach ($checkPos as $pos) {
                $leafId = $leafTokenMgr->getLeafIdFromBoardLocation($pos[0], $pos[1]);
                $leafToken = $leafTokenMgr->getLeafTokenByLeafIdAndPlayerId($leafId, \EA\GAIA_PLAYER_ID);
                if (!$leafToken->isOnFaunaBoard()) {
                    $validPos[] = $pos;
                }
            }
            $ret['faunaPositions'] = $validPos;
            return $this->argsMergeEarthDefault($playerId, $ret);
        });
    }
    
    public function soloFaunaChoose(int $x, int $y)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction('soloFaunaChoose');
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \EA\Actions\Gaia\GaiaFaunaChoose($playerId, $x, $y));
        $this->addNextConfirmEndPhaseOrExit($playerId, $creator);
        $this->addCommonActions($creator);
        $creator->save();
    }
}
