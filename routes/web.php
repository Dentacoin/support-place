<?php

use App\Http\Controllers\Support\NotFoundController;
use App\Http\Controllers\Support\SiteMapController;
use App\Http\Controllers\Support\IndexController;
use App\Http\Controllers\Auth\AuthenticateUser;
use App\Http\Controllers\SSOController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('custom-cookie', 								[SSOController::class, 'manageCustomCookie'])->name('custom-cookie');

//Empty route
$supportRoutes = function () {
	Route::get('user-logout',								[AuthenticateUser::class, 'logout']);
	Route::post('login',									[AuthenticateUser::class, 'login']);
	Route::get('sitemap.xml', 								[SiteMapController::class, 'sitemap']);
	
	Route::group(['prefix' => '{locale?}'], function(){
		Route::any('/', 									[IndexController::class, 'index']);
		Route::any('contact', 								[IndexController::class, 'contact']);
		Route::any('question/{slug}', 						[IndexController::class, 'question']);

		Route::get('page-not-found', 						[NotFoundController::class, 'home']);
		Route::get('{catch?}', 								[NotFoundController::class, 'catch']);
	});

};
Route::domain('dev-support.dentacoin.com')->group($supportRoutes);
Route::domain('support.dentacoin.com')->group($supportRoutes);