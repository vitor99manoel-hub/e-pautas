<?php

// Allows private Shell access for a restore without opening the marketplace.
http_response_code(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/up' ? 200 : 503);
header('Content-Type: text/plain; charset=utf-8');
header('Retry-After: 300');
echo "Manutenção de armazenamento. Tente novamente mais tarde.\n";
