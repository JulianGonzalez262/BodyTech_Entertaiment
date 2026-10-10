<?php

session_start();

/*
|--------------------------------------------------------------------------
| Configuración Google OAuth
|--------------------------------------------------------------------------
*/

$client_id = '389351401352-4091j27fmukdqj7posrgrklhmu3u6r6i.apps.googleusercontent.com';

$redirect_uri = 'http://localhost/bodytech/app/auth/google_callback.php';

$scope = urlencode('openid email profile');

/*
|--------------------------------------------------------------------------
| URL de autenticación
|--------------------------------------------------------------------------
*/

$url = "https://accounts.google.com/o/oauth2/v2/auth";

$url .= "?client_id=" . urlencode($client_id);

$url .= "&redirect_uri=" . urlencode($redirect_uri);

$url .= "&response_type=code";

$url .= "&scope=" . $scope;

$url .= "&access_type=online";

$url .= "&prompt=select_account";

/*
|--------------------------------------------------------------------------
| Redireccionar a Google
|--------------------------------------------------------------------------
*/

header("Location: $url");

exit;