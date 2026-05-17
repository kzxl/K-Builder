<?php

return [
    'secret'          => $_ENV['JWT_SECRET'] ?? 'default-secret-change-me',
    'expiry'          => (int) ($_ENV['JWT_EXPIRY'] ?? 3600),          // 1 hour
    'refresh_expiry'  => (int) ($_ENV['JWT_REFRESH_EXPIRY'] ?? 604800), // 7 days
    'algorithm'       => 'HS256',
    'issuer'          => $_ENV['APP_URL'] ?? 'http://localhost',
];
