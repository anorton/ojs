<?php

/**
 * @file plugins/generic/library/Bookshelf.inc.php
 *
 * Copyright (c) 2003-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Bookshelf
 * @ingroup library
 * @see BookshelfDAO
 *
 * @brief Basic class describing the Bookshelf (in Library).
 */

// $Id: User.inc.php,v 1.36 2009/05/14 14:46:20 asmecher Exp $


class Bookshelf extends DataObject {

	function Bookshelf() {
		parent::DataObject();
	}
	
	/**
	 * get bookshelf id
	 * @return int
	 */
	function getBookshelfId() {
		return $this->getData('bookshelfId');
	}

	/**
	 * set bookshelf id
	 * @param $bookshelfId int
	 */
	function setBookshelfId($bookshelfId) {
		return $this->setData('bookshelfId', $bookshelfId);
	}
	
	/**
	 * get bookshelf name
	 * @return int
	 */
	function getBookshelfName() {
		return $this->getData('bookshelfName');
	}

	/**
	 * set bookshelf name
	 * @param $bookshelfName string
	 */
	function setBookshelfName($bookshelfName) {
		return $this->setData('bookshelfName', $bookshelfName);
	}
	
	/**
	 * set bookshelf name
	 * @param $bookshelfName string
	 */
	function getLibraryId() {
		return $this->getData('libraryId');
	}
	
	/**
	 * set library id
	 * @param $libraryId int
	 */
	function setLibraryId($libraryId) {
		return $this->setData('libraryId', $libraryId);
	}

}

?>
