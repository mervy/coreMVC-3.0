<?php

$route[] = ['/', 'ArticlesController@index'];
$route[] = ['/articles', 'ArticlesController@blog'];
$route[] = ['/article/show/{title}/{id}', 'ArticlesController@show'];
$route[] = ['/articles/category/{cat}', 'ArticlesController@category'];
$route[] = ['/article/adblock', 'ArticlesController@adblock'];
$route[] = ['/newsletter', 'ArticlesController@newsletter'];
$route[] = ['/about', 'ArticlesController@about'];
$route[] = ['/contact', 'ArticlesController@contact'];
$route[] = ['/send', 'ArticlesController@contactSend'];
$route[] = ['/404', 'ArticlesController@pnf'];

/**
 * Administration
 */
$route[] = ['/admin', 'AdminController@index', 'auth'];
$route[] = ['/admin/show/{page}', 'AdminController@show','auth'];
$route[] = ['/admin/preview/{id}', 'AdminController@preview','auth'];
$route[] = ['/admin/create/{page}', 'AdminController@create','auth'];
$route[] = ['/admin/store/{page}', 'AdminController@store','auth'];
$route[] = ['/admin/edit/{page}/{id}', 'AdminController@edit','auth'];
$route[] = ['/admin/update/{page}/{id}', 'AdminController@update','auth'];
$route[] = ['/admin/approve/{page}/{id}', 'AdminController@approve','auth'];
$route[] = ['/admin/delete/{page}/{id}', 'AdminController@delete','auth'];

$route[] = ['/login', 'AdminController@login'];
$route[] = ['/login/auth', 'AdminController@auth'];
$route[] = ['/logout', 'AdminController@logout'];

return $route;

