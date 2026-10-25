<?php

return [
    'topping_price' => (int) env('MENU_TOPPING_PRICE', env('HOTMAD_TOPPING_PRICE', 2000)),

    'ffmpeg_path' => env('FFMPEG_PATH', 'ffmpeg'),
];
