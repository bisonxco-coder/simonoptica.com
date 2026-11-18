<?php

use App\Models\Language;

return [
    "login"=>[
        "placeholders" => [
            "language" => ["langNumber" => 7248, "default" => "Language"],
            "username" => ["langNumber" => 7069, "default" => "Username"],
            "password" => ["langNumber" => 7073, "default" => "Password"],
        ],
        "title" => ["langNumber" => 19100, "default" => env("WELCOMETEXT", "Welcome to LensWare")],
        "subtitle" => ["langNumber" => 19101, "default" => "Please login to your account"],
        "remeberMe" => ["langNumber" => 19102, "default" => "Remember me"],
        "login" => ["langNumber" => 10330, "default" => "Login"],
        "dialog" => [
            "error" => ["langNumber" => 8051, "default" => "Error"],
            "messages" => [
                "loginError" => ["langNumber" => 19103, "default" => "There has been an error logging in, please check credentials and try again."],
                "enterUsername" => ["langNumber" => 19104, "default" => "Please enter username"],
                "enterPassword" => ["langNumber" => 19105, "default" => "Please enter password"],
            ],
        ],
        "accept" => ["langNumber" => 20080, "default" => "Accept"],
        "decline" => ["langNumber" => 20081, "default" => "Decline"],
    ],
];
