<?php 
class DashletStatsCompare extends DashletStats{
	public function __construct($oModelReflection, $sId)
	{
		parent::__construct($oModelReflection, $sId);
		$this->aProperties['compare_query'] = 'SELECT ';
		$this->aProperties['compare_unit'] = '';
		$this->aProperties['percentage_compare_query'] = 'SELECT ';
	}

	/**
	 * @inheritdoc
	 */
	static public function GetInfo()
	{
		return array(
			'label' => Dict::S('UI:DashletStatsCompare:Label'),
			'icon' => 'env-'.utils::GetCurrentEnvironment().'/ahws-dashlet-stats/img/icons8-statistics-48.png',
			'description' => Dict::S('UI:DashletStatsCompare:Description'),
		);
	}

	/**
	 * @inheritdoc
	 */
	public function GetPropertiesFields(DesignerForm $oForm)
	{
		$oField = new DesignerTextField('title', Dict::S('UI:DashletStats:Prop:Title'), $this->aProperties['title']);
		$oForm->AddField($oField);

		$oField = new DesignerLongTextField('query', Dict::S('UI:DashletStats:Prop:Query'), $this->aProperties['query']);
		$oField->SetMandatory();
		$oForm->AddField($oField);
		
		$oField = new DesignerLongTextField('compare_query', Dict::S('UI:DashletStatsCompare:Prop:CompareQuery'), $this->aProperties['compare_query']);
		$oField->SetMandatory();
		$oForm->AddField($oField);

		$oField = new DesignerComboField('compare_unit', Dict::S('UI:DashletStatsCompare:Prop:CompareUnit'), $this->aProperties['compare_unit']);
		$oField->SetMandatory();
		$oField->SetAllowedValues(array('delta' => Dict::S('UI:DashletStatsCompare:Prop:CompareUnit:Difference'), 'percentage' => Dict::S('UI:DashletStatsCompare:Prop:CompareUnit:Percentage')));
		$oForm->AddField($oField);

		$aFunctionsWithAttribute = array(
			'avg' =>  Dict::S('UI:DashletStats:Prop:Function:Avg'),
			'max' => Dict::S('UI:DashletStats:Prop:Function:Max'),
			'min' => Dict::S('UI:DashletStats:Prop:Function:Min'),
			'sum' => Dict::S('UI:DashletStats:Prop:Function:Sum'),
		);

		$oSelectorField = new DesignerFormSelectorField('function', Dict::S('UI:DashletStats:Prop:Function'), $this->aProperties['function']);

		$oForm->AddField($oSelectorField);
		$oSelectorField->SetMandatory();
		// Count sub-menu
		$oSubForm = new DesignerForm();
		$oSelectorField->AddSubForm($oSubForm, Dict::S('UI:DashletStats:Prop:Function:Count'), 'count');

		$aFunctionAttributes = $this->GetNumericAttributes($this->aProperties['query']);
		IssueLog::Info(json_encode($aFunctionAttributes));
		// Functions with attribute
		foreach($aFunctionsWithAttribute as $sFct => $sLabel)
		{
			$oSubForm = new DesignerForm();
			$oField = new DesignerComboField('function_attribute', Dict::S('UI:DashletStats:Prop:FunctionAttribute'), $this->aProperties['function_attribute']);
			$oField->SetMandatory();
			$oField->SetAllowedValues($aFunctionAttributes);
			$oSubForm->AddField($oField);
			$oField = new DesignerTextField('unit', Dict::S('UI:DashletStats:Prop:Unit'), $this->aProperties['unit']);
			$oField->SetMandatory();
			$oSubForm->AddField($oField);
			$oField = new DesignerComboField('unit_position', Dict::S('UI:DashletStats:Prop:UnitPosition'), $this->aProperties['unit_position']);
			$oField->SetMandatory();
			$oField->SetAllowedValues(array('before' => Dict::S('UI:DashletStats:Prop:UnitPosition:Before'), 'after' => Dict::S('UI:DashletStats:Prop:UnitPosition:After')));
			$oSubForm->AddField($oField);
			$oSelectorField->AddSubForm($oSubForm, $sLabel, $sFct);
		}
		$oSubForm = new DesignerForm();
		$oField = new DesignerLongTextField('percentage_query', Dict::S('UI:DashletStats:Prop:Function:PercentageQuery'), $this->aProperties['percentage_query']);
		$oField->SetMandatory();
		$oSubForm->AddField($oField);
		$oField = new DesignerLongTextField('percentage_compare_query', Dict::S('UI:DashletStatsCompare:Prop:PercentageCompareQuery'), $this->aProperties['percentage_compare_query']);
		$oField->SetMandatory();
		$oSubForm->AddField($oField);
		$oSelectorField->AddSubForm($oSubForm, Dict::S('UI:DashletStats:Prop:Function:Percentage'), 'percentage');

	}

