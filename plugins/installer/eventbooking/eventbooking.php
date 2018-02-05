<?php
defined('_JEXEC') or die;

class plgInstallerEventbooking extends JPlugin
{
	public function onInstallerBeforePackageDownload(&$url, &$headers)
	{
		$uri = JUri::getInstance($url);

		$host       = $uri->getHost();
		$validHosts = array('joomdonation.com', 'www.joomdonation.com');
		if (!in_array($host, $validHosts))
		{
			return true;
		}

		$documentId = $uri->getVar('document_id');

		if ($documentId != 56)
		{
			return true;
		}

		// Get Download ID and append it to the URL

		// Require library + register autoloader
		require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';
		$config = EventbookingHelper::getConfig();

		// Append the Download ID to the download URL
		if (!empty($config->download_id))
		{
			$uri->setVar('download_id', $config->download_id);
			$url = $uri->toString();

			// Append domain to URL for logging
			$siteUri = JUri::getInstance();
			$uri->setVar('domain', $siteUri->getHost());

			$uri->setVar('version', EventbookingHelper::getInstalledVersion());

			$url = $uri->toString();
		}

		return true;
	}
}
