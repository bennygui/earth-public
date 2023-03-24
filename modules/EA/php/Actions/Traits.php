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

namespace EA\Actions\Traits;

trait CardQueryTrait
{
    private function cardFromHand(int $cardId)
    {
        $cardMgr = self::getMgr('card');
        $card = $cardMgr->getCardById($cardId);
        if ($card === null) {
            throw new \BgaSystemException("BUG! cardId {$cardId} does not exist");
        }
        if (!$card->isInPlayerHand($this->playerId)) {
            throw new \BgaSystemException("BUG! cardId {$cardId} is not in player {$this->playerId} hand");
        }
        return $card;
    }
}