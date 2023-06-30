<?php
class DashletStatsUIExtension implements iBackofficeLinkedStylesheetsExtension
{
	public function GetLinkedStylesheetsAbsUrls(): array
	{
		return [utils::GetAbsoluteUrlAppRoot().'env-'.utils::GetCurrentEnvironment().'/ahws-dashlet-stats/css/dashletstats.css'];
	}
}