protected $middlewareGroups = [
    'web' => [
        // ... other middleware
        \App\Http\Middleware\HandleInertiaRequests::class,
    ],
];