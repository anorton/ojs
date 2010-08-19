<?php

/**
 * @file LibraryPlugin.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LibraryPlugin
 * @ingroup plugins_generic_library
 *
 * @brief Library plugin class
 */

// $Id$


import('classes.plugins.GenericPlugin');

class LibraryPlugin extends GenericPlugin {

	/**
	 * Called as a plugin is registered to the registry
	 * @param $category String Name of category plugin was registered to
	 * @return boolean True iff plugin initialized successfully; if false,
	 * 	the plugin will not be registered.
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		$this->addLocaleData();
		if ($success) {
			$this->import('LibraryDAO');
			$libraryDao = new LibraryDAO();
			$returner =& DAORegistry::registerDAO('LibraryDAO', $libraryDao);
			
			$this->import('BookshelvedArticleDAO');
			$bookshelvedArticleDao = new BookshelvedArticleDAO();
			$returner =& DAORegistry::registerDAO('BookshelvedArticleDAO', $bookshelvedArticleDao);
			
			// Handler for public thesis abstract pages
			HookRegistry::register('LoadHandler', array($this, 'setupPublicHandler'));

			// Navigation bar link to Library page
			HookRegistry::register('Templates::Common::Header::Navbar::CurrentJournal', array($this, 'displayHeaderLink'));

			// Journal Manager link to thesis abstract management pages
			HookRegistry::register('Templates::Manager::Index::ManagementPages', array($this, 'callback'));
		}
		return $success;
	}

	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category, and should be suitable for part of a filename
	 * (ie short, no spaces, and no dependencies on cases being unique).
	 * @return String name of plugin
	 */
	function getName() {
		return 'LibraryPlugin';
	}

	function getDisplayName() {
		return Locale::translate('plugins.generic.library.displayName');
	}

	function getDescription() {
		return Locale::translate('plugins.generic.library.description');
	}

	/**
	 * Get the filename of the ADODB schema for this plugin.
	 */
	function getInstallSchemaFile() {
		return $this->getPluginPath() . '/' . 'schema.xml';
	}

	/*function getInstallEmailTemplatesFile() {
		return ($this->getPluginPath() . DIRECTORY_SEPARATOR . 'emailTemplates.xml');
	}

	function getInstallEmailTemplateDataFile() {
		return ($this->getPluginPath() . '/locale/{$installedLocale}/emailTemplates.xml');
	}*/

	function callback($hookName, $args) {
		$params =& $args[0]; 
		$smarty =& $args[1]; 
		$output =& $args[2]; 
		$output = '<li>&#187; <a href=”http://pkp.sfu.ca”>My New Link</a></li>'; 
		return false;
}

	/**
	 * Set the page's breadcrumbs, given the plugin's tree of items
	 * to append.
	 * @param $subclass boolean
	 */
	function setBreadcrumbs($isSubclass = false) {
		$templateMgr =& TemplateManager::getManager();
		$pageCrumbs = array(
			array(
				Request::url(null, 'user'),
				'navigation.user'
			),
			array(
				Request::url(null, 'manager'),
				'user.role.manager'
			)
		);
		if ($isSubclass) $pageCrumbs[] = array(
			Request::url(null, 'manager', 'plugin', array('generic', $this->getName(), 'library')),
			$this->getDisplayName(),
			true
		);

		$templateMgr->assign('pageHierarchy', $pageCrumbs);
	}

	/**
	 * Display verbs for the management interface.
	 */
	function getManagementVerbs() {
		$verbs = array();
		if ($this->getEnabled()) {
			$verbs[] = array(
				'disable',
				Locale::translate('manager.plugins.disable')
			);
		} else {
			$verbs[] = array(
				'enable',
				Locale::translate('manager.plugins.enable')
			);
		}
		return $verbs;
	}

	/**
	 * Determine whether or not this plugin is enabled.
	 */
	function getEnabled() {
		$journal =& Request::getJournal();
		if (!$journal) return false;
		return $this->getSetting($journal->getId(), 'enabled');
	}

	function setupPublicHandler($hookName, $params) {
		$page =& $params[0];
		if ($page == 'library') {
			define('HANDLER_CLASS', 'LibraryHandler');
			$handlerFile =& $params[2];
			$handlerFile = $this->getPluginPath() . '/' . 'LibraryHandler.inc.php';
		}
	}

	function displayHeaderLink($hookName, $params) {
		if ($this->getEnabled()) {
			$smarty =& $params[1];
			$output =& $params[2];
			$output .= '<li><a href="' . TemplateManager::smartyUrl(array('page'=>'library'), $smarty) . '" target="_parent">' . TemplateManager::smartyTranslate(array('key'=>'plugins.generic.library.headerLink'), $smarty) . '</a></li>';
		}
		return false;
	}

