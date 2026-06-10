<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Todo el enrutamiento de páginas lo maneja Vue Router en el SPA.
| Laravel solo sirve la vista welcome (el entry point de Vue) para
| cualquier ruta que no sea de la API.
*/

Route::get('/{any?}', function () {
    return view('welcome');
})->where('any', '.*');
