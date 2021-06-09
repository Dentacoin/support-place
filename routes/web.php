<?php

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

Route::get('custom-cookie', 						'App\Http\Controllers\SSOController@manageCustomCookie')->name('custom-cookie');

//Empty route
$supportRoutes = function () {
	Route::get('user-logout',								'App\Http\Controllers\Auth\AuthenticateUser@logout');
	Route::post('login',									'App\Http\Controllers\Auth\AuthenticateUser@login');
	
	Route::group(['prefix' => '{locale?}'], function(){
		Route::any('/', 									'App\Http\Controllers\Support\IndexController@index');
		Route::any('contact', 								'App\Http\Controllers\Support\IndexController@contact');
		Route::any('question/{slug}', 						'App\Http\Controllers\Support\IndexController@question');
	});
};
Route::domain('dev-support.dentacoin.com')->group($supportRoutes);
Route::domain('support.dentacoin.com')->group($supportRoutes);