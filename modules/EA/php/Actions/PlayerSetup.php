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

namespace EA\Actions\PlayerSetup;

require_once(__DIR__ . '/.././../../BX/php/Action.php');
require_once(__DIR__ . '/../CardDefMgr.php');
require_once('Traits.php');

class Choose extends \BX\Action\BaseActionCommandNoUndo
{
    use \EA\Actions\Traits\CardQueryTrait;

    private $cardIds;
    private $islandCardId;

    public function __construct(int $playerId, array $cardIds)
    {
        parent::__construct($playerId);
        $this->cardIds = $cardIds;
    }

    public function getIslandCardId()
    {
        return $this->islandCardId;
    }

    public function hasCardsToCompost()
    {
        $nb = \EA\CardDefMgr::getByCardId($this->islandCardId)->abilityBlack()->paymentCountCompostFromHand();
        return ($nb > 0);
    }

    public function nbCardsToDraw()
    {
        return \EA\CardDefMgr::getByCardId($this->islandCardId)->abilityBlack()->gainDrawCardFromDeck();
    }

    public function nbSoilToGain()
    {
        return \EA\CardDefMgr::getByCardId($this->islandCardId)->abilityBlack()->gainSoil();
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $nbCardsToSelect = (isGameModeBeginner() ? 2 : 3);
        if (count($this->cardIds) != $nbCardsToSelect) {
            throw new \BgaSystemException('BUG! cardIds size is not ' . $nbCardsToSelect);
        }
        $validCardTypes = [
            \EA\CARD_TYPE_ISLAND => true,
            \EA\CARD_TYPE_CLIMATE => true,
            \EA\CARD_TYPE_ECOSYSTEM => true,
        ];
        if (isGameModeBeginner()) {
            unset($validCardTypes[\EA\CARD_TYPE_ECOSYSTEM]);
        }
        foreach ($this->cardIds as $cardId) {
            $card = $this->cardFromHand($cardId);
            $cardType = $card->getCardDef()->type;
            if (!array_key_exists($cardType, $validCardTypes)) {
                throw new \BgaSystemException("BUG! cardId {$cardId} has an invalid type");
            }
            unset($validCardTypes[$cardType]);
            if ($cardType == \EA\CARD_TYPE_ISLAND) {
                $this->islandCardId = $cardId;
            }
            $card->modifyAction();
            $card->moveToPlayerBoard($this->playerId);
            $notifier->notify(
                NTF_UPDATE_CARDS,
                clienttranslate('${player_name} chooses a starting card: ${cardName} ${cardImage}'),
                [
                    'cards' => [$card],
                    'cardName' => $card->getCardDef()->name,
                    'cardImage' => $card->cardId,
                    'i18n' => ['cardName'],
                ]
            );
        }
        $cardMgr = self::getMgr('card');
        $discardCards = [];
        foreach ($cardMgr->getPlayerHandCards($this->playerId) as $card) {
            $card->modifyAction();
            $card->moveToBox();
            $discardCards[] = $card;
        }
        $notifier->notifyPrivateNoMessage(NTF_UPDATE_CARDS, ['cards' => $discardCards]);
    }
}
