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
{assign var="pageTitle" value="plugins.generic.library.myLibrary"}
{include file="common/header.tpl"}
{/strip}

<div id="myLibrary">
{include file="common/formErrors.tpl"}
{/*$hasLibrary|@debug_print_var*/}
<h4>Bookshelves</h4>
<!-- 
If user does not have bookshelves, prompt to create one.
else if user has one+ bookshelf, list bookshelf(s).
-->
{/*$bookshelfList|@debug_print_var*/}
{if $bookshelfList}
	<!--list bookshelves-->
	<form action="{url page="library" op="createBookshelf"}" method="post" name="createBookshelf">
	Create a New Bookshelf! Enter Bookshelf Name: <input type="text" name="bookshelfName" /><input type="submit" value="Create" />
	</form>
	<ul>
	{foreach from=$bookshelfList item=bookshelf}
		<li>
			<a href="{url page="library" op="viewBookshelf" path=$bookshelf->getBookshelfId()}" target="_parent">
			{$bookshelf->getBookshelfName()|escape|default:"&nbsp;"}</a>
		</li>
	{/foreach}
	</ul>
{else}
	You do not have any Bookshelves. Create one now:
	<form action="{url page="library" op="createBookshelf"}" method="post" name="createBookshelf">
	Bookshelf Name: <input type="text" name="bookshelfName" /><input type="submit" value="Create" />
	</form>
{/if}

</div>

{include file="common/footer.tpl"}