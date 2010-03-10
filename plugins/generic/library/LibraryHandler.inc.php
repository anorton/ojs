<?php

/**
 * @file pages/about/LibraryHandler.inc.php
 *
 * Copyright (c) 2003-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LibraryHandler
 * @ingroup pages_library
 *
 * @brief Handle requests for editor functions. 
 */

// $Id: LibraryHandler.inc.php,v 1.67 2009/09/10 15:59:37 asmecher Exp $


import('handler.Handler');

class LibraryHandler extends Handler {
	/**
	 * Constructor
	 **/
	function LibraryHandler() {
		parent::Handler();
	}

	/**
	 * Display Library index page. 
	 *
	 * If the user has a Library, a list of the Bookshelves contained in 
	 * 		this Library is displayed.
	 * If this user has a Library, but it is empty, text prompting them to 
	 *		create one is displayed.
	 * If this user does not have a Library, one is created before the Library
	 * 		index page is displayed.
	 */
	function index() {
		$this->validate();
		$this->setupTemplate();
		
		$libraryPlugin =& PluginRegistry::getPlugin('generic', 'LibraryPlugin');
		
		if ($libraryPlugin != null) {
			$libraryEnabled = $libraryPlugin->getEnabled();
		}
		
		if ($libraryEnabled) {
			$user =& Request::getUser();
			$userId = $user->getId();
	
			$templateMgr =& TemplateManager::getManager();
			
			$libraryDao =& DAORegistry::getDAO('LibraryDAO');	
			$hasLibrary = $libraryDao->hasLibrary($userId);
			if(!$hasLibrary) {
				//User does not have a Library, so create one.
				//$libraryDao->createLibraryForUser($userId);
				$this->_createLibrary(&$user);
			} 
			$bookshelfList = $libraryDao->getBookshelvesForUser(&$user);
			$templateMgr->assign('hasLibrary', $hasLibrary);
			$templateMgr->assign('bookshelfList', $bookshelfList);
			$templateMgr->display($libraryPlugin->getTemplatePath() . 'library.tpl');

		}
	}
	
	function _createLibrary(&$user) {
		$userId = $user->getId();
		$libraryDao =& DAORegistry::getDAO('LibraryDAO');	
		$hasLibrary = $libraryDao->hasLibrary($userId);
		if(!$hasLibrary) {
			//User does not have a Library, so create one.
			$libraryId = $libraryDao->createLibraryForUser($userId);
		}
	}

function addArticleToBookshelf($args) {
		$user =& Request::getUser();
		$journal =& Request::getJournal();
		$journalId = $journal->getJournalId();
		$journalBaseUrl = $journal->getUrl();
		$bookshelfId = Request::getUserVar('bookshelfId');
		$articleId = Request::getUserVar('articleId');

		$libraryDao =& DAORegistry::getDAO('LibraryDAO');
		
		$response = $libraryDao->addArticleToBookshelf($articleId, $bookshelfId, $journalId, $journalBaseUrl);
		
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
		$libraryDao =& DAORegistry::getDAO('LibraryDAO');
		
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
					$libraryDao->removeArticleFromBookshelf($a);
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
			$libraryPlugin =& PluginRegistry::getPlugin('generic', 'LibraryPlugin');
			$libraryDao =& DAORegistry::getDAO('LibraryDAO');
			$bookshelf = $libraryDao->getBookshelfById(&$bookshelfId);
			$bookshelfName = $bookshelf->getBookshelfName();
			$bookshelvedArticleList = $libraryDao->getBookshelfContents($bookshelfId);
			
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
			$templateMgr->display($libraryPlugin->getTemplatePath() . 'viewBookshelf.tpl');
		}
	}

	function createBookshelf() {
		$user =& Request::getUser();
		$userId = $user->getId();
		$bookshelfName = Request::getUserVar('bookshelfName');
		$libraryDao =& DAORegistry::getDAO('LibraryDAO');
		$libraryDao =& DAORegistry::getDAO('LibraryDAO');
		$library = $libraryDao->getLibraryForUser($userId);
		$libraryId = $library->getLibraryId();
		
		$newBookshelfId = $libraryDao->createBookshelfForUser($libraryId, $bookshelfName);
		
		Request::Redirect(null, 'library', 'viewBookshelf', $newBookshelfId);
	
	}
	
	function deleteBookshelf($args) {
		$bookshelfId = Request::getUserVar('bookshelfId');
		$libraryDao =& DAORegistry::getDAO('LibraryDAO');
		$libraryDao->deleteBookshelf($bookshelfId);
			
		Request::Redirect(null, 'library');
	}

}

?>
