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

namespace EA\Actions\GameEnd;

require_once(__DIR__ . '/.././../../BX/php/Action.php');

abstract class LastRoundActionBase extends \BX\Action\BaseActionCommandNoUndo
{
    public function __construct(int $playerId)
    {
        parent::__construct($playerId);
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $gameStateMgr = self::getMgr('game_state');
        if ($gameStateMgr->isLastRound()) {
            return;
        }
        $cardMgr = self::getMgr('card');
        $isLastRound = $cardMgr->isTableauFilledForOneOfAllPlayers();
        if (!$isLastRound) {
            return;
        }
        $this->onLastRound($notifier);
    }

    protected abstract function onLastRound(\BX\Action\BaseActionCommandNotifier $notifier);
}

class MarkLastRound extends LastRoundActionBase
{
    public function onLastRound(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $gameStateMgr = self::getMgr('game_state');
        $gameStateMgr->actionActivateLastRound();
        $notifier->notify(
            NTF_LAST_ROUND,
            clienttranslate('A 4x4 tableau is completed, this will be the last round'),
            [
                'isLastRound' => true,
            ]
        );
    }
}

class LeafTokenBonusLastRound extends LastRoundActionBase
{
    public function onLastRound(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $leafTokenMgr = self::getMgr('leaf_token');
        if ($leafTokenMgr->hasFaunaBoardTableauBonus()) {
            return;
        }
        $cardMgr = self::getMgr('card');
        if (!$cardMgr->isTableauFilledForPlayer($this->playerId)) {
            return;
        }
        $leafToken = gameHasExpansionAbundance()
            ? $leafTokenMgr->getLeafTokenCanBeOnTableauBonusByPlayerId($this->playerId)
            : $leafTokenMgr->getTableauBonusLeafTokenForPlayerId($this->playerId);
        if ($leafToken !== null) {
            $leafToken->modifyAction();
            $leafToken->moveToFaunaBoardTableauBonus();
            $notifier->notify(
                NTF_UPDATE_LEAF_TOKEN,
                clienttranslate('${player_name} claims the 4x4 tableau bonus on the fauna board'),
                [
                    'leafToken' => $leafToken->toPlayerUI($this->playerId),
                ]
            );
        }
    }
}
