<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

require 'config.php';
require 'models.php';
require 'vendor/autoload.php';

const CACHE_KEY_GALLERY_DATA = "gallery_data";

use phpFastCache\CacheManager;

use Alorel\Dropbox\Operation\AbstractOperation;
use Alorel\Dropbox\Operation\Files\ListFolder\ListFolder;
use Alorel\Dropbox\Operation\Files\ListFolder\ListFolderContinue;
use Alorel\Dropbox\Options\Builder\ListFolderOptions;

AbstractOperation::setDefaultAsync(false);
AbstractOperation::setDefaultToken(DROPBOX_V2_API_TOKEN);


function getAlbumTree() 
{
  // Setup CacheManager
  $cacheManager = CacheManager::Files(array(
      "path" => './cache',
  ));

  // Try to get album tree from cache
  $cachedAlbum = $cacheManager->getItem(CACHE_KEY_GALLERY_DATA);

  // If not in cache, fetch album and cache it.
  if (is_null($cachedAlbum->get())) 
  {
    $fetchedAlbum = fetchAlbumTree();
    $cachedAlbum->set($fetchedAlbum)->expiresAfter(600); // cache expires in 10 minutes
    $cacheManager->save($cachedAlbum);
  } 

  return $cachedAlbum->get();
}

function fetchAlbumTree()
{
  $lf = new ListFolder();
  $lfc = new ListFolderContinue();
  $entries = array();

  // Request folder contents rescursively
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

  // Parse folder contents to an album tree
  /* @var $album Album */
  $album = new Album('My photo gallery', '');
  foreach ($entries as $entry)
  {
      if ($entry['.tag'] === 'folder')
        $album->addItem(new Album($entry['name'], $entry['path_lower']));
  }
  foreach ($entries as $entry)
  {
      if ( $entry['.tag'] === 'file' && (preg_match("/\\.(jpeg|jpg)$/i", $entry['path_lower']) === 1) )
        $album->addItem( parseDropboxImage($entry) );
  }

  return $album;
}

function parseDropboxImage($entry)
{
	if (!isset($entry['media_info'])) {
		echo 'oops';
    var_dump($entry);
    die('//');
  }

 	$name = $entry['name'];
 	$path = $entry['path_lower'];
 	$width = $entry['media_info']['metadata']['dimensions']['width'];
 	$height = $entry['media_info']['metadata']['dimensions']['height'];
 	$date_taken = $entry['media_info']['metadata']['time_taken'];

	return new AlbumImage($name, $path, $width, $height, $date_taken);
}
