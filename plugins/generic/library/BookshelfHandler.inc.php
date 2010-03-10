<?php

/**
 * @file pages/about/BookshelfHandler.inc.php
 *
 * Copyright (c) 2003-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BookshelfHandler
 * @ingroup pages_bookshelf
 *
 * @brief Handle requests for bookshelf functions. 
 */

// $Id: LibraryHandler.inc.php,v 1.67 2009/09/10 15:59:37 asmecher Exp $


import('handler.Handler');

class BookshelfHandler extends Handler {
	/**
	 * Constructor
	 **/
	function BookshelfHandler() {
		parent::Handler();
	}
	
	function addArticleToBookshelf($args) {
		$user =& Request::getUser();
		$journal =& Request::getJournal();
		$journalId = $journal->getJournalId();
		$journalBaseUrl = $journal->getUrl();
		$bookshelfId = Request::getUserVar('bookshelfId');
		$articleId = Request::getUserVar('articleId');

		$bookshelfDao =& DAORegistry::getDAO('BookshelfDAO');
		
		$response = $bookshelfDao->addArticleToBookshelf($articleId, $bookshelfId, $journalId, $journalBaseUrl);
		
		Request::Redirect(null, 'library', 'viewBookshelf', $bookshelfId);

	}
	
	/**
	 * adminBookshelf: This function currently handles deleting articles from a bookshelf,
	 * 					but 
	 *
	 *
	 */
	function adminBookshelf($args) {
		$templateMgr =& TemplateManager::getManager();
		$bookshelfDao =& DAORegistry::getDAO('BookshelfDAO');
		
		//Get value of Submit button
		//If 'delete' call removeArticleFromBookshelf
		$submitBtn = Request::getUserVar('submitBtn');
		$bookshelfId =& Request::getUserVar('bookshelfId');
		if (!(strpos(strtolower($submitBtn), 'remove selected articles'))) {
			//Get the list of articles to delete and call removeArticlesFromBookshelf
			$articleList =& Request::getUserVar('selectArticles'); 
			if (is_array($articleList)) {
				foreach ($articleList as $a) {
					//removeArticleFromBookshelf(&$articleId, &$bookshelfId, &$journalId)
					$bookshelfDao->removeArticleFromBookshelf($a);
				}
			}
		}
		$args = array($bookshelfId);
		$this->viewBookshelf($args);
	}

	/**
	 * Display Bookshelf index page. 
	 *
	 */
	function viewBookshelf($args) {
		parent::setupTemplate();
		if (!isset($args[0])) {
			//Error, send back to Library page.
			Request::Redirect(null, 'library');
		} else {
			$bookshelfId = $args[0];
			$bookshelfDao =& DAORegistry::getDAO('BookshelfDAO');
			$bookshelf = $bookshelfDao->getBookshelfById(&$bookshelfId);
			$bookshelfName = $bookshelf->getBookshelfName();
			$bookshelvedArticleList = $bookshelfDao->getBookshelfContents($bookshelfId);
			
			$templateMgr =& TemplateManager::getManager();

			// Add font sizer js and css if not already in header
			$additionalHeadData = $templateMgr->get_template_vars('additionalHeadData');
			if (strpos(strtolower($additionalHeadData), 'sorttable.js') === false) {
				$additionalHeadData .= $templateMgr->fetch('common/sorttable.tpl');
				$templateMgr->assign('additionalHeadData', $additionalHeadData);
			}
			
			$templateMgr->assign('bookshelf', $bookshelf);
			$templateMgr->assign('articleList', $bookshelvedArticleList);
			$templateMgr->assign('bookshelfName', $bookshelfName);
			$templateMgr->display('library/viewBookshelf.tpl');
		}
	}

	function createBookshelf() {
		$user =& Request::getUser();
		$userId = $user->getId();
		$bookshelfName = Request::getUserVar('bookshelfName');
		$bookshelfDao =& DAORegistry::getDAO('BookshelfDAO');
		$libraryDao =& DAORegistry::getDAO('LibraryDAO');
		$library = $libraryDao->getLibraryForUser($userId);
		$libraryId = $library->getLibraryId();
		
		$newBookshelfId = $bookshelfDao->createBookshelfForUser($libraryId, $bookshelfName);
		
		Request::Redirect(null, 'library', 'viewBookshelf', $newBookshelfId);
	
	}
	
	function deleteBookshelf($args) {
		$bookshelfId = Request::getUserVar('bookshelfId');
		$bookshelfDao =& DAORegistry::getDAO('BookshelfDAO');
		$bookshelfDao->deleteBookshelf($bookshelfId);
			
		Request::Redirect(null, 'library');
	}

}

?>
