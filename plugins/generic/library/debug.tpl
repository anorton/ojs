
{strip}
{assign var="pageTitle" value="debug"}
{include file="common/header.tpl"}
{/strip}

<div id="myLibrary">
{include file="common/formErrors.tpl"}
{$libdebug}
<h4>Debug</h4>

{$selectArticles|@debug_print_var}
</div>

{include file="common/footer.tpl"}