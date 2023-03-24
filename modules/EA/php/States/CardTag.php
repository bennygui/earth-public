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

namespace EA\State\CardTag;

require_once(__DIR__ . '/.././../../BX/php/Action.php');

trait GameStatesTrait
{
    public function tagHandCard(int $cardId, int $cardTag)
    {
        \BX\Lock\Locker::lock();
        $playerId = $this->getCurrentPlayerId();
        if (array_search($playerId, $this->getPlayerIdArray()) === false) {
            throw new \BgaSystemException('BUG! Only players can tag cards');
        }
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);
        
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        if (!array_key_exists($cardId, $cardMgr->getPlayerHandCards($playerId))) {
            throw new \BgaSystemException('BUG! Only cards from hand can be tagged');
        }
        $cardTagMgr = \BX\Action\ActionRowMgrRegister::getMgr('card_tag');
        $cardTagMgr->updateCardTag($playerId, $cardId, $cardTag);

        $this->notifyPlayer(
            $playerId,
            NTF_UPDATE_CARD_TAG,
            '',
            [
                'cardTags' => $cardTagMgr->getPlayerCardTags($playerId),
            ]
        );
    }
}
