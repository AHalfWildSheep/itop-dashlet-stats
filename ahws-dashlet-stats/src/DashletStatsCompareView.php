<?php
class DashletStatsCompareView{
	private $sValue;
	private $iDeltaValue;
	private $sTitle;
	private $sClass;
	private $sFilter;

	/**
	 * DashletGroupByPlusView constructor.
	 *
	 * @param $sTitle
	 * @param $sValue
	 * @param $iDeltaValue
	 * @param $sClass
	 * @param $sFilter
	 */
	public function __construct($sTitle, $sValue, $iDeltaValue, $sClass, $sFilter, $sValueUnit, $sValueUnitPosition) 
	{
		$this->sValue = $sValue;
		$this->iDeltaValue = $iDeltaValue;
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
		$sHtmlIconUrl = MetaModel::GetClassIcon($this->sClass, false);
		$sHtmlUnit = $this->sValueUnit;
		$sHtmlUnitBefore = '';
		$sHtmlUnitAfter = '';
		if($this->sValueUnitPosition === 'before'){
			$sHtmlUnitBefore = '<span class="ahws-dashlet-stats--details--value--unit">'.$sHtmlUnit.'</span>';
		}
		else {
			$sHtmlUnitAfter = '<span class="ahws-dashlet-stats--details--value--unit">'.$sHtmlUnit.'</span>';
		}
		$sLinkUrl = utils::GetAbsoluteUrlAppRoot()."pages/UI.php?operation=search&filter=".$sFilter = rawurlencode($this->sFilter->serialize());
		
		$sDeltaIcon = '';
		if ($this->iDeltaValue > 0){
			$sDeltaIcon = '<span class="ahws-dashlet-compare-stats--delta ahws-dashlet-compare-stats--delta-positive"><i class="fas fa-arrow-up"></i>'.$this->iDeltaValue.'</span>';
		}
		else if ($this->iDeltaValue < 0){

			$sDeltaIcon = '<span class="ahws-dashlet-compare-stats--delta ahws-dashlet-compare-stats--delta-negative"><i class="fas fa-arrow-down"></i>'.$this->iDeltaValue.'</span>';
		}
		else{
			$sDeltaIcon = '<span class="ahws-dashlet-compare-stats--delta ahws-dashlet-compare-stats--delta-neutral"><i class="fas fa-equals"></i>'.$this->iDeltaValue.'</span>';
		}
		return
<<<HTML
<div id="$sDashletId" class="ibo-dashlet ahws-dashlet-stats">
	<a href="$sLinkUrl">
	<div class="ibo-dashlet-badge--icon-container">
		<img src="$sHtmlIconUrl" class="ibo-dashlet-badge--icon ibo-class-icon ibo-is-medium"/>
	</div>
	<div class="ahws-dashlet-stats--details">
		<div class="ahws-dashlet-stats--details--value">$sHtmlUnitBefore<span class="ahws-dashlet-stats--details--value--realvalue">$sHtmlValue</span>$sHtmlUnitAfter $sDeltaIcon</div>
		<div class="ahws-dashlet-stats--details--title">$sHtmlTitle</div>
	</div>
	</a>
</div>
HTML
		;
	}


}