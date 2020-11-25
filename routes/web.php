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

//Carganado clases
use App\Http\Middleware\ApiAuthMiddleware;

Route::get('/', function () {
    return view('welcome');
});

//Rutas de prueba
Route::get('/test-orm','PruebasController@testOrm');


//Rutas del API

    //Rutas de Prueba API
   /*  Route::get('/usuario/pruebas','UserController@pruebas');
    Route::get('/post/pruebas','PostController@pruebas');
    Route::get('/category/pruebas','CategoryController@pruebas');
    
     */

    //Rutas del controlador de usuarios
    
    Route::post('/api/register','UserController@register');
    
    Route::post('/api/login','UserController@login');
    
    
    Route::put('/api/user/update','UserController@update');
    
    Route::post('/api/user/upload','UserController@upload')->middleware(ApiAuthMiddleware::class);
    
    Route::get('/api/user/avatar/{filename}','UserController@getImage');
    
    Route::get('/api/user/detail/{id}','UserController@detail_user');
    
    
    
    
    //Rutas del controlador categorias (automaticas)
    Route::resource('/api/category','CategoryController');
    
    
    //Rutas del controlador de entradas (posts)
    Route::resource('/api/post','PostController');
    
    //Subir imagen del post
    Route::post('/api/post/upload','PostController@upload');
    
    //Conseguir o mostrar una imagen segun el nombre
    Route::get('/api/post/image/{filename}','PostController@getImage');
    
    //Conseguir post segun categoria
    Route::get('/api/post/category/{id}','PostController@getPostsByCategory');
    
    //Conseguir post segun usuario
    Route::get('/api/post/user/{id}','PostController@getPostsByUser');