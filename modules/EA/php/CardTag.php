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

require_once(__DIR__ . '/../../BX/php/Action.php');

const CARD_TAGS = [0, 1, 2];

class CardTag extends \BX\Action\BaseActionRow
{
    /** @dbcol @dbkey */
    public $cardTagId;
    /** @dbcol */
    public $playerId;
    /** @dbcol */
    public $cardId;
    /** @dbcol */
    public $cardTag;
}

class CardTagMgr extends \BX\Action\BaseActionRowMgr
{
    public function __construct()
    {
        parent::__construct('card_tag', \EA\CardTag::class);
    }

    public function getAll()
    {
        return $this->getAllRowsByKey();
    }

    public function getPlayerCardTags(int $playerId)
    {
        return array_filter($this->getAll(), fn ($ct) => $ct->playerId == $playerId);
    }

    public function updateCardTag(int $playerId, int $cardId, int $cardTag)
    {
        if (array_search($cardTag, CARD_TAGS) === false) {
            throw new \BgaSystemException("BUG! Invalid cardTag: $cardTag");
        }
        $cardTags = array_values(array_filter($this->getPlayerCardTags($playerId), fn ($ct) => $ct->cardId == $cardId));
        if (count($cardTags) > 1) {
            throw new \BgaSystemException("BUG! Multiple cardTag: $cardTag");
        }
        if (count($cardTags) == 0) {
            $ct = $this->db->newRow();
            $ct->playerId = $playerId;
            $ct->cardId = $cardId;
            $ct->cardTag = $cardTag;
            $this->db->insertRow($ct);
            return;
        }
        $ct = $cardTags[0];
        if ($ct->cardTag == $cardTag) {
            $this->db->deleteRow($ct);
        } else {
            $ct->cardTag = $cardTag;
            $this->db->updateRow($ct);
        }
    }

    public function deleteCardTag(int $cardId)
    {
        foreach ($this->getAll() as $ct) {
            if ($ct->cardId == $cardId) {
                $this->db->deleteRow($ct);
            }
        }
    }
}
