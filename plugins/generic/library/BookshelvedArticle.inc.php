<?php

/**
 * @defgroup article
 */

/**
 * @file plugins/generic/library/BookshelvedArticle.inc.php
 *
 * Copyright (c) 2003-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BookshelvedArticle
 * @ingroup library
 * @see ArticleDAO
 *
 * @brief BookshelvedArticle class.
 */

// $Id: BookshelvedArticle.inc.php,v 1.49 2009/12/02 06:38:29 jerico.dev Exp $

import('submission.Submission');
import('article.Article');

class BookshelvedArticle extends Article {
	var $baseUrl = "";
	var $bookshelvedItemsId = "";
	var $note = "";

	/**
	 * Constructor.
	 */
	function BookshelvedArticle() {
		parent::Article();
	}
	
	function setBaseUrl(&$url) {
		$this->baseUrl = $url;
	}
	
	function getBaseUrl() {
		return $this->$baseUrl;
	}

	function setBookshelvedItemsId(&$id) {
		$this->bookshelvedItemsId = $id;
	}
	
	function getBookshelvedItemsId() {
		return $this->$bookshelvedItemsId;
	}
	
	function setNote(&$note) {
		$this->note = $note;
	}
	
	function getNote() {
			return $this->note;
	}
	
}

?>