	/**
	 * @inheritdoc
	 *
	 * @throws \CoreException
	 * @throws \ArchivedObjectException
	 */
	public function Render($oPage, $bEditMode = false, $aExtraParams = array())
	{
		$sTitle = $this->aProperties['title'];
		$sQuery = $this->aProperties['query'];
		$sFunction = $this->aProperties['function'];
		$sAttr = $this->aProperties['function_attribute'];
		$sUnit = ($this->aProperties['function'] !== 'percentage' ? $this->aProperties['unit'] : '%');
		$sUnitPosition = $this->aProperties['unit_position'];
		$sPercentageQuery = $this->aProperties['percentage_query'];
		
		$sCompareUnit = $this->aProperties['compare_unit'];
		$sCompareQuery = $this->aProperties['compare_query'];
		$sPercentageCompareQuery = $this->aProperties['percentage_compare_query'];

		

		// First perform the query - if the OQL is not ok, it will generate an exception : no need to go further
		if (isset($aExtraParams['query_params']))
		{
			$aQueryParams = $aExtraParams['query_params'];
		}
		elseif (isset($aExtraParams['this->class']) && isset($aExtraParams['this->id']))
		{
			$oObj = MetaModel::GetObject($aExtraParams['this->class'], $aExtraParams['this->id']);
			$aQueryParams = $oObj->ToArgsForQuery();
		}
		else
		{
			$aQueryParams = array();
		}
		$oFilter = DBObjectSearch::FromOQL($sQuery, $aQueryParams);
		$oFilter->SetShowObsoleteData(utils::ShowObsoleteData());

		$sClass = $oFilter->GetClass();

		$oSet = new DBObjectSet($oFilter);

		$oCompareFilter = DBObjectSearch::FromOQL($sCompareQuery, $aQueryParams);
		$oCompareFilter->SetShowObsoleteData(utils::ShowObsoleteData());
		
		$oCompareSet = new DBObjectSet($oCompareFilter);
		
		$sDashletValue = 0;
		$sDashletDelta = 0;
		switch($sFunction){
			case 'count':
				$iCount = $oSet->Count();
				$sDashletValue = $iCount;
				
				$iCompareCount = $oCompareSet->Count();
				$sDashletDelta = ($sCompareUnit === 'delta' ? $iCount - $iCompareCount : round((($iCount * 100) / $iCompareCount), 2)) - 100;
				break;
			case 'max':
				$iMaxValue = null;
				while($oObject = $oSet->Fetch())
				{
					$iMaxValue = ($iMaxValue === null ? $oObject->Get($sAttr) : max($iMaxValue, $oObject->Get($sAttr)));

				}
				$iCompareMaxValue = null;
				while($oCompareObject = $oCompareSet->Fetch())
				{
					$iCompareMaxValue = ($iCompareMaxValue === null ? $oCompareObject->Get($sAttr) : max($iCompareMaxValue, $oCompareObject->Get($sAttr)));

				}
			
				$sDashletValue = $iMaxValue;
				$sDashletDelta = ($sCompareUnit === 'delta' ? $iMaxValue - $iCompareMaxValue : round((($iMaxValue * 100) / $iCompareMaxValue), 2)) - 100;
				break;
			case 'min':
				$iMinValue = null;
				while($oObject = $oSet->Fetch())
				{
					$iMinValue = ($iMinValue === null ? $oObject->Get($sAttr) : min($iMinValue, $oObject->Get($sAttr)));
				}
				$iCompareMinValue = null;
				while($oCompareObject = $oCompareSet->Fetch())
				{
					$iCompareMinValue = ($iCompareMinValue === null ? $oCompareObject->Get($sAttr) : min($iCompareMinValue, $oCompareObject->Get($sAttr)));

				}
			
				$sDashletValue = $iMinValue;
				$sDashletDelta = ($sCompareUnit === 'delta' ?  $iMinValue - $iCompareMinValue: round((($iMinValue * 100) / $iCompareMinValue), 2)) - 100;
				break;
			case 'avg':
				$iCount = $oSet->Count();
				$iTotalValue = null;
				while($oObject = $oSet->Fetch())
				{
					$iTotalValue = ($iTotalValue === null ? $oObject->Get($sAttr) : $iTotalValue + $oObject->Get($sAttr));

				}

				$iCompareCount = $oCompareSet->Count();
				$iCompareTotalValue = null;
				while($oCompareObject = $oCompareSet->Fetch())
				{
					$iCompareTotalValue = ($iCompareTotalValue === null ? $oCompareObject->Get($sAttr) : $iCompareTotalValue + $oCompareObject->Get($sAttr));
				}
			
				$sDashletValue = round($iTotalValue/$iCount, 2);
				$sDashletDelta = ($sCompareUnit === 'delta' ?  round(($iTotalValue/$iCount - $iCompareTotalValue/$iCompareCount), 2) : round(((($iTotalValue/$iCount) * 100) / ($iCompareTotalValue/$iCompareCount)), 2) - 100) ;
				break;
			case 'sum':
				$iTotalValue = null;
				while($oObject = $oSet->Fetch())
				{
					$iTotalValue = ($iTotalValue === null ? $oObject->Get($sAttr) : $iTotalValue + $oObject->Get($sAttr));
				}
				$iCompareTotalValue = null;
				while($oCompareObject = $oCompareSet->Fetch())
				{
					$iCompareTotalValue = ($iCompareTotalValue === null ? $oCompareObject->Get($sAttr) : $iCompareTotalValue + $oCompareObject->Get($sAttr));
				}
			
				$sDashletValue = $iTotalValue;
				$sDashletDelta =  ($sCompareUnit === 'delta' ? $iTotalValue - $iCompareTotalValue: round((($iTotalValue * 100) / $iCompareTotalValue), 2) - 100);;
				break;
			case 'percentage':
				$oPercentageFilter = DBObjectSearch::FromOQL($sPercentageQuery, $aQueryParams);
				$oPercentageFilter->SetShowObsoleteData(utils::ShowObsoleteData());
				$oPercentageSet = new DBObjectSet($oPercentageFilter);

				$oComparePercentageFilter = DBObjectSearch::FromOQL($sPercentageCompareQuery, $aQueryParams);
				$oComparePercentageFilter->SetShowObsoleteData(utils::ShowObsoleteData());
				$oComparePercentageSet = new DBObjectSet($oComparePercentageFilter);
				
				
				
				$sDashletValue = round((($oSet->Count() * 100) / $oPercentageSet->Count()), 2);
				$sDashletDelta = (($oSet->Count() * 100) / $oPercentageSet->Count()) - (($oCompareSet->Count() * 100) / $oComparePercentageSet->Count());
				$sCompareUnit = 'percentage';
				break;
		}
		$sDashletDelta = ($sCompareUnit === 'delta' ? $sDashletDelta : ($sUnitPosition === 'before' ? '%'.$sDashletDelta : $sDashletDelta.'%'));
		$sDashletValue = ($sUnitPosition === 'before' ? $sUnit.$sDashletValue : $sDashletValue.$sUnit);


		$oDashletView = new DashletStatsCompareView($sTitle,	$sDashletValue,$sDashletDelta, $sClass, $oFilter);
		$oDashletView->Display($oPage, 'block_'.$this->sId.($bEditMode ? '_edit' : ''),	$bEditMode);
	}
}
