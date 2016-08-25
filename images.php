<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'config.php';
require 'vendor/autoload.php';

use phpFastCache\CacheManager;

use Alorel\Dropbox\Operation\AbstractOperation;
use Alorel\Dropbox\Operation\Files\GetThumbnail;
use Alorel\Dropbox\Options\Builder\GetThumbnailOptions;
use Alorel\Dropbox\Parameters\ThumbnailFormat;
use Alorel\Dropbox\Parameters\ThumbnailSize;
use Alorel\Dropbox\Operation\Files\Download;

AbstractOperation::setDefaultAsync(false);
AbstractOperation::setDefaultToken(DROPBOX_V2_API_TOKEN);


if (!isset($_GET['path']))
	die('no path!');

$imagePath = urldecode($_GET['path']);

$cacheKey;
$is_thumb = FALSE;
if (isset($_GET['thumbnail']) && $_GET['thumbnail'] != '0' && $_GET['thumbnail'] != 'false') {
	$cacheKey = "gallery_thumb_".hash('sha256', $imagePath);
	$is_thumb = TRUE;
}
else
	$cacheKey = "gallery_image_".hash('sha256', $imagePath);

// Setup CacheManager
$cacheManager = CacheManager::Files(array(
      "path" => CACHE_DIR,
));

// Try to get image from cache
$cachedImage = $cacheManager->getItem($cacheKey);

// If not in cache, fetch image and cache it.
if (is_null($cachedImage->get())) 
{
	$fetchedImage;
	if ($is_thumb)
		$fetchedImage = fetchThumbnail($imagePath);
	else
		$fetchedImage = fetchImage($imagePath);

	$cachedImage->set($fetchedImage)->expiresAfter($is_thumb?7776000:604800); // cache expires in 90 days for thumbs, 7 days for full res
	$cacheManager->save($cachedImage);
}

header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60 * 24))); // 1 day
header('Content-type: image/jpeg');
echo $cachedImage->get();


function fetchImage($imagePath) 
{
  	$op = new Download();
	return $op->raw($imagePath)->getBody()->getContents();
}

function fetchThumbnail($imagePath) 
{
	$op = new GetThumbnail();
	$options = new GetThumbnailOptions();
	$options->setThumbnailSize(ThumbnailSize::w640h480())
	    ->setThumbnailFormat(ThumbnailFormat::jpeg());
	return $op->raw($imagePath, $options)->getBody()->getContents();
}
