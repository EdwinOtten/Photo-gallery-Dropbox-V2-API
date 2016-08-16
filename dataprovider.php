<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'config.php';
require 'models.php';
require 'vendor/autoload.php';


use Alorel\Dropbox\Operation\AbstractOperation;
use Alorel\Dropbox\Operation\Files\ListFolder\ListFolder;
use Alorel\Dropbox\Operation\Files\ListFolder\ListFolderContinue;
use Alorel\Dropbox\Options\Builder\ListFolderOptions;

AbstractOperation::setDefaultAsync(false);
AbstractOperation::setDefaultToken(DROPBOX_V2_API_TOKEN);


$lf = new ListFolder();
$lfc = new ListFolderContinue();
$entries = array();

$folderList = json_decode($lf->raw('', (new ListFolderOptions())->setRecursive(true)->setIncludeMediaInfo(true))->getBody()->getContents(), true);
$entries = array_merge($entries, $folderList['entries']);
$hasMore = $folderList['has_more'];
$cursor = $folderList['cursor'];

while ($hasMore) 
{
    $folderList = json_decode($lfc->raw($cursor)->getBody()->getContents(), true);
    $entries = array_merge($entries, $folderList['entries']);
    $hasMore = $folderList['has_more'];
    $cursor = $folderList['cursor'];
}

$gallery = new Album('My photo gallery', '');
foreach ($entries as $entry)
{
    if ($entry['.tag'] === 'folder')
    	$gallery->addItem(new Album($entry['name'], $entry['path_lower']));
}
foreach ($entries as $entry)
{
    if ($entry['.tag'] !== 'folder')
    	$gallery->addItem( parseDropboxImage($entry) );
}

function parseDropboxImage($entry)
{
   	$name = $entry['name'];
   	$path = $entry['path_lower'];
   	$width = $entry['media_info']['metadata']['dimensions']['width'];
   	$height = $entry['media_info']['metadata']['dimensions']['height'];
   	$date_taken = $entry['media_info']['metadata']['time_taken'];

	return new AlbumImage($name, $path, $width, $height, $date_taken);
}
