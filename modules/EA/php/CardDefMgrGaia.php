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

trait CardDefMgrGaia
{
    private static function getCardDefGaia()
    {
        return [
            (new CardDefBuilder())->gaiaId(360)->name(clienttranslate("North Pacific Gyre"))
                ->ability((new AbilityBuilder())->brown()->gain(ABILITY_GAIA_FAUNA_UPPER, 1)
                    ->desc("Choose either upper Fauna objective, place one leaf token as Gaia. Beginner: Skip Gaia's next turn. Expert: +8 soil for left, +2 earth cards for right.")->build())
                ->build(),
            (new CardDefBuilder())->gaiaId(361)->name(clienttranslate("South Pacific Gyre"))
                ->ability((new AbilityBuilder())->brown()->gain(ABILITY_GAIA_FAUNA_LOWER, 1)
                    ->desc("Choose either lower Fauna objective, place one leaf token as Gaia. Beginner: Skip Gaia's next turn. Expert: +4 sprouts for left, +3 growth for right.")->build())
                ->build(),
            (new CardDefBuilder())->gaiaId(362)->name(clienttranslate("Water"))
                ->ability((new AbilityBuilder())->blue()
                    ->desc('Player: +2 sprout or +2 soil, activate blue and multicolor abilities. Gaia: +7 sprouts, +1 sprout per blue abilities of yours.')->build())
                ->build(),
            (new CardDefBuilder())->gaiaId(363)->name(clienttranslate("Grow"))
                ->ability((new AbilityBuilder())->yellow()
                    ->desc('Player: +2 growth or +2 card, activate yellow and multicolor abilities. Gaia: +7 growth, +1 growth per card you draw this turn.')->build())
                ->build(),
            (new CardDefBuilder())->gaiaId(364)->name(clienttranslate("Compost"))
                ->ability((new AbilityBuilder())->red()
                    ->desc('Player: +2 soil or +2 compost from deck, activate red and multicolor abilities. Gaia: +8 compost from deck, +1 soil per soil you gained this turn.')->build())
                ->build(),
            (new CardDefBuilder())->gaiaId(365)->name(clienttranslate("Plant"))
                ->ability((new AbilityBuilder())->green()
                    ->desc('Player: Plant one card, draw 1 card, activate breen abilities. Gaia: Draw and add 3 cards to their Earth cards.')->build())
                ->build(),
        ];
    }
}
