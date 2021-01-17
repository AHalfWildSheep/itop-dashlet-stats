<?php
class DashletGroupByPlusView{
	private $sValue;
	private $sTitle;
	private $sClass;
	private $sFilter;

	/**
	 * DashletGroupByPlusView constructor.
	 *
	 * @param $sTitle
	 * @param $sValue
	 * @param $sClass
	 * @param $sFilter
	 */
	public function __construct($sTitle, $sValue, $sClass, $sFilter) 
	{
		$this->sValue = $sValue;
		$this->sTitle = $sTitle;
		$this->sClass = $sClass;
		$this->sFilter = $sFilter;
	}
	public function Display($oPage, $sDashletId, $bEditMode)
	{
		
		$sHtmlTitle = $this->sTitle;
		$sHtmlValue = $this->sValue;
		$sHtmlIconUrl = MetaModel::GetClassIcon($this->sClass);
		$sLinkUrl = utils::GetAbsoluteUrlAppRoot()."pages/UI.php?operation=search&filter=".$sFilter = rawurlencode($this->sFilter->serialize());
		$oPage->add(
<<<HTML
<div id="$sDashletId" class="dashlet-content ahws-dashlet-stats">
	<a href="$sLinkUrl">
	<div class="ahws-dashlet-stats--icon">
		$sHtmlIconUrl
	</div>
	<div class="ahws-dashlet-stats--details">
		<h2 class="ahws-dashlet-stats--details--title">$sHtmlTitle</h2>
		<div class="ahws-dashlet-stats--details--value">$sHtmlValue</div>
	</div>
	</a>
</div>
HTML
		);

	}


}