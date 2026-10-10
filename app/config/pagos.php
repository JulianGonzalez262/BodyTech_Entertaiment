<?php

return [
    'wompi' => [
        'sandbox' => true, // false en producción

        'public_key_test'  => 'pub_test_xxxxxxxxx',
        'private_key_test' => 'prv_test_xxxxxxxxx',

        'public_key_live'  => 'pub_prod_xxxxxxxxx',
        'private_key_live' => 'prv_prod_xxxxxxxxx',

        'currency' => 'COP',
        'redirect_url' => 'http://localhost/bodytech/public/afiliados/recibo.php',
        'webhook_url'  => 'http://localhost/bodytech/app/pagos/webhook_wompi.php',
    ]
];
