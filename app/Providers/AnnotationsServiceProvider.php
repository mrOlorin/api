<?php

namespace App\Providers;

use Collective\Annotations\AnnotationsServiceProvider as ServiceProvider;
use App\Http\Controllers;

/**
 * Description of AnnotationsServiceProvider
 *
 * @author Olorin
 */
class AnnotationsServiceProvider extends ServiceProvider
{

    protected $scanEvents = [];
    protected $scanRoutes = [
        Controllers\IndexController::class,
        Controllers\TokenAuthController::class,
        Controllers\CategoriesController::class,
        Controllers\ProductsController::class,
    ];
    protected $scanWhenLocal = true;

}
