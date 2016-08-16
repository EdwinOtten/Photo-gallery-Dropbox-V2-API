<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'config.php';
require 'vendor/autoload.php';


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

if (isset($_GET['thumbnail'])) 
{
	if ($_GET['thumbnail'] != '0' && $_GET['thumbnail'] != 'false') 
	{
		if (!file_exists(__DIR__.'/cache')) {
		    mkdir(__DIR__.'/cache', 0777, true);
		}
		$hash = hash('sha256', $imagePath);
		$cachedImagePath = __DIR__.'/cache/'.$hash.'.jpg';

		if (!file_exists($cachedImagePath)) 
		{
		    $op = new GetThumbnail();
		    $options = new GetThumbnailOptions();
		    $options->setThumbnailSize(ThumbnailSize::w640h480())
		        ->setThumbnailFormat(ThumbnailFormat::jpeg());

		    $fetchedThumb = $op->raw($imagePath, $options)->getBody()->getContents();
		    file_put_contents($cachedImagePath, $fetchedThumb);
		}

		$imginfo = getimagesize($cachedImagePath);
		header("Content-type: ".$imginfo['mime']);
		readfile($cachedImagePath);
		die;
	}
}


$op = new Download();
$response = $op->raw($imagePath)->getBody()->getContents();
header('Content-type: image/jpeg');
echo $response;
