<?php

use App\Models\Language;

return [
    "navbar" => [
        "newOrder" => [
            ["langNumber" => 6000, "default" => "NEW", "option" => Language::optionToUpper],
            ' ',
            ["langNumber" => 7058, "default" => "ORDER", "option" => Language::optionToUpper]
        ],
        "orderbasket" => ["langNumber" => 19044, "default" => "ORDER BASKET", "option" => Language::optionToUpper],
        "overview" => ["langNumber" => 7036, "default" => "OVERVIEW","option" => Language::optionToUpper],
        "dropdown" => [
            "userSettings" => ["langNumber" => 0, "default" => "User settings"],
            "tracerSettings" => ["langNumber" => 0, "default" => "Tracer settings"],
            "shopConfiguration" => ["langNumber" => 0, "default" => "Shop configuration"],
            "contact" => ["langNumber" => 0, "default" => "Contact"],
            "logout" => ["langNumber" => 0, "default" => "Log out"],
        ],
    ],
];
