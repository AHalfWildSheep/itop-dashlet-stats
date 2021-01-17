<?php

class DashletStatsUIExtension implements iPageUIExtension
{

	/**
	 * @inheritdoc
	 */
	public function GetNorthPaneHtml(\iTopWebPage $oPage)
	{
		$oPage->add_saas('env-'.utils::GetCurrentEnvironment().'/ahws-dashlet-stats/css/dashletstats.scss');
	}

	/**
	 * @inheritdoc
	 */
	public function GetSouthPaneHtml(\iTopWebPage $oPage)
	{
		// Do nothing.
	}

	/**
	 * @inheritdoc
	 */
	public function GetBannerHtml(\iTopWebPage $oPage)
	{
		// Do nothing.
	}}