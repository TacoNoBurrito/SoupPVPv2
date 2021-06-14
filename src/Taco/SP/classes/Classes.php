<?php namespace Taco\SP\classes;

abstract class Classes {

    //https://github.com/pmmp/PocketMine-MP/blob/stable/src/pocketmine/item/ItemIds.php

    public const CLASSES = [
        "Fighter" => [
            "helmet" => "306:0",
            "chestplate" => "307:0",
            "leggings" => "308:0",
            "boots" => "309:0",
            "weapon" => "276:0",
            "powerup" => "1:0:noName",
            "hasEffects" => false,
            "effects" => [
                "1:2"
            ],
            "price" => 0,
            "icon" => "null",
            "powerupCooldown" => 0,
            "isInteractPowerup" => false,
            "isPurchaseable" => false
        ],
        "Necromancer" => [
            "helmet" => "298:0",
            "chestplate" => "307:0",
            "leggings" => "300:0",
            "boots" => "309:0",
            "weapon" => "276:0",
            "powerup" => "280:0:ZombieAttack",
            "hasEffects" => false,
            "effects" => [
                "1:2"
            ],
            "price" => 100,
            "icon" => "textures/flame_atlas",
            "powerupCooldown" => 30,
            "isInteractPowerup" => false,
            "isPurchaseable" => true
        ]
    ];

    public const POWERUPS = [
        "Fighter" => "none",
        "Necromancer" => [
            "hasEffects" => false,
            "hasSpecialAbility" => true
        ],
    ];

}