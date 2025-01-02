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

require_once('CardDef.php');
require_once('CardDefMgrIsland.php');
require_once('CardDefMgrClimate.php');
require_once('CardDefMgrEarth.php');
require_once('CardDefMgrFauna.php');
require_once('CardDefMgrEcosystem.php');
require_once('CardDefMgrGaia.php');
require_once('CardDefMgrAbundance.php');

class CardDefMgr
{
    public static function getAll()
    {
        self::initCardDefs();
        return self::$cardDefs;
    }

    public static function getAllIsland()
    {
        self::initCardDefs();
        return array_filter(self::$cardDefs, fn ($cd) => $cd->isIsland());
    }

    public static function getAllClimate()
    {
        self::initCardDefs();
        return array_filter(self::$cardDefs, fn ($cd) => $cd->isClimate());
    }

    public static function getAllEarth()
    {
        self::initCardDefs();
        return array_filter(self::$cardDefs, fn ($cd) => $cd->isEarth());
    }

    public static function getAllFauna()
    {
        self::initCardDefs();
        return array_filter(self::$cardDefs, fn ($cd) => $cd->isFauna());
    }

    public static function getAllEcosystem()
    {
        self::initCardDefs();
        return array_filter(self::$cardDefs, fn ($cd) => $cd->isEcosystem());
    }

    public static function getAllGaia()
    {
        self::initCardDefs();
        return array_filter(self::$cardDefs, fn ($cd) => $cd->isGaia());
    }

    public static function getByCardId(int $cardId)
    {
        self::initCardDefs();
        if (!array_key_exists($cardId, self::$cardDefs)) {
            return null;
        }
        return self::$cardDefs[$cardId];
    }

    use \EA\CardDefMgrIsland;
    use \EA\CardDefMgrClimate;
    use \EA\CardDefMgrEarth;
    use \EA\CardDefMgrFauna;
    use \EA\CardDefMgrEcosystem;
    use \EA\CardDefMgrGaia;
    use \EA\CardDefMgrAbundance;

    private static $cardDefs;

    private static function initCardDefs()
    {
        if (self::$cardDefs != null) {
            return;
        }
        self::$cardDefs = [];
        foreach (self::getCardDefEarth() as $cardDef) {
            self::$cardDefs[$cardDef->id] = $cardDef;
        }
        foreach (self::getCardDefIsland() as $cardDef) {
            self::$cardDefs[$cardDef->id] = $cardDef;
        }
        foreach (self::getCardDefClimate() as $cardDef) {
            self::$cardDefs[$cardDef->id] = $cardDef;
        }
        foreach (self::getCardDefEcosystem() as $cardDef) {
            self::$cardDefs[$cardDef->id] = $cardDef;
        }
        foreach (self::getCardDefFauna() as $cardDef) {
            self::$cardDefs[$cardDef->id] = $cardDef;
        }
        foreach (self::getCardDefGaia() as $cardDef) {
            self::$cardDefs[$cardDef->id] = $cardDef;
        }
        if (gameHasExpansionAbundance()) {
            foreach (self::getCardDefAbundance() as $cardDef) {
                self::$cardDefs[$cardDef->id] = $cardDef;
            }
        }
    }
}
