<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Diskon Pesanan
    |--------------------------------------------------------------------------
    |
    | Fitur diskon manual per pesanan disembunyikan secara default karena
    | operasional tidak memakainya. Aktifkan lewat POS_DISCOUNT_ENABLED=true
    | jika suatu saat dibutuhkan lagi; panel di POS dan route diskon akan
    | ikut aktif kembali tanpa perubahan kode.
    |
    */

    'discounts_enabled' => (bool) env('POS_DISCOUNT_ENABLED', false),

];
