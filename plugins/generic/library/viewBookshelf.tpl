{**
 * library.tpl
 *
 * Copyright (c) 2003-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display my Library.
 *
 * $Id: library.tpl,v 1.10 2009/05/26 01:31:17 mcrider Exp $
 *}
{strip}
{translate|assign:"pageTitle" key="plugins.generic.library.bookshelf"}
{assign var="pageTitle2" value=$bookshelfName}
{assign var="pageTitleTranslated" value="$pageTitle: $pageTitle2"}
{include file="common/header.tpl"}
{/strip}

<div id="myLibrary">
{include file="common/formErrors.tpl"}
<p>
	<form name="deleteBookshelfForm" method="post" action="{url page="library" op="deleteBookshelf" path=$bookshelf->getBookshelfId()}">
		<input type="hidden" name="bookshelfId" value="{$bookshelf->getBookshelfId()}" />
		<input type="submit" name="deleteBookshelfBtn" value="Delete Bookshelf" />
	</form>
</p>
{/*$selectArticles|@debug_print_var*/}
{if $articleList}
	<!--list items on bookshelf-->
	<h4>Contents:</h4>
	<p>Click on table headings to sort.</p>
	<form name="adminBookshelfForm" method="post" action="{url page="library" op="adminBookshelf"}">
		<input type="hidden" name="bookshelfId" value="{$bookshelf->getBookshelfId()}" />
		<input type="submit" name="submitBtn" value="Remove Selected Articles" />			
		<table width="100%" class="sortable">
			<tr>
				<th>Select</th>
				<th>Title</th>
				<th>Author(s)</th>
				<th>Date Published</th>
			</tr>
				{foreach from=$articleList item=article}
					<tr>
						<td><input type="checkbox" name="selectArticles[]" value="{$article->bookshelvedItemsId}" /></td>
						<td><a href="{$article->baseUrl}/article/view/{$article->getArticleId()}">{$article->getArticleTitle()}</a></td>
						<td>{$article->getAuthorString()}</td>
						<td>{$article->getLastModified()}</td>

					</tr>		
				{/foreach}
		</table>
		<input type="submit" name="submitBtn" value="Remove Selected Articles" />
	</form>
{else}
	You do not have any items in this Bookshelf. 
{/if}

</div>

{include file="common/footer.tpl"}