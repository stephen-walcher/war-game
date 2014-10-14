<?php namespace Walcher\WarGame;

use Walcher\CardGame;

class Deck implements \Walcher\CardGame\Deck {
    private $deck = [];
    private $suits = ['D', 'H', 'C', 'S'];
    private $values = ['2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K', 'A'];

    private $ranking =
        [
            '2' => 2,
            '3' => 3,
            '4' => 4,
            '5' => 5,
            '6' => 6,
            '7' => 7,
            '8' => 8,
            '9' => 9,
            '10' => 10,
            'J' => 11,
            'Q' => 12,
            'K' => 13,
            'A' => 14
        ];

    /**
     * Create and store the deck object based on the number of decks provided
     *
     * @param integer   $decks  Number of decks to create
     */
    public function __construct($decks = 1)
    {
        // Construct the deck based on given parameters
        for ($i = 0; $i < $decks; $i++) {
            foreach ($this->suits as $s) {
                foreach ($this->values as $v) {
                    array_push($this->deck, "{$v}|{$s}");
                }
            }
        }
    }

    /**
     * Shuffle all cards in the deck
     */
    public function shuffle()
    {
        shuffle($this->deck);
    }

    /**
     * Deal one card from the deck
     *
     * @return Card     Card object to be dealt to user
     *
     * @throws DeckEmptyException
     */
    public function deal()
    {
        if (count($this->deck) == 0) {
            throw new DeckEmptyException;
        }

        return array_shift($this->deck);
    }

    /**
     * Count the remaining cards in this deck
     *
     * @return integer  Number of cards remaining in the deck
     */
    public function count()
    {
        return count($this->deck);
    }

    /**
     * Compares a provided array of cards and returns the high-card winner
     * based on ranking rules provided in this class. If there is a tie, the
     * function will return 'WAR'.
     *
     * @param array $in_play    Array of Card objects to compare
     *
     * @return Array|string  Highest ranking Card object (with the User ID as the key) or "War" if there is a tie
     */
    public function compare($in_play = [])
    {
        if (count($in_play) == 0) {
            return false;
        }

        $high_card = [];

        foreach ($in_play as $uid => $card) {
            if (count($high_card) != 1) {
                $high_card = []; // Clear out variable, if somehow got more than one high card

                $high_card[$uid] = $card;

            } else {
                list($current_value, $current_suit) = explode('|', current($high_card));
                list($new_value, $new_suit) = explode('|', $card);

                if ($this->ranking[$current_value] < $this->ranking[$new_value]) {
                    $high_card = []; // Clear out current high card

                    $high_card[$uid] = $card;

                } else if ($this->ranking[$current_value] == $this->ranking[$new_value]) {
                    return 'WAR'; // Stop everything and return a TIED value. This means war!
                }
            }
        }

        return $high_card;
    }
}

class DeckEmptyException extends \Exception {
    protected $message = 'There are no cards left in this deck!';
}
