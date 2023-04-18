{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- earth implementation : © Guillaume Benny bennygui@gmail.com
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    earth_earth.tpl
-->
<div id="ea-display-last-round" class="bx-hidden">
    <div>{DISPLAY_LAST_ROUND}</div>
</div>

<div id="ea-scorepad-container" class="bx-hidden">
    <div id="ea-scorepad">
        <table id="ea-scorepad-table">
            <thead>
                <tr>
                    <td></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="ea-scorepad-icon-container">
                            <div class="ea-icon-scoring"></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="ea-scorepad-icon-container">
                            <div class="ea-icon-card-type-event"></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="ea-scorepad-icon-container">
                            1x <div class="ea-icon-compost-destroy"></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="ea-scorepad-icon-container">
                            1x <div class="ea-icon-sprout"></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="ea-scorepad-icon-container">
                            1x <div class="ea-icon-growth"></div>
                            <br/>
                            / <div class="ea-icon-growth-scoring"></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="ea-scorepad-icon-container">
                            <div class="ea-icon-card-type-terrain"></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="ea-scorepad-icon-container">
                            <div class="ea-icon-card-type-ecosystem"></div>
                            <i class="fa fa-user" aria-hidden="true"></i>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="ea-scorepad-icon-container">
                            <div class="ea-icon-card-type-ecosystem"></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="ea-scorepad-icon-container">
                            <div class="ea-icon-card-type-ecosystem"></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="ea-scorepad-icon-container">
                            <div class="ea-icon-card-type-fauna"></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="ea-scorepad-icon-container">
                            &sum;
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div id='ea-area-full'>
    <div id='ea-area-card-hand-container' class='ea-config-closed'>
        <div id='ea-area-card-hand-config'>
            <input id='ea-card-hand-slider' type='range' min='35' max='100' step='5' value='50' />
            <div class="ea-ui-button ea-ui-icon-hand-fixed" id="ea-card-hand-button-fixed"></div>
            <div class="ea-ui-button ea-ui-icon-hand-above" id="ea-card-hand-button-above"></div>
            <div class="ea-ui-button ea-ui-icon-hand-below" id="ea-card-hand-button-below"></div>
        </div>
        <div id='ea-area-card-hand-config-control'>
            <div id='ea-area-card-hand-config-controller'><i class="fa fa-caret-down"></i></div>
        </div>
        <div id='ea-area-card-hand'></div>
    </div>
    <div id='ea-area-common'>
        <div id='ea-area-fauna-board'>
            <div id='ea-fauna-board'>
                 <div class='ea-snow-container'>
                     <div class='ea-snow'></div><div class='ea-snow'></div><div class='ea-snow'></div><div class='ea-snow'></div><div class='ea-snow'></div>
                     <div class='ea-snow'></div><div class='ea-snow'></div><div class='ea-snow'></div><div class='ea-snow'></div><div class='ea-snow'></div>
                     <div class='ea-snow'></div><div class='ea-snow'></div><div class='ea-snow'></div><div class='ea-snow'></div><div class='ea-snow'></div>
                     <div class='ea-snow'></div><div class='ea-snow'></div><div class='ea-snow'></div><div class='ea-snow'></div><div class='ea-snow'></div>
                     <div class='ea-snow'></div><div class='ea-snow'></div><div class='ea-snow'></div><div class='ea-snow'></div><div class='ea-snow'></div>
                     <div class='ea-snow'></div><div class='ea-snow'></div><div class='ea-snow'></div><div class='ea-snow'></div><div class='ea-snow'></div>
                     <div class='ea-snow'></div><div class='ea-snow'></div><div class='ea-snow'></div><div class='ea-snow'></div><div class='ea-snow'></div>
                     <div class='ea-snow'></div><div class='ea-snow'></div><div class='ea-snow'></div><div class='ea-snow'></div><div class='ea-snow'></div>
                     <div class='ea-snow'></div><div class='ea-snow'></div><div class='ea-snow'></div><div class='ea-snow'></div><div class='ea-snow'></div>
                     <div class='ea-snow'></div><div class='ea-snow'></div><div class='ea-snow'></div><div class='ea-snow'></div><div class='ea-snow'></div>
                     <div class='ea-snow'></div><div class='ea-snow'></div><div class='ea-snow'></div><div class='ea-snow'></div><div class='ea-snow'></div>
                 </div>

                <div id='ea-fauna-board-fauna-card-0-0'></div>
                <div id='ea-fauna-board-fauna-card-0-1'></div>
                <div id='ea-fauna-board-fauna-card-1-0'></div>
                <div id='ea-fauna-board-fauna-card-1-1'></div>
                <div id='ea-fauna-board-ecosystem-card-0'></div>
                <div id='ea-fauna-board-ecosystem-card-1'></div>

                <div class='ea-fauna-board-fauna-leaf-spot' id='ea-fauna-board-fauna-leaf-0-0-wait'></div>
                <div class='ea-fauna-board-fauna-leaf-spot' id='ea-fauna-board-fauna-leaf-0-0-0'></div>
                <div class='ea-fauna-board-fauna-leaf-spot' id='ea-fauna-board-fauna-leaf-0-0-1'></div>
                <div class='ea-fauna-board-fauna-leaf-spot' id='ea-fauna-board-fauna-leaf-0-0-2'></div>
                <div class='ea-fauna-board-fauna-leaf-spot' id='ea-fauna-board-fauna-leaf-0-0-3'></div>
                <div class='ea-fauna-board-fauna-leaf-spot' id='ea-fauna-board-fauna-leaf-0-0-4'></div>

                <div class='ea-fauna-board-fauna-leaf-spot' id='ea-fauna-board-fauna-leaf-0-1-wait'></div>
                <div class='ea-fauna-board-fauna-leaf-spot' id='ea-fauna-board-fauna-leaf-0-1-0'></div>
                <div class='ea-fauna-board-fauna-leaf-spot' id='ea-fauna-board-fauna-leaf-0-1-1'></div>
                <div class='ea-fauna-board-fauna-leaf-spot' id='ea-fauna-board-fauna-leaf-0-1-2'></div>
                <div class='ea-fauna-board-fauna-leaf-spot' id='ea-fauna-board-fauna-leaf-0-1-3'></div>
                <div class='ea-fauna-board-fauna-leaf-spot' id='ea-fauna-board-fauna-leaf-0-1-4'></div>

                <div class='ea-fauna-board-fauna-leaf-spot' id='ea-fauna-board-fauna-leaf-1-0-wait'></div>
                <div class='ea-fauna-board-fauna-leaf-spot' id='ea-fauna-board-fauna-leaf-1-0-0'></div>
                <div class='ea-fauna-board-fauna-leaf-spot' id='ea-fauna-board-fauna-leaf-1-0-1'></div>
                <div class='ea-fauna-board-fauna-leaf-spot' id='ea-fauna-board-fauna-leaf-1-0-2'></div>
                <div class='ea-fauna-board-fauna-leaf-spot' id='ea-fauna-board-fauna-leaf-1-0-3'></div>
                <div class='ea-fauna-board-fauna-leaf-spot' id='ea-fauna-board-fauna-leaf-1-0-4'></div>

                <div class='ea-fauna-board-fauna-leaf-spot' id='ea-fauna-board-fauna-leaf-1-1-wait'></div>
                <div class='ea-fauna-board-fauna-leaf-spot' id='ea-fauna-board-fauna-leaf-1-1-0'></div>
                <div class='ea-fauna-board-fauna-leaf-spot' id='ea-fauna-board-fauna-leaf-1-1-1'></div>
                <div class='ea-fauna-board-fauna-leaf-spot' id='ea-fauna-board-fauna-leaf-1-1-2'></div>
                <div class='ea-fauna-board-fauna-leaf-spot' id='ea-fauna-board-fauna-leaf-1-1-3'></div>
                <div class='ea-fauna-board-fauna-leaf-spot' id='ea-fauna-board-fauna-leaf-1-1-4'></div>
                
                <div id='ea-fauna-board-fauna-leaf-tableau-bonus'></div>
                
                <div id='ea-fauna-board-fauna-progress-button'>
                    <div class="ea-icon-card-type-fauna"></div>
                    <span>{PROGRESS}</span>
                </div>
            </div>
        </div>
        <div id='ea-area-deck-discard'>
            <div id='ea-area-deck'>
                <div id='ea-area-deck-cards'></div>
                <div id='ea-deck-count' class='ea-counter'><span>{DECK}&nbsp;</span><span id='ea-deck-count-number'>0</span></div>
            </div>
            <div id='ea-area-discard'>
                <div id='ea-area-discard-cards'></div>
                <div id='ea-discard-count' class='ea-counter'><span>{DISCARD}&nbsp;</span><span id='ea-discard-count-number'>0</span></div>
            </div>
        </div>
        <div id='ea-area-gaia-board'>
            <div id='ea-gaia-board'>
                <div id='ea-gaia-board-sprout-count' class='ea-counter'>0</div>
                <div id='ea-gaia-board-growth-count' class='ea-counter'>0/0</div>

                <div id='ea-gaia-board-leaf-board-0'></div>
                <div id='ea-gaia-board-leaf-board-1'></div>
                <div id='ea-gaia-board-leaf-board-2'></div>
                <div id='ea-gaia-board-leaf-board-3'></div>
                <div id='ea-gaia-board-leaf-board-4'></div>
                <div id='ea-gaia-board-leaf-board-5'></div>

                <div id='ea-gaia-soil-count' class='ea-counter'>0</div>
                <div id='ea-gaia-soil-box'></div>

                <div id='ea-gaia-board-compost'>
                    <div id='ea-gaia-board-compost-cards'></div>
                    <div id='ea-gaia-board-compost-count' class='ea-counter'>0</div>
                </div>

                <div id='ea-gaia-board-tableau'>
                    <div id='ea-gaia-board-tableau-cards'></div>
                    <div id='ea-gaia-board-tableau-count' class='ea-counter'>0</div>
                    <div id='ea-gaia-board-tableau-help' class='ea-gaia-board-help ea-card-help'></div>
                </div>
                
                <div id='ea-gaia-board-round-count' class='ea-counter'>0/12</div>

                <div id='ea-gaia-board-deck'>
                    <div id='ea-gaia-board-deck-cards'></div>
                    <div id='ea-gaia-board-deck-count' class='ea-counter'>0</div>
                </div>

                <div id='ea-gaia-board-gaia-card'>
                    <div id='ea-gaia-board-gaia-card-cards'></div>
                    <div id='ea-gaia-board-gaia-card-count' class='ea-counter'>0</div>
                    <div id='ea-gaia-board-gaia-card-help' class='ea-gaia-board-help ea-card-help'></div>
                </div>
            </div>
        </div>
    </div>
    <!-- BEGIN player-area -->
    <div id='ea-area-player-{PLAYER_ID}' class='ea-area-player' data-player-id='{PLAYER_ID}'>
        <div class='ea-area-player-title'>
            <h3 class='player-name' style='color: #{PLAYER_COLOR};'>{PLAYER_NAME}</h3>
        </div>
        <div class='ea-area-player-zone'>
            <div class='ea-area-player-tableau-container'>
                <div class='ea-area-player-tableau'>
                    <svg class='ea-scoring-lines' version='1.1' xmlns='http://www.w3.org/2000/svg'>
                        <path d='' />
                    </svg>
                </div>
            </div>
            <div class='ea-area-player-board'>
                <div class='ea-token-active-player ea-player-board-active-player-action-0 ea-token-inactive'></div>
                <div class='ea-token-active-player ea-player-board-active-player-action-1 ea-token-inactive'></div>
                <div class='ea-token-active-player ea-player-board-active-player-action-2 ea-token-inactive'></div>
                <div class='ea-token-active-player ea-player-board-active-player-action-3 ea-token-inactive'></div>
                <div class='ea-player-board'>
                    <div class='ea-bird-container'>
                        <div class='ea-bird' data-bird-index="0"></div>
                        <div class='ea-bird' data-bird-index="1"></div>
                        <div class='ea-bird' data-bird-index="2"></div>
                    </div>

                    <div class='ea-player-board-solo-easy bx-hidden'></div>
                    <div class='ea-player-board-solo-hard bx-hidden'></div>

                    <div class='ea-player-board-leaf-board-0'></div>
                    <div class='ea-player-board-leaf-board-1'></div>
                    <div class='ea-player-board-leaf-board-2'></div>
                    <div class='ea-player-board-leaf-board-3'></div>
                    <div class='ea-player-board-leaf-board-4'></div>
                    <div class='ea-player-board-leaf-board-5'></div>

                    <div class='ea-player-board-leaf-action-0'></div>
                    <div class='ea-player-board-leaf-action-1'></div>
                    <div class='ea-player-board-leaf-action-2'></div>
                    <div class='ea-player-board-leaf-action-3'></div>

                    <div class='ea-player-board-action-0'>
                        <div class='ea-player-board-action-colorblind'><div class='ea-colorblind-green'></div></div>
                    </div>
                    <div class='ea-player-board-action-1'>
                        <div class='ea-player-board-action-colorblind'><div class='ea-colorblind-red'></div></div>
                    </div>
                    <div class='ea-player-board-action-2'>
                        <div class='ea-player-board-action-colorblind'><div class='ea-colorblind-blue'></div></div>
                    </div>
                    <div class='ea-player-board-action-3'>
                        <div class='ea-player-board-action-colorblind'><div class='ea-colorblind-yellow'></div></div>
                    </div>

                    <div class='ea-player-soil-count ea-counter'>0</div>
                    <div class='ea-player-soil-box'></div>
                    <div class='ea-player-soil-conversion-box'></div>

                    <div class='ea-player-board-card ea-player-board-card-0'></div>
                    <div class='ea-player-board-card ea-player-board-card-1'></div>
                    <div class='ea-player-board-card ea-player-board-card-2'></div>

                    <div class='ea-player-board-compost'>
                        <div class='ea-player-board-compost-cards'></div>
                        <div class='ea-player-board-compost-count ea-counter'>0</div>
                    </div>

                    <div class='ea-player-board-event'>
                        <div class='ea-player-board-event-cards'></div>
                        <div class='ea-player-board-event-count ea-counter'>0</div>
                        <div class="ea-player-board-event-help ea-card-help"></div>
                    </div>
                </div>
                <details>
                    <summary><p class='ea-show-icon-help'>{SHOW_ICON_HELP}</p><p class='ea-hide-icon-help'>{HIDE_ICON_HELP}</p></summary>
                    <div class='ea-player-board-bottom'>
                        <div class='ea-player-board-bottom-resources'></div>
                        <div class='ea-player-board-bottom-endgame'></div>

                        <div class='ea-player-board-bottom-sprout'></div>
                        <div class='ea-player-board-bottom-compost'></div>
                        <div class='ea-player-board-bottom-growth'></div>
                        <div class='ea-player-board-bottom-draw'></div>
                        <div class='ea-player-board-bottom-plant'></div>
                        <div class='ea-player-board-bottom-spend'></div>

                        <div class='ea-player-board-bottom-score-base'></div>
                        <div class='ea-player-board-bottom-score-event'></div>
                        <div class='ea-player-board-bottom-score-compost'></div>
                        <div class='ea-player-board-bottom-score-sprout'></div>
                        <div class='ea-player-board-bottom-score-growth'></div>
                        <div class='ea-player-board-bottom-score-terrain'></div>
                        <div class='ea-player-board-bottom-score-ecosystem'></div>
                        <div class='ea-player-board-bottom-score-fauna'></div>
                    </div>
                </details>
            </div>
        </div>
    </div>
    <!-- END player-area -->
</div>
<div id='ea-shortcut-area'></div>
<div id='ea-element-creation' class='bx-hidden'></div>

{OVERALL_GAME_FOOTER}