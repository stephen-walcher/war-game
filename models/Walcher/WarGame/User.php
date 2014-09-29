<?php namespace Walcher\WarGame;

use Walcher\CardGame;

class User implements \Walcher\CardGame\User {
    private $hand;

    /**
     * Instantiate holder variable for a user's hand of cards
     */
    public function __construct()
    {
        // Instantiate our holder variable with the desired type.
        // Function can be overwritten
        $this->hand = [];
    }

    /**
     * Validates card object and add to user's hand
     *
     * @param Card  $card   Card object dealt to user by game
     */
    public function receive($card = false)
    {
        if ($card === false) {
            throw new Exception('No card was provided');
        }

        array_push($this->hand, $card);
    }

    /**
     * Play a card from the user's hand
     *
     * @return  Card    Card object from user's hand to play
     */
    public function play()
    {
        if (count($this->hand) == 0) {
            throw new OutOfCardsException;
        }

        return array_shift($this->hand);
    }

    /**
     * Checks if a user still has any cards left in their hand
     *
     * @return boolean
     */
    public function hasCards()
    {
        return count($this->hand) > 0;
    }

    /**
     * Gets the current number of cards a user has in their hand
     *
     * @return  integer Number of cards in hand
     */
    public function cardCount()
    {
        return count($this->hand);
    }
}

class OutOfCardsException extends \Exception {
    protected $message = 'This user is out of cards to play!';
}