<?php

class Player
{
    const VERSION = "AsdfLEAN 1.0";

    public function betRequest($game_state)
    {
    	$holeCards = array();
    	$stack = 1000;
    	foreach ($game_state['players'] as $player) {
    		if (isset($player['hole_cards']) && !empty($player['hole_cards'])) {
    			$holeCards = $player['hole_cards'];
    			$stack = $player['stack'];
    		}
    	}
    	foreach ($holeCards as $card) {
    		if (!in_array($card['rank'], array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9))) {
    			return $stack;
    		}
    	}
    	
        return 0;
    }

    public function showdown($game_state)
    {
    }
}
