<?php

interface ParsableFromAssocArrayInterface
{
	public static function assocArrayToObject(array $assocArray);
}

function compareAlbumItem($a, $b)
{
	if ( $a->type !== 'image' || $b->type !== 'image' )
	{
		if ( $a->type === 'album' && $b->type === 'image' )
			return -1;
		else if ( $a->type === 'image' && $b->type === 'album' )
			return 1;
		else if ( 
			$a->type === 'album' && 
			$b->type === 'album' && 
			preg_match("/^20[0-9]{2}/", $a->name) === 1 && 
			preg_match("/^20[0-9]{2}/", $b->name) === 1 )
		    return strcmp($b->name, $a->name); // sort reverse if both are albums and both names appears to be year-numbers
	}
	return strcmp($a->name, $b->name);
}

class AlbumItem
{
	public $type;
	public $name;
	public $path;

	function __construct($name, $path) 
	{
		if (!is_string($name))
			throw new Exception('class AlbumItem: $name should be a string!');
		if (!is_string($path))
			throw new Exception('class AlbumItem: $path should be a string!');

		$this->name = $name;
		$this->path = $path;
	}
}

class AlbumImage extends AlbumItem implements ParsableFromAssocArrayInterface
{
	public $type = "image";
	public $height;
	public $width;
	public $date_taken;

	function __construct($name, $path, $width, $height, $date_taken = NULL) 
	{
		if (!is_string($path))
			throw new Exception('class AlbumImage: $path should be a string!');

		if (preg_match("/\\.(jpeg|jpg)$/i", $path) === FALSE)
			throw new Exception('class AlbumImage: $path should be a JPEG/JPG!');

		if (!is_integer($width))
			throw new Exception('class AlbumImage: $width should be an integer!');

		if (!is_integer($height))
			throw new Exception('class AlbumImage: $height should be an integer!');
			
		parent::__construct($name, $path);
		$this->width = $width;
		$this->height = $height;
		$this->date_taken = $date_taken;
	}

	static function assocArrayToObject(array $data)
	{
		if (!isset($data['name']) || !isset($data['path']) || !isset($data['width']) || !isset($data['height'])|| !isset($data['date_taken']))
			return NULL;

		return new AlbumImage($data['name'], $data['path'], $data['width'], $data['height'], $data['date_taken']);
	}
}

class Album extends AlbumItem implements ParsableFromAssocArrayInterface
{
	public $type = "album";
	public $items;

	function __construct($name, $path, $items = array()) 
	{
		if (!is_array($items))
			throw new Exception('class Album: $items should be an array!');

		parent::__construct($name, $path);
		$this->items = array();

		foreach ($items as $item)
		{
			$this->addItem($item);
		}
	}

	function getImageForThumbnail()
	{
		foreach ($this->items as $item)
		{
			if ($item instanceof AlbumImage)
				return $item;
		}
		foreach ($this->items as $item)
		{
			if ($item instanceof Album) {
				$image = $item->getImageForThumbnail();
				if ($image)
					return $image;
			}
		}
		return NULL;
	}

	function getAlbumForPath($path)
	{
		if ($this->path === $path) {
			return $this;
		}
		foreach ($this->items as $item) 
		{
			if ($item instanceof Album) {
				$result = $item->getAlbumForPath($path);
				if ($result instanceof Album)
					return $result;
			}
		}
		return NULL;
	}

	function addItem(AlbumItem $newItem)
	{
		if (!is_subclass_of($newItem, 'AlbumItem'))
			throw new Exception('Album->items can only contain AlbumItems!'); 
	
		if ($this->path === getParentPath($newItem->path)) {
			$this->items[] = $newItem;
			usort($this->items, 'compareAlbumItem');
			return TRUE;
		}
		foreach ($this->items as $item) 
		{
			if ($item instanceof Album) {
				if ($item->addItem($newItem) === TRUE)
					return TRUE;
			}
		}
		return FALSE;
	}

	static function assocArrayToObject(array $data)
	{
		if (!isset($data['name']) || !isset($data['folderPath']) || !isset($data['thumbnailPath']) || !isset($data['items']))
			return NULL;

		/* @var $album Album */
		$album =  new Album($data['name'], $data['folderPath'], $data['thumbnailPath'], $albums);

		foreach ($data['items'] as $itemData)
		{
			switch ($itemData['type']) {
			 	case 'album':
			 		$album->addItem( Album::assocArrayToObject($itemData) );
			 		break;
			 	case 'image':
			 		$album->addItem( AlbumImage::assocArrayToObject($itemData) );
			 		break;
			}
		}

		return $album;
	}
}

function getParentPath($path)
{
	$pieces = explode('/', $path);
    $parentPath = '';
    for ($i = 0; $i < (count($pieces)-1); $i++) {
        $parentPath .= $pieces[$i].'/';
    }
    return substr($parentPath, 0, -1); // strips the last slash
}
