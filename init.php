<?php defined('SYSPATH') or die('No direct script access.');

Route::set('api-v1', 'api/v1/<controller>(/<id>(/<action>))(<format>)',
	   array(
		 'format' => '\..*',
		 )
	   )
->defaults(array(
		 'directory' => 'Api/V1',
		 'controller' => 'dashboard',
		 'action' => 'index',
		 'format' => '',
		 'id' => NULL,
		 ))
->filter(function($route, $params, $request)
	 {
	   /* removes the period used as format delimiter */
	   if( substr($params['format'], 0, 1) === '.' )
	     {
	       $params['format'] = substr($params['format'], 1);
	     }
	   
	   if( $params['format'] === '' )
	     {
	       $params['format'] = NULL;
	     }

	   return $params;
	 });
