<?php
class DashletStatsView{
	private $sValue;
	private $sTitle;
	private $sClass;
	private $sFilter;
	private $sValueUnit;
	private $sValueUnitPosition;

	/**
	 * DashletGroupByPlusView constructor.
	 *
	 * @param $sTitle
	 * @param $sValue
	 * @param $sClass
	 * @param $sFilter
	 */
	public function __construct($sTitle, $sValue, $sClass, $sFilter, $sValueUnit, $sValueUnitPosition) 
	{
		$this->sValue = $sValue;
		$this->sTitle = $sTitle;
		$this->sClass = $sClass;
		$this->sFilter = $sFilter;
		$this->sValueUnit = $sValueUnit;
		$this->sValueUnitPosition = $sValueUnitPosition;
	}
	public function Display($oPage, $sDashletId, $bEditMode)
	{
		
		$sHtmlTitle = $this->sTitle;
		$sHtmlValue = $this->sValue;
		$sHtmlUnit = $this->sValueUnit;
		$sHtmlUnitBefore = '';
		$sHtmlUnitAfter = '';
		if($this->sValueUnitPosition === 'before'){
			$sHtmlUnitBefore = '<span class="ahws-dashlet-stats--details--value--unit">'.$sHtmlUnit.'</span>';
		}
		else {
			$sHtmlUnitAfter = '<span class="ahws-dashlet-stats--details--value--unit">'.$sHtmlUnit.'</span>';
		}
		$sHtmlIconUrl = MetaModel::GetClassIcon($this->sClass, false);
		$sLinkUrl = utils::GetAbsoluteUrlAppRoot()."pages/UI.php?operation=search&filter=".$sFilter = rawurlencode($this->sFilter->serialize());
		return
<<<HTML
<div id="$sDashletId" class="ibo-dashlet ahws-dashlet-stats">
	<a href="$sLinkUrl">
	<div class="ibo-dashlet-badge--icon-container">
		<img src="$sHtmlIconUrl" class="ibo-dashlet-badge--icon ibo-class-icon ibo-is-medium"/>
	</div>
	<div class="ahws-dashlet-stats--details">
		<div class="ahws-dashlet-stats--details--value">$sHtmlUnitBefore<span class="ahws-dashlet-stats--details--value--realvalue">$sHtmlValue</span>$sHtmlUnitAfter</div>
		<div class="ahws-dashlet-stats--details--title">$sHtmlTitle</div>
	</div>
	</a>
</div>
HTML
		;

	}


}