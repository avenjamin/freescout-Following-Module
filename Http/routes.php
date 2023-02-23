<?php

Route::group(['middleware' => 'web', 'prefix' => \Helper::getSubdirectory(), 'namespace' => 'Modules\Following\Http\Controllers'], function()
{
    Route::get('/', 'FollowingController@index');
});
