<?php
use AHalfWildSheep\iTop\Extension\DashletStats\Controller\DashletStatsCompareView;
use AHalfWildSheep\iTop\Extension\DashletStats\Helper\StatsHelper;
use DBObjectSearch;
use DBObjectSet;
use DesignerComboField;
use DesignerForm;
use DesignerFormSelectorField;
use DesignerLongTextField;
use DesignerTextField;
use Dict;
use MetaModel;
use utils;

class DashletStatsCompare extends DashletStats{
	public function __construct($oModelReflection, $sId)
	{
		parent::__construct($oModelReflection, $sId);
		$this->aProperties['compare_query'] = 'SELECT ';
		$this->aProperties['compare_unit'] = '';
		$this->aProperties['percentage_compare_query'] = 'SELECT ';
		$this->aCSSClasses[] = 'ibo-dashlet--is-inline';
		$this->aCSSClasses[] = 'ibo-dashlet-badge';
		$this->aCSSClasses[] = 'ahws-dashlet-stats';
	}

	/**
	 * @inheritdoc
	 */
	static public function GetInfo()
	{
		return array(
			'label' => Dict::S('UI:DashletStatsCompare:Label'),
			'icon' => 'env-'.utils::GetCurrentEnvironment().'/ahws-dashlet-stats/img/icons8-account-96.png',
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
		// Functions with attribute
		foreach($aFunctionsWithAttribute as $sFct => $sLabel)
		{
			$oSubForm = new DesignerForm();
			$oField = new DesignerComboField('function_attribute', Dict::S('UI:DashletStats:Prop:FunctionAttribute'), $this->aProperties['function_attribute']);
			$oField->SetMandatory();
			$oField->SetAllowedValues($aFunctionAttributes);
			$oSubForm->AddField($oField);
			$oField = new DesignerTextField('unit', Dict::S('UI:DashletStats:Prop:Unit'), $this->aProperties['unit']);
			$oSubForm->AddField($oField);
			$oField = new DesignerComboField('unit_position', Dict::S('UI:DashletStats:Prop:UnitPosition'), $this->aProperties['unit_position']);
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

		$oField = new DesignerIntegerField('precision', Dict::S('UI:DashletStats:Prop:Function:Precision'), $this->aProperties['precision']);
		$oField->SetBoundaries(null, null);
		$oField->SetMandatory();
		$oForm->AddField($oField);

		$oField = new DesignerTextField('divided_by', Dict::S('UI:DashletStats:Prop:Function:DividedBy'), $this->aProperties['divided_by']);
		$oField->SetMandatory();
		$oForm->AddField($oField);
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
		$sUnit = ($this->aProperties['function'] !== 'percentage' ? $this->aProperties['unit'] : '%');
		$sUnitPosition = $this->aProperties['unit_position'];
		$sCompareQuery = $this->aProperties['compare_query'];
		
		$aStatsExtraParams['attr'] = $this->aProperties['function_attribute'];
		$aStatsExtraParams['percentage_query'] = $this->aProperties['percentage_query'];
		$aStatsExtraParams['compare_unit'] = $this->aProperties['compare_unit'];
		$aStatsExtraParams['percentage_compare_query'] = $this->aProperties['percentage_compare_query'];

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
		$aStatsExtraParams['query_params'] = $aQueryParams;
 
		list($sDashletValue, $iDashletDelta) = StatsHelper::ComputeCompareStats($sFunction, $oSet, $oCompareSet, $aStatsExtraParams);
		$sDashletValue = StatsHelper::FormatValue($sDashletValue, $this->aProperties['precision'], $this->aProperties['divided_by']);
		$iDashletDelta = StatsHelper::FormatValue($iDashletDelta, $this->aProperties['precision'], $this->aProperties['divided_by']);
		
		if($sFunction === 'percentage'){
			$aStatsExtraParams['compare_unit'] = 'percentage';
		}
		
		return new DashletStatsCompareView('block_'.$this->sId.($bEditMode ? '_edit' : ''), $sTitle, $sDashletValue, $iDashletDelta, $sClass, $oFilter, $sUnit, $sUnitPosition, $aStatsExtraParams['compare_unit']);
	}
}
