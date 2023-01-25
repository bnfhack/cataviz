<?php
/**
 * Aiguillage de l’application (routage)
 */
declare(strict_types=1);

// where am I ?
$home_dir = __DIR__ . '/';

// load the master class
require_once($home_dir . "Cataviz.php");

use Oeuvres\Kit\{Route,I18n};



// Default title for pages
I18n::put(['title'=>'Cataviz']);
// register the template in which include content
Route::template($home_dir . 'template.php');


// Welcome page
Route::get('/', $home_dir . 'html/index.html');
// plots
Route::get('/(.*)', $home_dir . 'plots/$1.php');
// blog, TODO, transform odt->page
Route::get('/articles/(.*)', $home_dir . 'html/$1.html');
// catch all
Route::route('/404', $home_dir . 'html/404.html');
// No Route has worked
echo "Bad routage, 404.";
