<?php

class User {
    private $hand = [];
    
    public function __construct()
    {
        
    }
    
    public function receive($card = false)
    {
        if ($card === false) {
            throw new Exception('No card was provided');
        }

        array_push($this->hand, $card);
    }
    
    public function play()
    {
        if (count($this->hand) == 0) {
            throw new OutOfCardsException;
        }

        return array_shift($this->hand);
    }
    
    public function hasCards()
    {
        return count($this->hand) > 0;
    }
    
    public function cardCount()
    {
        return count($this->hand);
    }
    
    public function __toString()
    {
        return implode(',', $this->hand);
    }
}

class OutOfCardsException extends \Exception {
    
}