<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/', function (\App\Models\Tenant\Restaurant $restaurant, \App\Models\Tenant\Menu $menu) {
        $restaurant = $restaurant->first();
        $menuItems = $menu->orderBy('id', 'DESC')->paginate(10);
        return view('tenant-home', compact('restaurant', 'menuItems'));
    })->name('tenant.home');
        


    Route::get('/photo/{path}', function($path){
        $image = str_replace('|', '/', $path);
        $path = storage_path('app/public/' . $image);
        $mimeType = \Illuminate\Support\Facades\File::mimeType($path);
        return response(file_get_contents($path))->header('Content-Type', $mimeType);
    })->name('server.image');

    //Middleware auth para controle de sessão admin...
    Route::middleware('auth')->group(function() {

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
        Route::get('/dashboard', function () {
            return view('dashboard');
        })->middleware(['auth'])->name('dashboard');
    
        Route::prefix('restaurants/menu')->name('restaurants.menu.')->group(function(){
            Route::get('/', \App\Http\Livewire\Tenants\RestaurantMenu\Index::class)->name('index');
        });
    });
    
    require __DIR__.'/auth.php';
});


