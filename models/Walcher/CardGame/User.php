<?php namespace Walcher\CardGame;

interface User {
    public function __construct();
    public function receive();
    public function play();
}