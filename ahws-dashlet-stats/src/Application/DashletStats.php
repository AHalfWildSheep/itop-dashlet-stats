<?php
use AHalfWildSheep\iTop\Extension\DashletStats\Controller\DashletStatsView;
use AHalfWildSheep\iTop\Extension\DashletStats\Helper\StatsHelper;
use Dashlet;
use DBObjectSearch;
use DBObjectSet;
use DesignerComboField;
use DesignerForm;
use DesignerFormSelectorField;
use DesignerLongTextField;
use DesignerTextField;
use Dict;
use Exception;
use MetaModel;
use utils;

class DashletStats extends Dashlet
{
	public function __construct($oModelReflection, $sId)
	{
		parent::__construct($oModelReflection, $sId);
		$this->aProperties['title'] = '';
		$this->aProperties['query'] = 'SELECT ';
		$this->aProperties['function'] = 'count';
		$this->aProperties['function_attribute'] = '';
		$this->aProperties['unit'] = '';
		$this->aProperties['unit_position'] = 'after';
		$this->aProperties['percentage_query'] = '';
		$this->aProperties['precision'] = 2;
		$this->aProperties['divided_by'] = 1;
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
			'label' => Dict::S('UI:DashletStats:Label'),
			'icon' => 'env-'.utils::GetCurrentEnvironment().'/ahws-dashlet-stats/img/icons8-math-96.png',
			'description' => Dict::S('UI:DashletStats:Description'),
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
	 * @param string $sOql
	 *
	 * @return array
	 */
	protected function GetNumericAttributes($sOql)
	{
		$aFunctionAttributes = array();
		try
		{
			$oQuery = $this->oModelReflection->GetQuery($sOql);
			$sClass = $oQuery->GetClass();
			if (is_null($sClass))
			{
				return $aFunctionAttributes;
			}
			foreach($this->oModelReflection->ListAttributes($sClass) as $sAttCode => $sAttType)
			{
				switch ($sAttType)
				{
					case 'AttributeDecimal':
					case 'AttributeDuration':
					case 'AttributeInteger':
					case 'AttributePercentage':
					case 'AttributeSubItem': // TODO: Known limitation: no unit displayed (values in sec)
						$sLabel = $this->oModelReflection->GetLabel($sClass, $sAttCode);
						$aFunctionAttributes[$sAttCode] = $sLabel;
						break;
					case 'AttributeString':
					case 'AttributeEnum':
					case 'AttributeText':
					case 'AttributeLongText':
					case 'AttributeEncryptedString':
						if(MetaModel::GetModuleSetting('ahws-dashlet-stats', 'allow_string_attribute', 'false') === true)
						{
							$sLabel = $this->oModelReflection->GetLabel($sClass, $sAttCode);
							$aFunctionAttributes[$sAttCode] = $sLabel;
						}
						break;
				}
			}
		}
		catch (Exception $e)
		{
			// In case the OQL is bad
		}

		return $aFunctionAttributes;
	}
	
	public function Update($aValues, $aUpdatedFields)
	{
		if (in_array('query', $aUpdatedFields) || in_array('function', $aUpdatedFields))
		{
			$this->bFormRedrawNeeded = true;
		}
		return parent::Update($aValues, $aUpdatedFields);
	}

	/**
	 * @inheritdoc
	 *
	 * @throws \CoreException
	 * @throws \ArchivedObjectException
	 */
	public function Render($oPage, $bEditMode = false, $aExtraParams = array())
	{
		$aStatsExtraParams = array();
		$sTitle = $this->aProperties['title'];
		$sQuery = $this->aProperties['query'];
		$sFunction = $this->aProperties['function'];
		$sUnit = ($this->aProperties['function'] !== 'percentage' ? $this->aProperties['unit'] : '%');
		$sUnitPosition = $this->aProperties['unit_position'];
		$aStatsExtraParams['attr'] = $this->aProperties['function_attribute'];
		$aStatsExtraParams['percentage_query'] = $this->aProperties['percentage_query'];


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
		
		$aStatsExtraParams['query_params'] = $aQueryParams;
		
		$sDashletValue = StatsHelper::FormatValue(StatsHelper::ComputeStats($sFunction, $oSet, $aStatsExtraParams),
			$this->aProperties['precision'],
			$this->aProperties['divided_by']
		);
		
		return new DashletStatsView('block_'.$this->sId.($bEditMode ? '_edit' : ''), $sTitle, $sDashletValue, $sClass, $oFilter, $sUnit, $sUnitPosition);
	}
}