<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
Route::get('/', function () {
  return view('welcome');
});

Route::get('/show/page','AdController@showPage');
Route::get('/showSuccess','AdController@showSuccess');
Route::any('/claims','AdController@claims');
Route::any('/clause','AdController@clause');
Route::post('/receiveInsurance','AdController@receiveInsurance');
Route::post('/getTokenKey','AdController@getTokenKey');
Route::post('/getSponsorByKey','AdController@getSponsorByKey');
Route::post('/getSecurityState','AdController@getSecurityState');

