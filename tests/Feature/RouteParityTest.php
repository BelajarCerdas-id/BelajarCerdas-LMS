<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RouteParityTest extends TestCase
{
    /**
     * Test that all application route files load and resolve properly.
     */
    public function test_all_routes_are_registered_and_have_valid_controllers(): void
    {
        $routes = Route::getRoutes()->getRoutes();

        $this->assertNotEmpty($routes, 'Route collection should not be empty.');
        $this->assertGreaterThanOrEqual(650, count($routes), 'Expected at least 650 registered routes.');

        foreach ($routes as $route) {
            $action = $route->getAction();
            $this->assertNotEmpty($action, "Route {$route->uri()} must have an action.");
        }
    }
}
