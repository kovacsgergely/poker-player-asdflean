<?php

class Player
{
    const VERSION = "AsdfLEAN 1.0";

    public function betRequest($game_state)
    {
    	$holeCards = array();
    	$stack = 1000;
    	$myPlayer = $this->getMyPlayer($game_state);
    	if ($myPlayer) {
    		$holeCards = $myPlayer['hole_cards'];
    		$stack = $myPlayer['stack'];
    	}
    	foreach ($holeCards as $card) {
    		if (!in_array($card['rank'], array(2, 3, 4, 5, 6, 7, 8, 9, 10))) {
    			return $stack;
    		}
    	}
    	
        return 0;
    }

    public function showdown($game_state)
    {
    }
    
    public function getMyPlayer($game_state)
	{
		$retval = array();
		foreach ($game_state['players'] as $player) {
    		if (isset($player['hole_cards']) && !empty($player['hole_cards'])) {
				$retval = $player;
				break;
			}
		}
			
		return $retval;
    }
    
    public function isFigure($cards)
	{
		if (!in_array($card['rank'], array(2, 3, 4, 5, 6, 7, 8, 9, 10))) {
			return true;
		}
		
		return false;
	}
}
