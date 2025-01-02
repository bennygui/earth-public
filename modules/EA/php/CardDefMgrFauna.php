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

trait CardDefMgrFauna
{
    private static function getCardDefFauna()
    {
        return [
            // 337
            (new CardDefBuilder())->faunaId(337, 1)->name(clienttranslate("Bald Eagle"), "Haliaeetus leucocephalus")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_FLORA_WITH_PIECES, 4, ABILITY_GROWTH, 3])
                    ->desc(clienttranslate('4 Flora cards or more, each with 3 growth or more'))->build())
                ->build(),
            (new CardDefBuilder())->faunaId(337, 2)->name(clienttranslate("Pale-Billed Woodpecker"), "Campephilus guatemalensis")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_FLORA_WITH_PIECES, 7, ABILITY_GROWTH, 1])
                    ->desc(clienttranslate('7 Flora cards or more, each with 1 growth or more'))->build())
                ->build(),
            // 338
            (new CardDefBuilder())->faunaId(338, 1)->name(clienttranslate("Yellow-Bellied Marmot"), "Marmota flavisentris")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_FLORA_WITH_PIECES, 6, ABILITY_SPROUT, 4])
                    ->desc(clienttranslate('6 Flora cards or more, each with 4 sprouts or more'))->build())
                ->build(),
            (new CardDefBuilder())->faunaId(338, 2)->name(clienttranslate("Red Squirrel"), "Sciurus vulgaris")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_FLORA_WITH_PIECES, 9, ABILITY_SPROUT, 1])
                    ->desc(clienttranslate('9 Flora cards or more, each with 1 sprout or more'))->build())
                ->build(),
            // 339
            (new CardDefBuilder())->faunaId(339, 1)->name(clienttranslate("European Mole"), "Talpa europaea")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_SOIL_COUNT, 20])
                    ->desc(clienttranslate('20 soil or more in your reserve'))->build())
                ->build(),
            (new CardDefBuilder())->faunaId(339, 2)->name(clienttranslate("Earthworm"), "Lumbricus terrestris")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_COMPOST_COUNT, 15])
                    ->desc(clienttranslate('15 cards or more in your compost'))->build())
                ->build(),
            // 340
            (new CardDefBuilder())->faunaId(340, 1)->name(clienttranslate("Andean Condor"), "Vultur gryphus")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_HAND_COUNT, 20])
                    ->desc(clienttranslate('20 cards or more in your hand'))->build())
                ->build(),
            (new CardDefBuilder())->faunaId(340, 2)->name(clienttranslate("Green Tree Ant"), "Oecophylia smaragdina")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_EVENT_COUNT, 4])
                    ->desc(clienttranslate('4 event cards or more in your Event Stack'))->build())
                ->build(),
            // 341
            (new CardDefBuilder())->faunaId(341, 1)->name(clienttranslate("Mountain Lion"), "Puma concolor")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_CARDS_WITH_HABITAT, 8, CARD_HABITAT_ROCKY])
                    ->desc(clienttranslate('8 cards or more with a Rocky habitat (including Island and Climate)'))->build())
                ->build(),
            (new CardDefBuilder())->faunaId(341, 2)->name(clienttranslate("Plains Zebra"), "Equus quagga")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_CARDS_WITH_HABITAT, 8, CARD_HABITAT_SUNNY])
                    ->desc(clienttranslate('8 cards or more with a Sunny habitat (including Island and Climate)'))->build())
                ->build(),
            // 342
            (new CardDefBuilder())->faunaId(342, 1)->name(clienttranslate("American Alligator"), "Alligator mississippiensis")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_CARDS_WITH_HABITAT, 8, CARD_HABITAT_WET])
                    ->desc(clienttranslate('8 cards or more with a Wet habitat (including Island and Climate)'))->build())
                ->build(),
            (new CardDefBuilder())->faunaId(342, 2)->name(clienttranslate("Arctic Fox"), "Vulpes lagopus")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_CARDS_WITH_HABITAT, 8, CARD_HABITAT_COLD])
                    ->desc(clienttranslate('8 cards or more with a Cold habitat (including Island and Climate)'))->build())
                ->build(),
            // 343
            (new CardDefBuilder())->faunaId(343, 1)->name(clienttranslate("Bornean Orangutan"), "Pongo pygmaeus")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_FLORA_WITH_TYPE, 4, CARD_TYPE_TREE])
                    ->desc(clienttranslate('4 or more Tree'))->build())
                ->build(),
            (new CardDefBuilder())->faunaId(343, 2)->name(clienttranslate("Siberian Tiger"), "Panthera tigris altaica")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_FLORA_WITH_TYPE, 4, CARD_TYPE_BUSH])
                    ->desc(clienttranslate('4 or more Bush'))->build())
                ->build(),
            // 344
            (new CardDefBuilder())->faunaId(344, 1)->name(clienttranslate("Lubber Grasshopper"), "Chromacris speciosa")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_FLORA_WITH_TYPE, 4, CARD_TYPE_HERB])
                    ->desc(clienttranslate('4 or more Herb'))->build())
                ->build(),
            (new CardDefBuilder())->faunaId(344, 2)->name(clienttranslate("Wild Boar"), "Sus scrofa")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_FLORA_WITH_TYPE, 4, CARD_TYPE_MUSHROOM])
                    ->desc(clienttranslate('4 or more Mushroom'))->build())
                ->build(),
            // 345
            (new CardDefBuilder())->faunaId(345, 1)->name(clienttranslate("Echidna"), "Tachyglossus aculeatus")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_CARDS_WITH_ABILITY_COLOR, 6, AB_COLOR_RED])
                    ->desc(clienttranslate('6 cards or more with Red abilities (including Island and Climate)'))->build())
                ->build(),
            (new CardDefBuilder())->faunaId(345, 2)->name(clienttranslate("Kingfisher"), "Alcedinidae")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_CARDS_WITH_ABILITY_COLOR, 6, AB_COLOR_BLUE])
                    ->desc(clienttranslate('6 cards or more with Blue abilities (including Island and Climate)'))->build())
                ->build(),
            // 346
            (new CardDefBuilder())->faunaId(346, 1)->name(clienttranslate("Green Iguana"), "Iguana iguana")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_CARDS_WITH_ABILITY_COLOR, 6, AB_COLOR_YELLOW])
                    ->desc(clienttranslate('6 cards or more with Yellow abilities (including Island and Climate)'))->build())
                ->build(),
            (new CardDefBuilder())->faunaId(346, 2)->name(clienttranslate("Spotted Hyena"), "Crocuta crocuta")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_CARDS_WITH_ABILITY_COLOR, 3, AB_COLOR_BLACK])
                    ->desc(clienttranslate('3 cards or more with Black abilities (including Island and Climate)'))->build())
                ->build(),
            // 347
            (new CardDefBuilder())->faunaId(347, 1)->name(clienttranslate("Northern Giraffe"), "Giraffa camelopardalis")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_COLUMNS, 2])
                    ->desc(clienttranslate('Fill 2 or more columns in your island (tableau)'))->build())
                ->build(),
            (new CardDefBuilder())->faunaId(347, 2)->name(clienttranslate("Cairns Birdwing Butterfly"), "Ornithoptera euphorion")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_ROWS, 2])
                    ->desc(clienttranslate('Fill 2 or more rows in your island (tableau)'))->build())
                ->build(),
            // 348
            (new CardDefBuilder())->faunaId(348, 1)->name(clienttranslate("Emerald Tree Boa"), "Corallus caninus")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_DIAGONALS, 4])
                    ->desc(clienttranslate('Fill both 4 card diagonals in your island'))
                    ->faq(clienttranslate("You need 8 cards to fill the 2 diagonals of your tableau (in an X pattern)."))
                    ->build())
                ->build(),
            (new CardDefBuilder())->faunaId(348, 2)->name(clienttranslate("King Penguin"), "Aptenodytes patagonicus")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_WITH_DIRECTIONS, 4])
                    ->desc(clienttranslate('4 or more cards with directional aids (arrows)'))->build())
                ->build(),
            // 349
            (new CardDefBuilder())->faunaId(349, 1)->name(clienttranslate("Wood Duck"), "Aix sponsa")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_CARDS_WITH_LESS_HABITAT, 7, 1])
                    ->desc(clienttranslate('7 or more cards, each with 1 or zero Habitats (Sunny, Wet, Rocky, Cold)'))->build())
                ->build(),
            (new CardDefBuilder())->faunaId(349, 2)->name(clienttranslate("Siamese Rhinoceros Beetle"), "Xylotrupes socrates")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_CARDS_WITH_MORE_HABITAT, 10, 2])
                    ->desc(clienttranslate('10 or more cards, each with 2 or more Habitats (Sunny, Wet, Rocky, Cold)'))->build())
                ->build(),
            // 350
            (new CardDefBuilder())->faunaId(350, 1)->name(clienttranslate("Grey Wolf"), "Canis lupus")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_CARDS_WITH_MORE_SCORE, 6, 4])
                    ->desc(clienttranslate('6 or more cards, each with a score value of 4 or more (including Island and Climate)'))->build())
                ->build(),
            (new CardDefBuilder())->faunaId(350, 2)->name(clienttranslate("Seven-Spotted Ladybug"), "Harmonia axyridis")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_CARDS_WITH_LESS_SCORE, 11, 3])
                    ->desc(clienttranslate('11 or more cards, each with a score value of 3 or less (including Island and Climate)'))->build())
                ->build(),
            // 351
            (new CardDefBuilder())->faunaId(351, 1)->name(clienttranslate("African Bush Elephant"), "Loxodonta africana")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_CARDS_WITH_MORE_COST, 6, 4])
                    ->desc(clienttranslate('6 or more cards, each with a soil cost of 4 or more'))->build())
                ->build(),
            (new CardDefBuilder())->faunaId(351, 2)->name(clienttranslate("Western Honeybee"), "Apis mellifera")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_CARDS_WITH_LESS_COST, 9, 3])
                    ->desc(clienttranslate('9 or more cards, each with a soil cost of 3 or less'))->build())
                ->build(),
            // 352
            (new CardDefBuilder())->faunaId(352, 1)->name(clienttranslate("Fire Salamander"), "Salamandra salamandra")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_CARDS_SETS, 2, 5])
                    ->desc(clienttranslate('2 or more sets of 5 cards including Red, Blue, Yellow, Terrain and Event (including Island and Climate) (each on a different card)'))
                    ->faq(clienttranslate("To get this objective, you must have at least 2 Red, 2 Blue, 2 Yellow, 2 Terrains and 2 Events. Each must be from a different card. Multicolored cards can count for one (and only one) of the colors."))
                    ->build())
                ->build(),
            (new CardDefBuilder())->faunaId(352, 2)->name(clienttranslate("Panther Chameleon"), "Furcifer pardalis")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_CARDS_WITH_MORE_ABILITY, 5, 2])
                    ->desc(clienttranslate('5 or more cards, each having 2 different abilities (including Island and Climate)'))->build())
                ->build(),
            // 353
            (new CardDefBuilder())->faunaId(353, 1)->name(clienttranslate("Rainbow Shield Bug"), "Calidea dregii")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_CARDS_WITH_ABILITY_COLOR, 2, AB_COLOR_MULTICOLOR])
                    ->desc(clienttranslate('2 or more cards with multicolored abilities'))->build())
                ->build(),
            (new CardDefBuilder())->faunaId(353, 2)->name(clienttranslate("Arctic Tern"), "Sterna paradisaea")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_FLORA_WITH_BOLD_GEOGRAPHY, 3])
                    ->desc(clienttranslate('3 or more Flora with a geographic term in their name (in bold)'))
                    ->faq(clienttranslate("Some compromise had to be made for balance issues so not all geographic term may be bold. Only bold terms can be counted toward this objective."))
                    ->build())
                ->build(),
            // 354
            (new CardDefBuilder())->faunaId(354, 1)->name(clienttranslate("Barn Owl"), "Tyto alba")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_FLORA_WITH_UNDERLINE_ANIMAL, 3])
                    ->desc(clienttranslate('3 or more Flora with an animal in their name (underlined)'))
                    ->faq(clienttranslate("Some compromise had to be made for balance issues so not all animals may be underlined. Only underlined animals can be counted toward this objective."))
                    ->build())
                ->build(),
            (new CardDefBuilder())->faunaId(354, 2)->name(clienttranslate("Talamanca Hummingbird"), "Eugenes spectabilis")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_FLORA_WITH_ITALIC_COLOR, 3])
                    ->desc(clienttranslate('3 or more Flora with a color in their name (in italic)'))
                    ->faq(clienttranslate("Some compromise had to be made for balance issues so not all colors may be italic. Only italic colors can be counted toward this objective."))
                    ->build())
                ->build(),
            // 355
            (new CardDefBuilder())->faunaId(355, 1)->name(clienttranslate("Red-Eyed Tree Frog"), "Agalychnis callidryas")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_CARDS_WITH_ABILITY_COLOR, 3, AB_COLOR_GREEN])
                    ->desc(clienttranslate('3 or more cards with Green abilities (includes Island)'))->build())
                ->build(),
            (new CardDefBuilder())->faunaId(355, 2)->name(clienttranslate("Brown Bear"), "Ursus arctos")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_CARDS_WITH_ABILITY_COLOR, 5, AB_COLOR_BROWN])
                    ->desc(clienttranslate('5 or more cards with Brown abilities (includes Island)'))->build())
                ->build(),
            // 356
            (new CardDefBuilder())->faunaId(356, 1)->name(clienttranslate("American Bison"), "Bison bison")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_FLORA_WITH_EXACT_PIECE_SPOT, 4, 6, ABILITY_SPROUT])
                    ->desc(clienttranslate('4 or more Flora, each with 6 sprout spaces'))
                    ->faq(clienttranslate("You do not need sprout pieces to claim this objective."))
                    ->build())
                ->build(),
            (new CardDefBuilder())->faunaId(356, 2)->name(clienttranslate("Red Deer"), "Cervus elaphus")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_FLORA_WITH_LESS_PIECE_SPOT, 6, 3, ABILITY_SPROUT])
                    ->desc(clienttranslate('6 or more Flora, each with 3 sprout spaces or less'))
                    ->faq(clienttranslate("You do not need sprout pieces to claim this objective."))
                    ->build())
                ->build(),
            // 357
            (new CardDefBuilder())->faunaId(357, 1)->name(clienttranslate("Brown-Throated Sloth"), "Bradypus variegatus")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_FLORA_WITH_MORE_PIECE_SPOT, 4, 4, ABILITY_GROWTH])
                    ->desc(clienttranslate('4 or more Flora, each with 4 growth spaces or more'))
                    ->faq(clienttranslate("You do not need growth pieces to claim this objective."))
                    ->build())
                ->build(),
            (new CardDefBuilder())->faunaId(357, 2)->name(clienttranslate("Western Moose"), "Alces alces andersoni")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_FLORA_WITH_LESS_PIECE_SPOT, 7, 2, ABILITY_GROWTH])
                    ->desc(clienttranslate('7 or more Flora, each with 2 growth spaces or less'))
                    ->faq(clienttranslate("You do not need growth pieces to claim this objective."))
                    ->build())
                ->build(),
            // 358
            (new CardDefBuilder())->faunaId(358, 1)->name(clienttranslate("Mountain Gorilla"), "Gorilla beringei beringei")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_FLORA_FILLED_FIECES, 4])
                    ->desc(clienttranslate('4 or more Flora filled with as many sprouts and growth as possible (Having no sprout spaces or no growth spaces count as filled)'))->build())
                ->build(),
            (new CardDefBuilder())->faunaId(358, 2)->name(clienttranslate("Black Wildebeest"), "Connochaetes gnou")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_FLORA_EMPTY_FIECES, 8])
                    ->desc(clienttranslate('8 or more Flora without any growth or sprout'))->build())
                ->build(),
            // 359
            (new CardDefBuilder())->faunaId(359, 1)->name(clienttranslate("Sri Lankan Leopard"), "Panthera pardus")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_CARDS_WITH_ODD_SCORE, 7])
                    ->desc(clienttranslate('7 or more cards with an odd score value (0 counts as even)'))->build())
                ->build(),
            (new CardDefBuilder())->faunaId(359, 2)->name(clienttranslate("Atlantic Puffin"), "Fratercula arctica")
                ->ability((new AbilityBuilder())->scores([AB_FAUNA_CARDS_WITH_EVEN_SCORE, 10])
                    ->desc(clienttranslate('10 or more cards with an even score value (0 counts as even)'))->build())
                ->build(),
        ];
    }
}
