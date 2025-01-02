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

trait CardDefMgrAbundance
{
    private static function getCardDefAbundance()
    {
        return [
            // Earth
            (new CardDefBuilder())->terrainId(284)->name(clienttranslate('Chernozem Terrain'))
                ->soil(3)->score(0)
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_SPROUT, 3)->condition(AB_COND_PER_COLOR, AB_COLOR_BLACK)
                    ->desc(clienttranslate('Gain 3 sprouts per black ability, including this one'))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->terrainId(285)->name(clienttranslate('Volcanic Island Terrain'))
                ->soil(8)->score(6)->sunny()->wet()->rocky()->cold()
                ->ability((new AbilityBuilder())->brown()->scores([AB_SCORE_PER_EVENT, 3])
                    ->desc(clienttranslate('Score 3 points per Event cards played by you'))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->terrainId(286)->name(clienttranslate('Rainbow Mountain Terrain'))
                ->soil(3)->score(4)->sunny()->rocky()->cold()
                ->ability((new AbilityBuilder())->green()->gain(ABILITY_GROWTH, 1)->build())
                ->ability((new AbilityBuilder())->brown()->scores([AB_SCORE_DIRECTION_DIFFERENT_COLOR, 9])->direction(AB_DIRECTION_ROW)
                    ->desc(clienttranslate('Score 9 points if all cards in this row have a different ability colour'))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->terrainId(287)->name(clienttranslate('Forest Meadow Terrain'))
                ->soil(10)->score(0)->wet()->rocky()->cold()
                ->ability((new AbilityBuilder())->green()->gain(ABILITY_SEED, 1)->build())
                ->ability((new AbilityBuilder())->brown()->scores([AB_SCORE_DIRECTION_DIFFERENT_COLOR, 9])->direction(AB_DIRECTION_COLUMN)
                    ->desc(clienttranslate('Score 9 points if all cards in this column have a different ability colour'))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->terrainId(288)->name(clienttranslate('Strait Terrain'))
                ->soil(7)->score(9)->sunny()->rocky()
                ->ability((new AbilityBuilder())->brown()->scores([AB_SCORE_PER_DIRECTIONAL_AID, 3])
                    ->desc(clienttranslate('Score 3 points per card with directional aids (arrows)'))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->terrainId(289)->name(clienttranslate('Prominent Peak Terrain'))
                ->soil(3)->score(4)->sunny()->rocky()->cold()
                ->ability((new AbilityBuilder())->green()->gain(ABILITY_DRAW_CARD_FROM_DECK, 1)->build())
                ->ability((new AbilityBuilder())->brown()->scores([AB_SCORE_DIRECTION_DIFFERENT_GROWTH, 9])->direction(AB_DIRECTION_COLUMN)
                    ->desc(clienttranslate('Score 9 points if all cards in this column have a different number of growth pieces'))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->terrainId(290)->name(clienttranslate('Cordillera Terrain'))
                ->soil(2)->score(4)->sunny()->wet()->rocky()->cold()
                ->ability((new AbilityBuilder())->green()->gain(ABILITY_GROWTH, 1)->build())
                ->ability((new AbilityBuilder())->brown()->scores([AB_SCORE_DIRECTION_DIFFERENT_GROWTH, 9])->direction(AB_DIRECTION_ROW)
                    ->desc(clienttranslate('Score 9 points if all cards in this row have a different number of growth pieces'))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->terrainId(291)->name(clienttranslate('Cold Peaked Mount Terrain'))
                ->soil(3)->score(4)->rocky()->cold()
                ->ability((new AbilityBuilder())->green()->gain(ABILITY_SOIL, 1)->build())
                ->ability((new AbilityBuilder())->brown()->scores([AB_SCORE_DIRECTION_DIFFERENT_SPROUT, 9])->direction(AB_DIRECTION_COLUMN)
                    ->desc(clienttranslate('Score 9 points if all cards in this column have a different number of sprouts'))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->terrainId(292)->name(clienttranslate('Sub-Fridgid Grassland Terrain'))
                ->soil(3)->score(3)->sunny()->wet()->rocky()->cold()
                ->ability((new AbilityBuilder())->green()->gain(ABILITY_SPROUT, 2)->build())
                ->ability((new AbilityBuilder())->brown()->scores([AB_SCORE_DIRECTION_DIFFERENT_SPROUT, 9])->direction(AB_DIRECTION_ROW)
                    ->desc(clienttranslate('Score 9 points if all cards in this row have a different number of sprouts'))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->terrainId(293)->name(clienttranslate('Tropical Sierra Terrain'))
                ->soil(10)->score(8)->sunny()->wet()->rocky()->cold()
                ->ability((new AbilityBuilder())->brown()->scores([AB_SCORE_PER_SET, 6])
                    ->desc(clienttranslate('Score 6 points per set of 5 cards including Yellow, Blue, Red, Terrain and Event, each on a different card (including Island and Climate)'))
                    ->faq(clienttranslate("To get 12 points with this card, you must have 2 Red, 2 Blue, 2 Yellow, 2 Terrains and 2 Events. Each must be from a different card."))
                    ->build())
                ->abundance()->build(),
            (new CardDefBuilder())->eventId(294)->name(clienttranslate('Ice Age Event'))
                ->score(2)
                ->ability((new AbilityBuilder())->black()->pay(ABILITY_SPROUT, 2)->gain(ABILITY_DRAW_CARD_FROM_DECK, 3)->build())
                ->endTurn()
                ->abundance()->build(),
            (new CardDefBuilder())->eventId(295)->name(clienttranslate('Warm Period Event'))
                ->score(0)
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_SPROUT, 5)->gain(ABILITY_SPROUT_ALL_OTHERS, 3)->build())
                ->abundance()->build(),
            (new CardDefBuilder())->eventId(296)->name(clienttranslate('Hot Period Event'))
                ->score(1)
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_GROWTH, 3)->gain(ABILITY_SPROUT_ALL_OTHERS, 2)->build())
                ->abundance()->build(),
            (new CardDefBuilder())->eventId(297)->name(clienttranslate('Continental Shift Event'))
                ->score(1)
                ->ability((new AbilityBuilder())->black()->pay(ABILITY_SPROUT, 4)->gain(ABILITY_DRAW_CARD_FROM_DECK, 2)->gain(ABILITY_COMPOST_FROM_DECK, 6)->build())
                ->endTurn()
                ->abundance()->build(),
            (new CardDefBuilder())->eventId(298)->name(clienttranslate('Super Volcano Event'))
                ->score(1)
                ->ability((new AbilityBuilder())->black()->pay(ABILITY_SPROUT, 5)->gain(ABILITY_SOIL, 6)->build())
                ->endTurn()
                ->abundance()->build(),
            (new CardDefBuilder())->eventId(299)->name(clienttranslate('Magnetic Flip Event'))
                ->score(-1)
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_DRAW_CARD_FROM_DECK, 3)->gain(ABILITY_SPROUT_ALL_OTHERS, 2)->build())
                ->abundance()->build(),
            (new CardDefBuilder())->eventId(300)->name(clienttranslate('Massive Asteroid Event'))
                ->score(1)
                ->ability((new AbilityBuilder())->black()->pay(ABILITY_SPROUT, 4)->pay(ABILITY_GROWTH, 4)->gain(ABILITY_DRAW_CARD_FROM_DECK, 3)->gain(ABILITY_SOIL, 5)->build())
                ->endTurn()
                ->abundance()->build(),
            (new CardDefBuilder())->eventId(301)->name(clienttranslate('New Mountain Range Formation Event'))
                ->score(0)
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_SOIL, 2)->gain(ABILITY_SPROUT_ALL_OTHERS, 2)->build())
                ->abundance()->build(),
            (new CardDefBuilder())->eventId(302)->name(clienttranslate('Monsoon Event'))
                ->score(1)
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_SPROUT, 2)->gain(ABILITY_GROWTH, 2)->gain(ABILITY_SPROUT_ALL_OTHERS, 3)->build())
                ->abundance()->build(),
            (new CardDefBuilder())->terrainId(303)->name(clienttranslate('Volcanic Crater Terrain'))
                ->soil(8)->score(4)->rocky()->cold()
                ->ability((new AbilityBuilder())->brown()->scores([AB_SCORE_PER_GERMINATE_CONDITION, 3, GERMINATE_CARD_ABILITY_ICON_DRAW])
                    ->desc(clienttranslate('Gain 3 points and also gain 3 points per card with a Draw ability'))->build())
                ->germinate(GERMINATE_CARD_ABILITY_ICON_DRAW)
                ->abundance()->build(),
            (new CardDefBuilder())->terrainId(304)->name(clienttranslate('Lava Field Terrain'))
                ->soil(10)->score(0)->sunny()->rocky()
                ->ability((new AbilityBuilder())->brown()->scores([AB_SCORE_PER_GERMINATE_CONDITION, 2, GERMINATE_CARD_ABILITY_ICON_SOIL])
                    ->desc(clienttranslate('Gain 2 points and also gain 2 points per card with a Soil ability'))->build())
                ->germinate(GERMINATE_CARD_ABILITY_ICON_SOIL)
                ->abundance()->build(),
            (new CardDefBuilder())->terrainId(305)->name(clienttranslate('Canyon Terrain'))
                ->soil(6)->score(2)->wet()->rocky()
                ->ability((new AbilityBuilder())->brown()->scores([AB_SCORE_PER_GERMINATE_CONDITION, 2, GERMINATE_CARD_ABILITY_ICON_COMPOST])
                    ->desc(clienttranslate('Gain 2 points and also gain 2 points per card with a Compost ability'))->build())
                ->germinate(GERMINATE_CARD_ABILITY_ICON_COMPOST)
                ->abundance()->build(),
            (new CardDefBuilder())->terrainId(306)->name(clienttranslate('Bayou Terrain'))
                ->soil(12)->score(0)->wet()
                ->ability((new AbilityBuilder())->brown()->scores([AB_SCORE_PER_GERMINATE_CONDITION, 3, GERMINATE_CARD_ABILITY_ICON_GROWTH])
                    ->desc(clienttranslate('Gain 3 points and also gain 3 points per card with a Growth ability'))->build())
                ->germinate(GERMINATE_CARD_ABILITY_ICON_GROWTH)
                ->abundance()->build(),
            (new CardDefBuilder())->terrainId(307)->name(clienttranslate('Floodplain Terrain'))
                ->soil(12)->score(0)->wet()
                ->ability((new AbilityBuilder())->brown()->scores([AB_SCORE_PER_GERMINATE_CONDITION, 3, GERMINATE_CARD_ABILITY_ICON_SPROUT])
                    ->desc(clienttranslate('Gain 3 points and also gain 3 points per card with a Sprout ability'))->build())
                ->germinate(GERMINATE_CARD_ABILITY_ICON_SPROUT)
                ->abundance()->build(),
            (new CardDefBuilder())->terrainId(308)->name(clienttranslate('Tropical Jungle Terrain'))
                ->soil(8)->score(4)->wet()
                ->ability((new AbilityBuilder())->brown()->scores([AB_SCORE_PER_GERMINATE_CONDITION, 2, GERMINATE_CARD_ABILITY_ICON_COLON])
                    ->desc(clienttranslate('Gain 2 points and also gain 2 points per card with a : (colon) ability'))->build())
                ->germinate(GERMINATE_CARD_ABILITY_ICON_COLON)
                ->abundance()->build(),
            (new CardDefBuilder())->bushId(309)->name(clienttranslate('Foxgloves'), 'Digitalis purpurea')
                ->soil(3)->score(1)
                ->growth(1, 1)->sprout(4)->underline()
                ->ability((new AbilityBuilder())->red()->gain(ABILITY_SOIL, 4)->gain(ABILITY_SPROUT_ALL_OTHERS, 2)->build())
                ->abundance()->build(),
            (new CardDefBuilder())->herbId(310)->name(clienttranslate('Poppy'), 'Papaver rhoras')
                ->soil(2)->score(1)
                ->growth(3, 3)->sprout(5)
                ->ability((new AbilityBuilder())->blue()->gain(ABILITY_SPROUT, 4)->gain(ABILITY_SPROUT_ALL_OTHERS, 1)->build())
                ->abundance()->build(),
            (new CardDefBuilder())->herbId(311)->name(clienttranslate('Orange Nasturium'), 'Tropaeolum majus')
                ->soil(3)->score(1)
                ->growth(2, 2)->sprout(6)->sunny()->rocky()->italic()
                ->ability((new AbilityBuilder())->yellow()->gain(ABILITY_GROWTH, 3)->gain(ABILITY_SPROUT_ALL_OTHERS, 1)->build())
                ->abundance()->build(),
            (new CardDefBuilder())->mushroomId(312)->name(clienttranslate('Armillaria Fungus'), 'Armillaria ostoyae')
                ->soil(3)->score(0)
                ->growth(5, 6)->sprout(6)->wet()->rocky()->cold()
                ->ability((new AbilityBuilder())->red()->gain(ABILITY_COMPOST_FROM_DECK, 3)->gain(ABILITY_SPROUT_ALL_OTHERS, 1)->build())
                ->abundance()->build(),
            (new CardDefBuilder())->treeId(313)->name(clienttranslate('American Aspen'), 'Populus tremuloides')
                ->soil(6)->score(0)
                ->growth(4, 5)->sprout(6)->rocky()->bold()
                ->ability((new AbilityBuilder())->yellow()->gain(ABILITY_DRAW_CARD_FROM_DECK, 3)->gain(ABILITY_SPROUT_ALL_OTHERS, 2)->build())
                ->abundance()->build(),
            (new CardDefBuilder())->mushroomId(314)->name(clienttranslate('Yellow Netted Stinkhorn'), 'Phallus multicolor')
                ->soil(3)->score(4)
                ->sprout(2)->sunny()->rocky()->cold()->italic()
                ->ability((new AbilityBuilder())->multicolor()->gain(ABILITY_SOIL, 1)->build())
                ->abundance()->build(),
            (new CardDefBuilder())->treeId(315)->name(clienttranslate('Japanese Cherry Tree'), 'Prunus serrulata')
                ->soil(9)->score(3)
                ->growth(4, 6)->sprout(1)->sunny()->wet()->cold()->bold()->italic()
                ->ability((new AbilityBuilder())->multicolor()->gain(ABILITY_GROWTH, 2)->build())
                ->abundance()->build(),
            (new CardDefBuilder())->herbId(316)->name(clienttranslate('Nemesia'), 'Nemesia cheiranthus')
                ->soil(12)->score(5)
                ->growth(3, 3)->sprout(6)->sunny()->rocky()->cold()
                ->ability((new AbilityBuilder())->multicolor()->gain(ABILITY_SPROUT, 3)->build())
                ->abundance()->build(),
            (new CardDefBuilder())->treeId(317)->name(clienttranslate('Plum Tree'), 'Prunus domestica')
                ->soil(9)->score(5)
                ->growth(5, 6)->sprout(4)->sunny()
                ->ability((new AbilityBuilder())->multicolor()->gain(ABILITY_DRAW_CARD_FROM_DECK, 1)->build())
                ->abundance()->build(),
            (new CardDefBuilder())->bushId(318)->name(clienttranslate('Concord Grape'), 'Vitis labrusca')
                ->soil(3)->score(1)
                ->growth(3, 5)->sprout(2)
                ->ability((new AbilityBuilder())->red()->gain(ABILITY_SEED, 2)->gain(ABILITY_SPROUT_ALL_OTHERS, 4)->build())
                ->abundance()->build(),
            (new CardDefBuilder())->bushId(319)->name(clienttranslate('Salmonberry'), 'Rubus spectabilis')
                ->soil(7)->score(4)
                ->growth(4, 5)->sprout(2)->underline()
                ->ability((new AbilityBuilder())->yellow()->gain(ABILITY_SEED, 2)->gain(ABILITY_SPROUT_ALL_OTHERS, 4)->build())
                ->abundance()->build(),
            (new CardDefBuilder())->mushroomId(320)->name(clienttranslate('Bamboo Mushrooms'), 'Phallus indusiatus')
                ->soil(3)->score(0)
                ->growth(3, 4)->sprout(4)->wet()->rocky()
                ->ability((new AbilityBuilder())->blue()->gain(ABILITY_SEED, 2)->gain(ABILITY_SPROUT_ALL_OTHERS, 4)->build())
                ->abundance()->build(),
            (new CardDefBuilder())->mushroomId(321)->name(clienttranslate('Mica Cap'), 'Coprinellus micaceus')
                ->soil(6)->score(0)
                ->growth(2, 5)->sprout(6)->sunny()->rocky()
                ->ability((new AbilityBuilder())->red()->gain(ABILITY_SEED, 1)->build())
                ->abundance()->build(),
            (new CardDefBuilder())->treeId(322)->name(clienttranslate('Thuja Giant'), 'Thuja koraiensis x plicata')
                ->soil(8)->score(1)
                ->growth(6, 8)->sprout(4)->sunny()->cold()
                ->ability((new AbilityBuilder())->yellow()->gain(ABILITY_SEED, 1)->build())
                ->abundance()->build(),
            (new CardDefBuilder())->herbId(323)->name(clienttranslate('Meadowsweet'), 'Filipendula ulmaria')
                ->soil(3)->score(0)
                ->growth(4, 6)->sprout(3)->wet()
                ->ability((new AbilityBuilder())->yellow()->gain(ABILITY_DRAW_CARD_FROM_DECK, 2)->gain(ABILITY_SPROUT_ALL_OTHERS, 2)->build())
                ->ability((new AbilityBuilder())->red()->gain(ABILITY_SOIL, 2)->gain(ABILITY_SPROUT_ALL_OTHERS, 1)->build())
                ->abundance()->build(),
            (new CardDefBuilder())->bushId(324)->name(clienttranslate('Roman Candle Podocarpus'), 'Podocarpus macrophyllus')
                ->soil(3)->score(0)
                ->growth(2, 2)->sprout(2)->sunny()->rocky()->bold()
                ->ability((new AbilityBuilder())->yellow()->gain(ABILITY_GROWTH, 2)->gain(ABILITY_SPROUT_ALL_OTHERS, 1)->build())
                ->ability((new AbilityBuilder())->blue()->gain(ABILITY_SEED, 2)->gain(ABILITY_SPROUT_ALL_OTHERS, 4)->build())
                ->abundance()->build(),
            (new CardDefBuilder())->mushroomId(325)->name(clienttranslate('Haymaker Mushroom'), 'Panaeolina foenisecii')
                ->soil(0)->score(1)
                ->growth(2, 2)->sprout(4)->sunny()->wet()->cold()
                ->ability((new AbilityBuilder())->red()->gain(ABILITY_COMPOST_FROM_DECK, 2)->gain(ABILITY_SPROUT_ALL_OTHERS, 1)->build())
                ->ability((new AbilityBuilder())->blue()->gain(ABILITY_SOIL, 2)->gain(ABILITY_SPROUT_ALL_OTHERS, 2)->build())
                ->abundance()->build(),
            (new CardDefBuilder())->mushroomId(326)->name(clienttranslate('Porcelain Fungus'), 'Oudemansiella mucida')
                ->soil(3)->score(3)
                ->growth(1, 1)->sprout(4)->wet()
                ->ability((new AbilityBuilder())->yellow()->pay(ABILITY_GROWTH, 1)->gain(ABILITY_SOIL, 3)->gain(ABILITY_SPROUT_CHOOSE_ONE, 2)->build())
                ->ability((new AbilityBuilder())->blue()->pay(ABILITY_COMPOST_DESTROY, 1)->gain(ABILITY_SOIL, 3)->gain(ABILITY_SPROUT_CHOOSE_ONE, 2)->build())
                ->abundance()->build(),
            (new CardDefBuilder())->treeId(327)->name(clienttranslate('Ponderosa Pine'), 'Pinus ponderosa')
                ->soil(9)->score(5)
                ->growth(7, 10)->sprout(4)
                ->ability((new AbilityBuilder())->red()->pay(ABILITY_SOIL, 1)->gain(ABILITY_DRAW_CARD_FROM_DECK, 2)->gain(ABILITY_SPROUT_CHOOSE_ONE, 2)->build())
                ->ability((new AbilityBuilder())->blue()->pay(ABILITY_SPROUT, 2)->gain(ABILITY_GROWTH, 4)->gain(ABILITY_SPROUT_CHOOSE_ONE, 1)->build())
                ->abundance()->build(),
            (new CardDefBuilder())->mushroomId(328)->name(clienttranslate('Red Bolete'), 'Baorangia bicolor')
                ->soil(3)->score(2)
                ->growth(3, 5)->rocky()->cold()->italic()
                ->ability((new AbilityBuilder())->yellow()->pay(ABILITY_COMPOST_FROM_HAND, 1)->gain(ABILITY_SOIL, 3)->gain(ABILITY_SPROUT_CHOOSE_ONE, 2)->build())
                ->ability((new AbilityBuilder())->red()->pay(ABILITY_GROWTH, 1)->gain(ABILITY_DRAW_CARD_FROM_DECK, 2)->gain(ABILITY_SPROUT_CHOOSE_ONE, 2)->build())
                ->abundance()->build(),
            (new CardDefBuilder())->herbId(329)->name(clienttranslate('Dutch Iris'), 'Iris hollandica')
                ->soil(3)->score(0)
                ->growth(1, 1)->sprout(2)->sunny()->wet()->bold()
                ->ability((new AbilityBuilder())->yellow()->gain(ABILITY_SOIL, 1)->build())
                ->ability((new AbilityBuilder())->blue()->gain(ABILITY_SEED, 1)->build())
                ->abundance()->build(),
            (new CardDefBuilder())->bushId(330)->name(clienttranslate('Quesnelia'), 'Quesnelia quesneliana')
                ->soil(8)->score(0)
                ->growth(4, 5)->sprout(4)->sunny()->wet()
                ->ability((new AbilityBuilder())->red()->gain(ABILITY_SEED, 1)->build())
                ->ability((new AbilityBuilder())->blue()->gain(ABILITY_SOIL, 1)->build())
                ->abundance()->build(),
            (new CardDefBuilder())->herbId(331)->name(clienttranslate('Elegant zinnia'), 'Zinnia elegans')
                ->soil(3)->score(0)
                ->growth(1, 1)->sprout(2)->sunny()->wet()
                ->ability((new AbilityBuilder())->yellow()->gain(ABILITY_SEED, 1)->build())
                ->ability((new AbilityBuilder())->red()->gain(ABILITY_DRAW_CARD_FROM_DECK, 1)->build())
                ->abundance()->build(),
            (new CardDefBuilder())->herbId(332)->name(clienttranslate('Zebra Plant'), 'Aphelandra squarrosa')
                ->soil(3)->score(1)
                ->growth(1, 1)->sprout(4)->underline()
                ->ability((new AbilityBuilder())->yellow()->gain(ABILITY_GROWTH, 1)->condition(AB_COND_PER_NEIGHBOUR, 1)->condition(AB_COND_PER_TYPE, CARD_TYPE_TREE)
                    ->desc(clienttranslate("Gain 1 growth per Tree owned by one of your neighbours"))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->mushroomId(333)->name(clienttranslate('Rice Blast Fungus'), 'Magnaporthe oryzae')
                ->soil(3)->score(1)
                ->growth(4, 4)->sprout(2)->wet()->cold()
                ->ability((new AbilityBuilder())->blue()->gain(ABILITY_SPROUT, 1)->condition(AB_COND_PER_NEIGHBOUR, 1)->condition(AB_COND_PER_TYPE, CARD_TYPE_HERB)
                    ->desc(clienttranslate("Gain 1 sprout per Herb owned by one of your neighbours"))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->herbId(334)->name(clienttranslate('Tea Plant'), 'Camellia sinensis')
                ->soil(7)->score(0)
                ->sprout(6)->sunny()->cold()
                ->ability((new AbilityBuilder())->red()->gain(ABILITY_SOIL, 1)->condition(AB_COND_PER_NEIGHBOUR, 1)->condition(AB_COND_PER_TYPE, CARD_TYPE_BUSH)
                    ->desc(clienttranslate("Gain 1 soil per Bush owned by one of your neighbours"))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->mushroomId(335)->name(clienttranslate('Russula parasite'), 'Hypomyces lutevirens')
                ->soil(6)->score(1)
                ->growth(3, 5)->sprout(6)->wet()->cold()
                ->ability((new AbilityBuilder())->red()->gain(ABILITY_COMPOST_FROM_DECK, 1)->condition(AB_COND_PER_NEIGHBOUR, 1)->condition(AB_COND_PER_TYPE, CARD_TYPE_MUSHROOM)
                    ->desc(clienttranslate("Compost 1 card from the deck per Mushroom owned by one of your neighbours"))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->mushroomId(336)->name(clienttranslate('Red Waxy Cap'), 'Hygrocybe coccinea')
                ->soil(7)->score(1)
                ->growth(3, 6)->sprout(6)->rocky()->cold()->italic()
                ->ability((new AbilityBuilder())->red()->gain(ABILITY_COMPOST_FROM_HAND, 1)->condition(AB_COND_PER_NEIGHBOUR, 2)->condition(AB_COND_PER_COLOR, AB_COLOR_RED)
                    ->desc(clienttranslate("Compost 1 card from your hand per 2 red abilities owned by one of your neighbours"))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->treeId(337)->name(clienttranslate('Blue Palo Verde'), 'Parkinsonia florida')
                ->soil(6)->score(1)
                ->growth(2, 3)->sunny()->italic()
                ->ability((new AbilityBuilder())->yellow()->gain(ABILITY_GROWTH, 1)->condition(AB_COND_PER_NEIGHBOUR, 2)->condition(AB_COND_PER_COLOR, AB_COLOR_YELLOW)
                    ->desc(clienttranslate("Gain 1 growth per 2 yellow abilities owned by one of your neighbours"))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->herbId(338)->name(clienttranslate('Borage'), 'Borago officinalis')
                ->soil(6)->score(2)
                ->growth(1, 2)->sprout(3)->sunny()
                ->ability((new AbilityBuilder())->blue()->gain(ABILITY_SPROUT, 1)->condition(AB_COND_PER_NEIGHBOUR, 2)->condition(AB_COND_PER_COLOR, AB_COLOR_BLUE)
                    ->desc(clienttranslate("Gain 1 sprout per 2 blue abilities owned by one of your neighbours"))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->bushId(339)->name(clienttranslate('Prickly Russian Thistle'), 'Kali tragus')
                ->soil(8)->score(1)
                ->growth(2, 2)->sprout(4)->rocky()->bold()
                ->ability((new AbilityBuilder())->red()->gain(ABILITY_SOIL, 1)->condition(AB_COND_PER_NEIGHBOUR, 1)->condition(AB_COND_PER_TYPE, CARD_TYPE_TERRAIN)
                    ->desc(clienttranslate("Gain 1 soil per Terrain owned by one of your neighbours"))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->treeId(340)->name(clienttranslate('Cacao Tree'), 'Theobroma cacao')
                ->soil(2)->score(4)
                ->growth(1, 1)->sprout(4)->rocky()
                ->ability((new AbilityBuilder())->blue()->gain(ABILITY_COMPOST_FROM_HAND, 1)->condition(AB_COND_PER_NEIGHBOUR, 1)->condition(AB_COND_PER_TYPE, CARD_TYPE_EVENT)
                    ->desc(clienttranslate("Compost 1 card from your hand per event played by one of your neighbours"))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->bushId(341)->name(clienttranslate('Kangaroo Paw'), 'Anigozanthos')
                ->soil(8)->score(0)
                ->growth(3, 3)->sprout(6)->cold()->underline()
                ->ability((new AbilityBuilder())->yellow()->gain(ABILITY_DRAW_CARD_FROM_DECK, 1)->condition(AB_COND_PER_NEIGHBOUR, 1)->condition(AB_COND_PER_COLOR, AB_COLOR_GREEN)
                    ->desc(clienttranslate("Draw 1 card per green abilities owned by one of your neighbours"))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->bushTreeId(342)->name(clienttranslate('Honey Locust'), 'Gleditsia triacanthos')
                ->soil(6)->score(0)
                ->growth(1, 1)->sprout(2)->wet()->rocky()->cold()->underline()
                ->ability((new AbilityBuilder())->blue()->gain(ABILITY_SOIL, 1)->condition(AB_COND_PER_NEIGHBOUR, 1)->condition(AB_COND_PER_COLOR, AB_COLOR_BLACK)
                    ->desc(clienttranslate("Gain 1 soil per black abilities owned by one of your neighbours"))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->bushId(343)->name(clienttranslate('Purple Butterfly bush'), 'Buddleia davidii')
                ->soil(9)->score(0)
                ->growth(3, 3)->sprout(4)->sunny()->wet()->rocky()->cold()->underline()->italic()
                ->ability((new AbilityBuilder())->red()->gain(ABILITY_SOIL, 2)->condition(AB_COND_PER_NEIGHBOUR, 1)->condition(AB_COND_PER_COLOR, AB_COLOR_MULTICOLOR)
                    ->desc(clienttranslate("Gain 2 soil per multicolor abilities owned by one of your neighbours"))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->herbId(344)->name(clienttranslate('Snake plant'), 'Sansevieria trifasciata')
                ->soil(2)->score(3)
                ->growth(2, 2)->sprout(2)->sunny()->wet()->rocky()->cold()->underline()
                ->ability((new AbilityBuilder())->multicolor()->gain(ABILITY_SPROUT, 1)->condition(AB_COND_PER_NEIGHBOUR, 1)->condition(AB_COND_PER_COLOR, AB_COLOR_MULTICOLOR)
                    ->desc(clienttranslate("Gain 1 sprout per multicolor abilities owned by one of your neighbours"))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->treeId(345)->name(clienttranslate('Madagascar Dragon Tree'), 'Dracaena draco')
                ->soil(8)->score(0)
                ->growth(1, 1)->sprout(6)->wet()->cold()->bold()->underline()
                ->ability((new AbilityBuilder())->blue()->gain(ABILITY_SPROUT, 1)->condition(AB_COND_PER_NEIGHBOUR, 1)->condition(AB_COND_PER_GERMINATE, GERMINATE_ABILITY_2)
                    ->desc(clienttranslate("Gain 1 sprout per card with 2 different abilities owned by one of your neighbours"))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->herbId(346)->name(clienttranslate('White Snowdrop'), 'Galanthus nivalis')
                ->soil(2)->score(3)
                ->growth(2, 3)->sprout(3)->rocky()->italic()
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_COMPOST_FROM_HAND, 1)->condition(AB_COND_PER_NEIGHBOUR, 1)->condition(AB_COND_PER_COLOR, AB_COLOR_RED)
                    ->desc(clienttranslate("Compost 1 card from your hand per red abilities owned by one of your neighbours"))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->bushTreeId(347)->name(clienttranslate('Chinese Elm'), 'Ulmus parvifolia')
                ->soil(5)->score(0)
                ->growth(1, 1)->sprout(2)->cold()->bold()
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_SOIL, 1)->condition(AB_COND_PER_NEIGHBOUR, 1)->condition(AB_COND_PER_COLOR, AB_COLOR_BLUE)
                    ->desc(clienttranslate("Gain 1 soil per blue abilities owned by one of your neighbours"))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->bushTreeId(348)->name(clienttranslate('Siberian Elm'), 'Ulmus pumila')
                ->soil(3)->score(0)
                ->growth(2, 4)->sprout(5)->sunny()->wet()->bold()
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_ALL_MAY_PLANT_MORE_CARD, 1)
                    ->desc(clienttranslate('Everyone may play an additional card during this green action'))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->mushroomId(349)->name(clienttranslate('Netted Rhodotus'), 'Rhodotus palmatus')
                ->soil(3)->score(1)
                ->growth(2, 3)->sprout(3)->wet()->cold()
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_DRAW_CARD_FROM_DECK, 4)->gain(ABILITY_SPROUT_ALL_OTHERS, 2)->build())
                ->abundance()->build(),
            (new CardDefBuilder())->treeId(350)->name(clienttranslate('Black Bamboo Tree'), 'Phyllostachys nigra')
                ->soil(3)->score(0)
                ->growth(3, 3)->sprout(4)->sunny()->wet()->cold()->italic()
                ->ability((new AbilityBuilder())->yellow()->gain(ABILITY_GROWTH, 1)->condition(AB_COND_ADD_TO_TYPE_IN_DIRECTION)->condition(AB_COND_PER_TYPE, CARD_TYPE_TREE)->direction(AB_DIRECTION_DIAG_ADJACENT)
                    ->desc(clienttranslate('Place up to 1 growth on this Flora and each diagonally adjacent Tree card'))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->herbId(351)->name(clienttranslate('Arugula'), 'Eruca vesicaria')
                ->soil(2)->score(1)
                ->sprout(6)->sunny()
                ->ability((new AbilityBuilder())->blue()->gain(ABILITY_SPROUT, 1)->condition(AB_COND_ADD_TO_TYPE_IN_DIRECTION)->condition(AB_COND_PER_TYPE, CARD_TYPE_HERB)->direction(AB_DIRECTION_DIAG_ADJACENT)
                    ->desc(clienttranslate('Place up to 1 sprout on this Flora and each diagonally adjacent Herb card'))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->mushroomId(352)->name(clienttranslate('Aspen Boletes'), 'Leccinum insigne')
                ->soil(3)->score(4)
                ->growth(1, 1)->sprout(4)->wet()
                ->ability((new AbilityBuilder())->blue()->gain(ABILITY_SPROUT, 1)->condition(AB_COND_ADD_TO_TYPE_IN_DIRECTION)->condition(AB_COND_PER_TYPE, CARD_TYPE_MUSHROOM)->direction(AB_DIRECTION_DIAG_ADJACENT)
                    ->desc(clienttranslate('Place up to 1 sprout on this Flora and each diagonally adjacent Mushroom card'))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->bushId(353)->name(clienttranslate('Wax-leaf Privet'), 'Ligustrum japonicum')
                ->soil(3)->score(4)
                ->growth(2, 2)->sunny()->wet()->rocky()->cold()
                ->ability((new AbilityBuilder())->yellow()->gain(ABILITY_GROWTH, 1)->condition(AB_COND_ADD_TO_TYPE_IN_DIRECTION)->condition(AB_COND_PER_TYPE, CARD_TYPE_BUSH)->direction(AB_DIRECTION_DIAG_ADJACENT)
                    ->desc(clienttranslate('Place up to 1 growth on this Flora and each diagonally adjacent Bush card'))->build())
                ->abundance()->build(),
            // Island
            (new CardDefBuilder())->islandId(295, 1)->name(clienttranslate("Vancouver Island"))
                ->score(3)
                ->wet()->cold()
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_DRAW_CARD_FROM_DECK, 12)->pay(ABILITY_COMPOST_FROM_HAND, 6)->gain(ABILITY_SOIL, 6)->build())
                ->ability((new AbilityBuilder())->yellow()->gain(ABILITY_GROWTH, 2)->gain(ABILITY_SPROUT_CHOOSE_ONE, 2)->build())
                ->abundance()->build(),
            (new CardDefBuilder())->islandId(295, 2)->name(clienttranslate("Ross Island"))
                ->score(7)
                ->rocky()->cold()
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_DRAW_CARD_FROM_DECK, 9)->pay(ABILITY_COMPOST_FROM_HAND, 5)->gain(ABILITY_SOIL, 5)->build())
                ->ability((new AbilityBuilder())->red()->gain(ABILITY_COMPOST_FROM_DECK, 2)->gain(ABILITY_SPROUT_CHOOSE_ONE, 1)->build())
                ->abundance()->build(),
            (new CardDefBuilder())->islandId(296, 1)->name(clienttranslate("Java Island"))
                ->score(10)
                ->sunny()->rocky()
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_DRAW_CARD_FROM_DECK, 8)->pay(ABILITY_COMPOST_FROM_HAND, 4)->gain(ABILITY_SOIL, 4)->build())
                ->ability((new AbilityBuilder())->blue()->gain(ABILITY_SPROUT, 2)->gain(ABILITY_SPROUT_CHOOSE_ONE, 1)->build())
                ->abundance()->build(),
            (new CardDefBuilder())->islandId(296, 2)->name(clienttranslate("Madagascard Island"))
                ->score(0)
                ->sunny()->wet()
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_DRAW_CARD_FROM_DECK, 9)->pay(ABILITY_COMPOST_FROM_HAND, 4)->gain(ABILITY_SOIL, 6)->build())
                ->ability((new AbilityBuilder())->brown()->gain(ABILITY_KEEP_CARDS_WHEN_PLANTING, 2)
                    ->desc(clienttranslate('When you choose the green action, keep 2 cards instead of 1'))->build())
                ->abundance()->build(),
            // Climate
            (new CardDefBuilder())->climateId(305, 1)->name(clienttranslate("Temperate Hot Summer Climate"))
                ->score(2)
                ->sunny()->wet()
                ->ability((new AbilityBuilder())->yellow()->gain(ABILITY_GROWTH, 1)->condition(AB_COND_PER_NEIGHBOUR, 1)->condition(AB_COND_PER_GERMINATE, GERMINATE_GROWTH_SCORE_5_OR_MORE)
                    ->desc(clienttranslate("Gain 1 growth per Flora with a growth score value of 5+ owned by one of your neighbours"))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->climateId(305, 2)->name(clienttranslate("Cold Winter Continental Climate"))
                ->score(3)
                ->wet()->rocky()->cold()
                ->ability((new AbilityBuilder())->red()->gain(ABILITY_COMPOST_FROM_DECK, 1)->condition(AB_COND_PER_NEIGHBOUR, 1)->condition(AB_COND_PER_COLOR, AB_COLOR_BROWN)
                    ->desc(clienttranslate("Compost 1 card from the deck per brown abilities owned by one of your neighbours"))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->climateId(306, 1)->name(clienttranslate("Hot Steppe Climate"))
                ->score(4)
                ->sunny()->wet()
                ->ability((new AbilityBuilder())->blue()->gain(ABILITY_SPROUT, 1)->condition(AB_COND_PER_NEIGHBOUR, 1)->condition(AB_COND_PER_GERMINATE, GERMINATE_SPROUT_SPACE_EXACTLY_6)
                    ->desc(clienttranslate("Gain 1 sprout per Flora with 6 sprout spaces owned by one of your neighbours"))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->climateId(306, 2)->name(clienttranslate("Cold Arid Desert Climate"))
                ->score(1)
                ->rocky()->cold()
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_SEED, 2)->build())
                ->abundance()->build(),
            // Fauna
            (new CardDefBuilder())->faunaId(360, 1)->name(clienttranslate('Margay'), 'Leopardus wiedii')
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_GERMINATE_CONDITION, 6, GERMINATE_CARD_ABILITY_ICON_GROWTH])
                    ->desc(clienttranslate('6 or more cards with Growth ability (including Island and Climate)'))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->faunaId(360, 2)->name(clienttranslate('Praying Mantis'), 'Mantis religiosa')
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_GERMINATE_CONDITION, 5, GERMINATE_CARD_ABILITY_ICON_DRAW])
                    ->desc(clienttranslate('5 or more cards with Draw ability (including Island and Climate)'))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->faunaId(361, 1)->name(clienttranslate('Hedgehog'), 'Erinaceinae')
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_GERMINATE_CONDITION, 7, GERMINATE_CARD_ABILITY_ICON_SPROUT])
                    ->desc(clienttranslate('7 or more cards with Sprout ability (including Island and Climate)'))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->faunaId(361, 2)->name(clienttranslate('Dung Beetle'), 'Scarabaeinae')
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_GERMINATE_CONDITION, 7, GERMINATE_CARD_ABILITY_ICON_COMPOST])
                    ->desc(clienttranslate('7 or more cards with Compost ability (including Island and Climate)'))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->faunaId(362, 1)->name(clienttranslate('Hippopotamus'), 'Hippopotamus amphibius')
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_GERMINATE_CONDITION, 8, GERMINATE_CARD_ABILITY_ICON_SOIL])
                    ->desc(clienttranslate('8 or more cards with Soil ability (including Island and Climate)'))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->faunaId(362, 2)->name(clienttranslate('Yellow Cheeked Gibbon'), 'Nomascus gabriellae')
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_GERMINATE_CONDITION, 7, GERMINATE_CARD_ABILITY_ICON_COLON])
                    ->desc(clienttranslate('7 or more cards with : (colon) ability (including Island and Climate)'))->build())
                ->abundance()->build(),
            // Ecosystem
            (new CardDefBuilder())->ecosystemId(337, 1)->name(clienttranslate('Miyawaki Forest Ecosystem'))
                ->ability((new AbilityBuilder())->scores([AB_ECO_GERMINATE_CONDITION, 5, 2, GERMINATE_CARD_ABILITY_ICON_GROWTH])
                    ->desc(clienttranslate('Score 5 points per 2 cards with Growth ability (including Island and Climate)'))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->ecosystemId(337, 2)->name(clienttranslate('Quiver Tree Forest Ecosystem'))
                ->ability((new AbilityBuilder())->scores([AB_ECO_GERMINATE_CONDITION, 5, 2, GERMINATE_CARD_ABILITY_ICON_DRAW])
                    ->desc(clienttranslate('Score 5 points per 2 cards with Draw ability (including Island and Climate)'))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->ecosystemId(338, 1)->name(clienttranslate('Khao Yai Ecosystem'))
                ->ability((new AbilityBuilder())->scores([AB_ECO_GERMINATE_CONDITION, 2, 1, GERMINATE_CARD_ABILITY_ICON_SPROUT])
                    ->desc(clienttranslate('Score 2 points per card with Sprout ability (including Island and Climate)'))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->ecosystemId(338, 2)->name(clienttranslate('St-Hilaire Mount Ecosystem'))
                ->ability((new AbilityBuilder())->scores([AB_ECO_GERMINATE_CONDITION, 2, 1, GERMINATE_CARD_ABILITY_ICON_COMPOST])
                    ->desc(clienttranslate('Score 2 points per card with Compost ability (including Island and Climate)'))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->ecosystemId(339, 1)->name(clienttranslate('Magdelen Islands Ecosystem'))
                ->ability((new AbilityBuilder())->scores([AB_ECO_GERMINATE_CONDITION, 4, 2, GERMINATE_CARD_ABILITY_ICON_SOIL])
                    ->desc(clienttranslate('Score 4 points per 2 cards with Soil ability (including Island and Climate)'))->build())
                ->abundance()->build(),
            (new CardDefBuilder())->ecosystemId(339, 2)->name(clienttranslate('Okefenokee Swamp Ecosystem'))
                ->ability((new AbilityBuilder())->scores([AB_ECO_GERMINATE_CONDITION, 2, 2, GERMINATE_CARD_ABILITY_ICON_COLON])
                    ->desc(clienttranslate('Score 2 points per 2 cards with : (colon) ability (including Island and Climate)'))->build())
                ->abundance()->build(),
        ];
    }
}