	function displayManagerLink($hookName, $params) {
		if ($this->getEnabled()) {
			$smarty =& $params[1];
			$output =& $params[2];
			$output .= '<li>&#187; <a href="' . $this->smartyPluginUrl(array('op'=>'plugin', 'path'=>'theses'), $smarty) . '">' . TemplateManager::smartyTranslate(array('key'=>'plugins.generic.thesis.manager.theses'), $smarty) . '</a></li>';
		}
		return false;
	}

	function displaySearchLink($hookName, $params) {
		if ($this->getEnabled()) {
			$smarty =& $params[1];
			$output =& $params[2];
			$currentJournal = $smarty->get_template_vars('currentJournal');
			if (!empty($currentJournal)) {
				$output .= '<a href="' . TemplateManager::smartyUrl(array('page'=>'thesis'), $smarty) . '" class="action">' . TemplateManager::smartyTranslate(array('key'=>'plugins.generic.thesis.searchLink'), $smarty) . '</a><br /><br />';
			}
		}
		return false;
	}

	/**
	 * Set the enabled/disabled state of this plugin
	 */
	function setEnabled($enabled) {
		$journal =& Request::getJournal();
		if ($journal) {
			$this->updateSetting($journal->getId(), 'enabled', $enabled ? true : false);
			return true;
		}
		return false;
	}

