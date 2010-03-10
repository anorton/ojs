<?php

/**
 * @file classes/user/BookshelfDAO.inc.php
 *
 * Copyright (c) 2003-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BookshelfDAO
 * @ingroup library
 * @see DAO
 *
 * @brief Basic class describing the Bookshelf (part of Library).
 */

// $Id: BookshelfDAO.inc.php,v 1.50 2009/06/03 22:24:34 asmecher Exp $


class BookshelfDAO extends DAO {
	
	/**
	 * Create a Bookshelf for a given user.
	 *
	 * @param $user User
	 * @return $bookshelfId the id of the newly created bookshelf
	 */	
	function &createBookshelfForUser($libraryId, $name){
		$this->update(
			'INSERT INTO bookshelves (library_id, name) VALUES (?, ?)', array($libraryId, $name));
		$result =& $this->retrieve(
			'SELECT bookshelf_id FROM bookshelves WHERE library_id = ? AND name = ?', array($libraryId, $name));
		$row =&  $result->GetRowAssoc(false);
		$bookshelfId = $row['bookshelf_id'];
		return $bookshelfId;
	}
	
	/**
	 * Get the Bookshelves for a given Library.
	 * @return array(Bookshelves)
	 * @param $user User
	 */	
	function &getBookshelvesInLibrary($library_id){
		$result =& $this->retrieve(
			'SELECT * FROM bookshelves WHERE library_id = ?', $library_id);

		while (!$result->EOF) {
			$bookshelves[] =& $this->_returnBookshelfFromRow($result->GetRowAssoc(false));
			$result->moveNext();
		}

		$result->Close();
		unset($result);
		return $bookshelves;
	
	}
	
	/**
	 * Get the Bookshelves for a given Library.
	 * @return array(Bookshelves)
	 * @param $user User
	 */	
	function &getBookshelvesForUser(&$user){
		if (isset($user)) {
		$userId =& $user->getId();
		$libraryDao =& DAORegistry::getDAO('LibraryDAO');
		$library = $libraryDao->getLibraryForUser($userId);
		$libraryId = $library->getLibraryId();
		
		$bookshelves = $this->getBookshelvesInLibrary($libraryId);
		}
		return $bookshelves;
	}
	
	/**
	 * Get the Bookshelf denoted by $bookshelfId
	 * @return array(Bookshelves)
	 * @param $user User
	 */	
	function &getBookshelfById($bookshelfId){
		$result =& $this->retrieve(
			'SELECT * FROM bookshelves WHERE bookshelf_id = ?', $bookshelfId);

		//There should only ever be a single row returned by this query
		$bookshelf =& $this->_returnBookshelfFromRow($result->GetRowAssoc(false));
		
		
		
		$result->Close();
		unset($result);
		return $bookshelf;
	}
	
	/**
	 * Get the list of articles in a bookshelf
	 * @return array(Articles)
	 * @param $bookshelfId bookshelf_id
	 */	
	function &getBookshelfContents($bookshelfId){
		$result =& $this->retrieve(
			'SELECT bookshelved_items.item_id, bookshelved_items.bookshelf_id, bookshelved_items.journal_base_url, articles.article_id, articles.journal_id 
			FROM bookshelved_items 
			INNER JOIN articles 
			ON bookshelved_items.article_id = articles.article_id
			WHERE bookshelved_items.bookshelf_id = ?', $bookshelfId);
				
		$bookshelfList = array();
		$i = 0;
		while (!$result->EOF) {
			$article = $this->_returnBookshelvedArticleFromRow($result->GetRowAssoc(false));
			$bookshelfList[$i] =& $article;
			$result->moveNext();
			$i++;
			unset($article);
		}	
				
		$result->Close();
		unset($result);
		return $bookshelfList;
	}
	
	
	/**
	 * Delete a Bookshelf for a given user.
	 * Called on user deletion.
	 * @param $user User
	 */	
	function &deleteBookshelf(&$bookshelfId) {
		$this->update('DELETE FROM bookshelves WHERE bookshelf_id = ?', $bookshelfId);
	}
	
	/**
	 * Add an Article to a Bookshelf.
	 *
	 * @param $article Article, $bookshelf Bookshelf, $journal Journal
	 */
	function addArticleToBookshelf(&$articleId, &$bookshelfId, &$journalId, &$journalBaseUrl) {
		$this->update(
			'INSERT INTO bookshelved_items SET article_id = ?, bookshelf_id = ?, journal_id = ?, journal_base_url = ?', 
			array($articleId, $bookshelfId, $journalId, $journalBaseUrl));
	}
	
	/**
	 * Delete an Article from a Bookshelf.
	 *
	 * @param $article Article, $bookshelf Bookshelf, $journal Journal
	 */
	function removeArticleFromBookshelf($bookshelvedItemsId) {
		$this->update('DELETE FROM bookshelved_items WHERE item_id = ?', 
			$bookshelvedItemsId);
			
	}
	
	
	/**
	 * Creates and returns a Bookshelf object from a row
	 * @param $row array
	 * @return Comment object
	 */
	function &_returnBookshelfFromRow($row) {
		$bookshelf = new Bookshelf();
		$bookshelf->setBookshelfId($row['bookshelf_id']);
		$bookshelf->setLibraryId($row['library_id']);
		$bookshelf->setBookshelfName($row['name']);

		return $bookshelf;
	}
	
	/**
	 * Creates and returns an Article object from a row
	 * @param $row array
	 * @return Comment object
	 */
	function &_returnBookshelvedArticleFromRow($row) {
		$articleId = $row['article_id'];
		$journalId = $row['journal_id'];
		$baseUrl = $row['journal_base_url'];
		$itemId = $row['item_id'];
		
		$article = new BookshelvedArticle();
		
		$articleDao =& DAORegistry::getDAO('BookshelvedArticleDAO');
		$article = $articleDao->getArticle($articleId, $journalId);
		$article->setBaseUrl($baseUrl);
		$article->setBookshelvedItemsId($itemId);

		return $article;
	}
}

?>
