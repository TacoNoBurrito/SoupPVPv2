<?php namespace Taco\SP\ft;

abstract class FloatingText {

    public const FLOATING_TEXT = [
        "welcome" => [
            "x" => 100,
            "y" => 100,
            "z" => 100,
            "text" => "Welcome {name}!",
            "per-player" => true,
            "update-every" => 10,
            "does-update" => true
        ]
    ];

}