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

namespace EA\Actions\Conversion;

require_once(__DIR__ . '/.././../../BX/php/Action.php');

class KeepReturnFromConversionState extends \BX\Action\BaseActionCommand
{
    protected $privateStateIdInit;
    protected $privateStateId;

    public function __construct(int $playerId)
    {
        parent::__construct($playerId);
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if (!$this->privateStateIdInit) {
            if ($notifier->getBGAGameState()->isPlayerActive($this->playerId)) {
                $player = self::getMgr('player')->getByPlayerId($this->playerId);
                $this->privateStateId = $player->playerState;
            } else {
                $this->privateStateId = null;
            }
            $this->privateStateIdInit = true;
        }

        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);
        $ps->modifyAction();
        $ps->returnFromConversionStateId = $this->privateStateId;
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
    }
}

class ConvertSprout extends \BX\Action\BaseActionCommand
{
    private $payedSproutList;
    private $nbGainedSoil;
    private $undoTableau;

    public function __construct(int $playerId, array $payedSproutList)
    {
        parent::__construct($playerId);
        $this->nbGainedSoil = 0;
        $this->payedSproutList = $payedSproutList;
        if (count($payedSproutList) % 2 != 0) {
            throw new \BgaSystemException('BUG! payedSproutList is not even');
        }
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $cardMgr = self::getMgr('card');
        $this->undoTableau = \BX\META\deepClone($cardMgr->getPlayerTableauCards($this->playerId, $this->playerId));

        $nbPayedSprout = 0;
        $payedSprout = [];
        for ($i = 0; $i < count($this->payedSproutList); $i += 2) {
            $payedSprout[$this->payedSproutList[$i]] = $this->payedSproutList[$i + 1];
            $nbPayedSprout += $this->payedSproutList[$i + 1];
        }
        if ($nbPayedSprout <= 0 || ($nbPayedSprout % 3) != 0) {
            throw new \BgaUserException($notifier->_('You must select a multiple of 3 sprouts (3, 6, 9, ...)'));
        }
        $this->nbGainedSoil = 2 * floor($nbPayedSprout / 3);
        foreach ($cardMgr->getPlayerTableauCountSprout($this->playerId) as $count) {
            if (!\array_key_exists($count->cardId, $payedSprout)) {
                continue;
            }
            if ($payedSprout[$count->cardId] < 0) {
                throw new \BgaSystemException('BUG! payedSprout is negative');
            }
            if ($count->count - $payedSprout[$count->cardId] < 0) {
                throw new \BgaSystemException("BUG! Payed too many sprouts on cardId {$count->cardId}");
            }
            $card = $cardMgr->getCardById($count->cardId);
            $card->modifyAction();
            $card->removeSprout($payedSprout[$count->cardId]);
            unset($payedSprout[$count->cardId]);
        }
        if (count($payedSprout) > 0) {
            throw new \BgaSystemException('BUG! payedSprout on invalid cards');
        }
        self::getMgr('player_state')->incStatNbSproutsConverted($this->playerId, $nbPayedSprout);
        self::getMgr('player_state')->incStatNbSoilGained($this->playerId, $this->nbGainedSoil);
        $notifier->notify(
            NTF_UPDATE_PLAYER_TABLEAU,
            clienttranslate('${player_name} converts ${nbPayedSprout} ${sproutIcon} to ${nbGainedSoil} ${soilIcon}'),
            [
                'nbPayedSprout' => $nbPayedSprout,
                'sproutIcon' => clienttranslate('sprout(s)'),
                'soilIcon' => clienttranslate('soil'),
                'nbGainedSoil' => $this->nbGainedSoil,
                'tableauCards' => \EA\cardsToCompactUI($cardMgr->getPlayerTableauCards($this->playerId, null)),
            ]
        );
        $notifier->notifyPrivateNoMessage(
            NTF_UPDATE_PLAYER_TABLEAU,
            [
                'tableauCards' => \EA\cardsToCompactUI($cardMgr->getPlayerTableauCards($this->playerId, $this->playerId)),
            ]
        );
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($this->undoTableau !== null) {
            $notifier->notifyNoMessage(NTF_UPDATE_PLAYER_TABLEAU, ['tableauCards' => \EA\cardsToCompactUI($this->undoTableau)]);
        }
    }

    public function getNbGainedSoil()
    {
        return $this->nbGainedSoil;
    }
}

class PaySeed extends \BX\Action\BaseActionCommand
{
    private $nbSeedBefore;

