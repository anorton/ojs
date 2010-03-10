<?php

/**
 * @file classes/library/Library.inc.php
 *
 * Copyright (c) 2003-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Library
 * @ingroup library
 * @see LibraryDAO
 *
 * @brief Basic class describing the Library.
 */

// $Id: User.inc.php,v 1.36 2009/05/14 14:46:20 asmecher Exp $


class Library extends DataObject {

	function Library() {
		parent::DataObject();
	}
	
	/**
	 * get library id
	 * @return int
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
	
	/**
	 * get user id
	 * @return int
	 */
	function getUser() {
		return $this->getData('user');
	}

	/**
	 * set user id
	 * @param $user int
	 */
	function setUser($user) {
		return $this->setData('user', $user);
	}
	
	/**
	 * Retrieve array of Bookshelves.
	 * @return array
	 */
	function &getBookshelves() {
		$bookshelfDao =& DAORegistry::getDAO('BookshelfDAO');
		$bookshelves =& $bookshelfDao->getBookshelvesInLibrary($this->getId());
		return $bookshelves;
	}

	function createBookcase($user, $name) {
		$bookshelfDao =& DAORegistry::getDAO('BookshelfDAO');
		$bookshelfDao->createBookshelfForUser(&$user, $name);
	}

	function &addArticleToBookcase($article, $bookshelf, $journal) {
		$bookshelfDao =& DAORegistry::getDAO('BookshelfDAO');
		$bookshelfDao->addArticleToBookshelf($article, $bookshelf, $journal);
	}
}

?>
