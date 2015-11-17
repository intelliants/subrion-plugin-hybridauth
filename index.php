<?php
//##copyright##

if (iaView::REQUEST_HTML == $iaView->getRequestType())
{
	require_once IA_PLUGINS . 'hybridauth/includes/Hybrid/Auth.php';
	require_once IA_PLUGINS . 'hybridauth/includes/Hybrid/Endpoint.php';

	Hybrid_Endpoint::process();
}