    public function __construct(int $playerId)
    {
        parent::__construct($playerId);
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);
        $this->nbSeedBefore = $ps->seedCount;
        $ps->modifyAction();
        $ps->removeSeed(1);
        $notifier->notify(
            NTF_PLAYER_PAY_SEED,
            clienttranslate('${player_name} pays 1 ${seedIcon}'),
            [
                'totalSeedCount' => $ps->seedCount,
                'seedIcon' => clienttranslate('seed'),
            ]
        );
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $notifier->notifyNoMessage(
            NTF_PLAYER_GAIN_SEED,
            [
                'totalSeedCount' => $this->nbSeedBefore,
            ]
        );
    }
}

class ConvertSproutToSeed extends \BX\Action\BaseActionCommand
{
    private $payedSproutList;
    private $nbGainedSeed;
    private $undoTableau;

    public function __construct(int $playerId, array $payedSproutList)
    {
        parent::__construct($playerId);
        $this->nbGainedSeed = 0;
        $this->payedSproutList = $payedSproutList;
        if (count($payedSproutList) % 2 != 0) {
            throw new \BgaSystemException('BUG! payedSproutList is not even');
        }
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $cardMgr = self::getMgr('card');
        $this->undoTableau = \BX\META\deepClone($cardMgr->getPlayerTableauCards($this->playerId, $this->playerId));

        $nbPayedSprout = 0;
        $payedSprout = [];
        for ($i = 0; $i < count($this->payedSproutList); $i += 2) {
            $payedSprout[$this->payedSproutList[$i]] = $this->payedSproutList[$i + 1];
            $nbPayedSprout += $this->payedSproutList[$i + 1];
        }
        if ($nbPayedSprout <= 0 || ($nbPayedSprout % 4) != 0) {
            throw new \BgaUserException($notifier->_('You must select a multiple of 4 sprouts (4, 8, 12, ...)'));
        }
        $this->nbGainedSeed = floor($nbPayedSprout / 4);
        foreach ($cardMgr->getPlayerTableauCountSprout($this->playerId) as $count) {
            if (!\array_key_exists($count->cardId, $payedSprout)) {
                continue;
            }
            if ($payedSprout[$count->cardId] < 0) {
                throw new \BgaSystemException('BUG! payedSprout is negative');
            }
            if ($count->count - $payedSprout[$count->cardId] < 0) {
                throw new \BgaSystemException("BUG! Payed too many sprouts on cardId {$count->cardId}");
            }
            $card = $cardMgr->getCardById($count->cardId);
            $card->modifyAction();
            $card->removeSprout($payedSprout[$count->cardId]);
            unset($payedSprout[$count->cardId]);
        }
        if (count($payedSprout) > 0) {
            throw new \BgaSystemException('BUG! payedSprout on invalid cards');
        }

        self::getMgr('player_state')->incStatNbSproutsConverted($this->playerId, $nbPayedSprout);
        self::getMgr('player_state')->incStatNbSeedGained($this->playerId, $this->nbGainedSeed);

        $notifier->notify(
            NTF_UPDATE_PLAYER_TABLEAU,
            clienttranslate('${player_name} converts ${nbPayedSprout} ${sproutIcon} to ${nbGainedSeed} ${seedIcon}'),
            [
                'nbPayedSprout' => $nbPayedSprout,
                'sproutIcon' => clienttranslate('sprout(s)'),
                'seedIcon' => clienttranslate('seed'),
                'nbGainedSeed' => $this->nbGainedSeed,
                'tableauCards' => \EA\cardsToCompactUI($cardMgr->getPlayerTableauCards($this->playerId, null)),
            ]
        );
        $notifier->notifyPrivateNoMessage(
            NTF_UPDATE_PLAYER_TABLEAU,
            [
                'tableauCards' => \EA\cardsToCompactUI($cardMgr->getPlayerTableauCards($this->playerId, $this->playerId)),
            ]
        );
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($this->undoTableau !== null) {
            $notifier->notifyNoMessage(NTF_UPDATE_PLAYER_TABLEAU, ['tableauCards' => \EA\cardsToCompactUI($this->undoTableau)]);
        }
    }

    public function getNbGainedSeed()
    {
        return $this->nbGainedSeed;
    }
}

class ConvertLeafToSeed extends \BX\Action\BaseActionCommand
{
    private $tokenId;
    private $undoLeaf;