 	/*
 	 * Execute a management verb on this plugin
 	 * @param $verb string
 	 * @param $args array
	 * @param $message string Location for the plugin to put a result msg
 	 * @return boolean
 	 */
	function manage($verb, $args, &$message) {
		Locale::requireComponents(array(LOCALE_COMPONENT_APPLICATION_COMMON,  LOCALE_COMPONENT_PKP_MANAGER, LOCALE_COMPONENT_PKP_USER));
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->register_function('plugin_url', array(&$this, 'smartyPluginUrl'));
		$journal =& Request::getJournal();
		$returner = true;

		switch ($verb) {
			case 'enable':
				$this->setEnabled(true);
				$message = Locale::translate('plugins.generic.library.enabled');
				$returner = false;
				break;
			case 'disable':
				$this->setEnabled(false);
				$message = Locale::translate('plugins.generic.library.disabled');
				$returner = false;
				break;
			/*case 'settings':
				if ($this->getEnabled()) {
					$this->import('ThesisSettingsForm');
					$form = new ThesisSettingsForm($this, $journal->getId());
					if (Request::getUserVar('save')) {
						$form->readInputData();
						if ($form->validate()) {
							$form->execute();
							Request::redirect(null, 'manager', 'plugin', array('generic', $this->getName(), 'theses'));
						} else {
							$this->setBreadCrumbs(true);
							$form->display();
						}
					} else {
						$this->setBreadCrumbs(true);
						$form->initData();
						$form->display();
					}
				} else {
					Request::redirect(null, 'manager');
				}
				break;
			case 'delete':
				if ($this->getEnabled()) {
					if (!empty($args)) {
						$thesisId = (int) $args[0];	
						$thesisDao =& DAORegistry::getDAO('ThesisDAO');

						// Ensure thesis is for this journal
						if ($thesisDao->getThesisJournalId($thesisId) == $journal->getId()) {
							$thesisDao->deleteThesisById($thesisId);
						}
					}
					Request::redirect(null, 'manager', 'plugin', array('generic', $this->getName(), 'theses'));
				} else {
					Request::redirect(null, 'manager');
				}
				break;
			case 'create':
			case 'edit':
				if ($this->getEnabled()) {
					$thesisId = !isset($args) || empty($args) ? null : (int) $args[0];
					$thesisDao =& DAORegistry::getDAO('ThesisDAO');

					// Ensure thesis is valid and for this journal
					if (($thesisId != null && $thesisDao->getThesisJournalId($thesisId) == $journal->getId()) || ($thesisId == null)) {
						$this->import('ThesisForm');

						if ($thesisId == null) {
							$templateMgr->assign('thesisTitle', 'plugins.generic.thesis.manager.createTitle');
						} else {
							$templateMgr->assign('thesisTitle', 'plugins.generic.thesis.manager.editTitle');	
						}

						$journalSettingsDao =& DAORegistry::getDAO('JournalSettingsDAO');
						$journalSettings =& $journalSettingsDao->getJournalSettings($journal->getId());

						$thesisForm = new ThesisForm($thesisId);
						$thesisForm->initData();
						$this->setBreadCrumbs(true);
						$templateMgr->assign('journalSettings', $journalSettings);
						$thesisForm->display();
					} else {
						Request::redirect(null, 'manager', 'plugin', array('generic', $this->getName(), 'theses'));
					}
				} else {
					Request::redirect(null, 'manager');
				}
				break;
			case 'update':
				if ($this->getEnabled()) {
					$this->import('ThesisForm');
					$thesisId = Request::getUserVar('thesisId') == null ? null : (int) Request::getUserVar('thesisId');
					$thesisDao =& DAORegistry::getDAO('ThesisDAO');

					if (($thesisId != null && $thesisDao->getThesisJournalId($thesisId) == $journal->getId()) || $thesisId == null) {

						$thesisForm = new ThesisForm($thesisId);
						$thesisForm->readInputData();

						if ($thesisForm->validate()) {
							$thesisForm->execute();

							if (Request::getUserVar('createAnother')) {
								Request::redirect(null, 'manager', 'plugin', array('generic', $this->getName(), 'create'));
							} else {
								Request::redirect(null, 'manager', 'plugin', array('generic', $this->getName(), 'theses'));
							}				
						} else {
							if ($thesisId == null) {
								$templateMgr->assign('thesisTitle', 'plugins.generic.thesis.manager.createTitle');
							} else {
								$templateMgr->assign('thesisTitle', 'plugins.generic.thesis.manager.editTitle');	
							}

							$journalSettingsDao =& DAORegistry::getDAO('JournalSettingsDAO');
							$journalSettings =& $journalSettingsDao->getJournalSettings($journal->getId());

							$this->setBreadCrumbs(true);
							$templateMgr->assign('journalSettings', $journalSettings);
							$thesisForm->display();
						}		
					} else {
						Request::redirect(null, 'manager', 'plugin', array('generic', $this->getName(), 'theses'));
					}
				} else {
					Request::redirect(null, 'manager');
				}	
				break;*/
			default:
				if ($this->getEnabled()) {
					/*$this->import('Thesis');
					$searchField = null;
					$searchMatch = null;
					$search = Request::getUserVar('search');
					$dateFrom = Request::getUserDateVar('dateFrom', 1, 1);
					if ($dateFrom !== null) $dateFrom = date('Y-m-d H:i:s', $dateFrom);
					$dateTo = Request::getUserDateVar('dateTo', 32, 12, null, 23, 59, 59);
					if ($dateTo !== null) $dateTo = date('Y-m-d H:i:s', $dateTo);

					if (!empty($search)) {
						$searchField = Request::getUserVar('searchField');
						$searchMatch = Request::getUserVar('searchMatch');
					}			

					$rangeInfo =& Handler::getRangeInfo('theses');
					$thesisDao =& DAORegistry::getDAO('ThesisDAO');
					$theses =& $thesisDao->getThesesByJournalId($journal->getId(), $searchField, $search, $searchMatch, $dateFrom, $dateTo, null, $rangeInfo);

					$templateMgr->assign('theses', $theses);
					$this->setBreadCrumbs();

					// Set search parameters
					$duplicateParameters = array(
						'searchField', 'searchMatch', 'search',
						'dateFromMonth', 'dateFromDay', 'dateFromYear',
						'dateToMonth', 'dateToDay', 'dateToYear'
					);
					foreach ($duplicateParameters as $param)
						$templateMgr->assign($param, Request::getUserVar($param));

					$templateMgr->assign('dateFrom', $dateFrom);
					$templateMgr->assign('dateTo', $dateTo);
					$templateMgr->assign('yearOffsetPast', THESIS_APPROVED_YEAR_OFFSET_PAST);

					$fieldOptions = Array(
						THESIS_FIELD_FIRSTNAME => 'plugins.generic.thesis.manager.studentFirstName',
						THESIS_FIELD_LASTNAME => 'plugins.generic.thesis.manager.studentLastName',
						THESIS_FIELD_EMAIL => 'plugins.generic.thesis.manager.studentEmail',
						THESIS_FIELD_DEPARTMENT => 'plugins.generic.thesis.manager.department',
						THESIS_FIELD_UNIVERSITY => 'plugins.generic.thesis.manager.university',
						THESIS_FIELD_TITLE => 'plugins.generic.thesis.manager.title',
						THESIS_FIELD_ABSTRACT => 'plugins.generic.thesis.manager.abstract',
						THESIS_FIELD_SUBJECT => 'plugins.generic.thesis.manager.keyword'
					);
					$templateMgr->assign('fieldOptions', $fieldOptions);

					$templateMgr->display($this->getTemplatePath() . 'theses.tpl');*/
				} else {
					Request::redirect(null, 'manager');
				}
		}
		return $returner;
	}

}
?>
