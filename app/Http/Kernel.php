protected $routeMiddleware = [
    'ip.whitelist' => \App\Http\Middleware\CheckIpWhitelist::class,
];