    public function __construct(int $playerId, int $tokenId)
    {
        parent::__construct($playerId);
        $this->tokenId = $tokenId;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $leafTokenMgr = self::getMgr('leaf_token');
        $leafs = $leafTokenMgr->getDiscardableLeafInOrder($this->playerId);
        if (count($leafs) <= 0) {
            throw new \BgaSystemException('No dicardable leaf left!');
        }
        $leaf = null;
        foreach ($leafs as $t) {
            if ($t->tokenId == $this->tokenId) {
                $leaf = $t;
                break;
            }
        }
        if ($leaf === null) {
            throw new \BgaSystemException("Could not find leaf tokenId {$this->tokenId}");
        }

        $this->undoLeaf = \BX\Meta\deepClone($leaf);

        $leaf->modifyAction();
        if (isGameSolo()) {
            $leaf->moveToGaiaAbundance();
        } else {
            $leaf->moveToDiscard();
        }

        self::getMgr('player_state')->incStatNbLeafsConverted($this->playerId, 1);
        self::getMgr('player_state')->incStatNbSeedGained($this->playerId, 1);

        $notifier->notify(
            NTF_UPDATE_LEAF_TOKEN,
            clienttranslate('${player_name} converts 1 ${leafIcon} to 1 ${seedIcon}'),
            [
                'leafToken' => $leaf->toPlayerUI($this->playerId),
                'leafIcon' => clienttranslate('leaf'),
                'seedIcon' => clienttranslate('seed'),
            ]
        );
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $notifier->notifyNoMessage(
            NTF_UPDATE_LEAF_TOKEN,
            [
                'leafToken' => $this->undoLeaf->toPlayerUI($this->playerId),
            ]
        );
    }
}

class Germinate extends \BX\Action\BaseActionCommandNoUndo
{
    private $germinateId;
    private $cardId;

    public function __construct(int $playerId, int $germinateId, int $cardId)
    {
        parent::__construct($playerId);
        $this->germinateId = $germinateId;
        $this->cardId = $cardId;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $cardMgr = self::getMgr('card');
        $handCardIdsBefore = array_keys($cardMgr->getPlayerHandCards($this->playerId));

        $card = $cardMgr->getCardById($this->cardId);
        if ($card === null) {
            throw new \BgaSystemException("Cardid {$this->cardId} does not exist, cannot germinate");
        }
        if (!$card->isInDeck()) {
            throw new \BgaSystemException("Cardid {$this->cardId} is not in deck, cannot germinate");
        }

        $card->modifyAction();
        $card->moveToPlayerHand($this->playerId);

        if (isGameSolo()) {
            $gameStateMgr = self::getMgr('game_state');
            $gameStateMgr->modifySoloPlayerGainedCard(1);
        }

        self::getMgr('player_state')->incStatNbGerminate($this->playerId, 1);
        self::getMgr('player_state')->incStatNbCardsDrawn($this->playerId, 1);

        $handCardIdsAfter = array_keys($cardMgr->getPlayerHandCards($this->playerId));
        $notifier->notifyPrivateNoMessage(
            NTF_UPDATE_CARDS,
            [
                'cards' => [$card],
                'handCardIdsBefore' => $handCardIdsBefore,
                'handCardIdsAfter' => $handCardIdsAfter,
            ]
        );
        $notifier->notify(
            NTF_UPDATE_CARD_COUNTS,
            clienttranslate('${player_name} pays 1 ${seedIcon} to germinate with objective ${objective} and draws: ${cardName} ${cardImage}'),
            [
                'cardCounts' => $cardMgr->getCardCountsUIForPlayerId($this->playerId),
                'seedIcon' => clienttranslate('seed'),
                'objective' => \EA\CardDef::germinateIdToText($this->germinateId),
                'cardName' => $card->getCardDef()->name,
                'cardImage' => $card->cardId,
                'i18n' => ['seedIcon', 'objective', 'cardName'],
            ]
        );
        $notifier->notifyNoMessage(
            NTF_UPDATE_CARD_COUNTS,
            [
                'cardCounts' => $cardMgr->getCardCountsUIDeckDiscard(),
            ]
        );
    }
}

class GerminateFindsNoCard extends \BX\Action\BaseActionCommandNoUndo
{
    private $germinateId;

    public function __construct(int $playerId, int $germinateId)
    {
        parent::__construct($playerId);
        $this->germinateId = $germinateId;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        self::getMgr('player_state')->incStatNbGerminate($this->playerId, 1);

        $notifier->notify(
            \BX\Action\NTF_MESSAGE,
            clienttranslate('${player_name} tries Germinate but finds no cards matching objective: ${objective}'),
            [
                'objective' => \EA\CardDef::germinateIdToText($this->germinateId),
                'i18n' => ['objective'],
            ]
        );
    }
}
