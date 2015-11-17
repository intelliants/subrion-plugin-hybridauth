{if $core.config.hybrid_google || $core.config.hybrid_facebook || $core.config.hybrid_github}
	<hr>
	{if $core.config.hybrid_google}
		<a class="btn btn-google-plus" href="{$smarty.const.IA_URL}login/google/"><i class="icon-google-plus"></i> Connect with Google Plus</a>
	{/if}

	{if $core.config.hybrid_facebook}
		<a class="btn btn-facebook" href="{$smarty.const.IA_URL}login/facebook/"><i class="icon-facebook"></i> Connect with Facebook</a>
	{/if}

	{if $core.config.hybrid_github}
		<a class="btn btn-github" href="{$smarty.const.IA_URL}login/github/"><i class="icon-github"></i> Connect with Github</a>
	{/if}
{/if}