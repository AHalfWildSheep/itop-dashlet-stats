<?php

namespace AHalfWildSheep\iTop\Extension\DashletStats\Helper;

use DBObjectSearch;
use DBObjectSet;
use Dict;
use utils;

class StatsHelper {
	
	public static function ComputeStats($sFunction, $oSet, $aStatsExtraParams){
		$sAttr = $aStatsExtraParams['attr'];
		$sPercentageQuery = $aStatsExtraParams['percentage_query'];
		$aQueryParams = $aStatsExtraParams['query_params'] ;

		$StatsValue = Dict::S('UI:DashletStats:Value');
		
		switch($sFunction){
			case 'count':
				$iCount = $oSet->Count();
				$StatsValue = $iCount;
				break;
			case 'max':
				$iMaxValue = null;
				while($oObject = $oSet->Fetch())
				{
					$iObjectValue = $oObject->Get($sAttr);
					$iObjectValue = is_numeric($iObjectValue) ? $iObjectValue : 0;
	
					$iMaxValue = ($iMaxValue === null ? $iObjectValue : max($iMaxValue, $iObjectValue));
	
				}
				$StatsValue = $iMaxValue;
				break;
			case 'min':
				$iMinValue = null;
				while($oObject = $oSet->Fetch())
				{
					$iObjectValue = $oObject->Get($sAttr);
					$iObjectValue = is_numeric($iObjectValue) ? $iObjectValue : 0;
	
					$iMinValue = ($iMinValue === null ? $iObjectValue : min($iMinValue, $iObjectValue));
				}
				$StatsValue = $iMinValue;
				break;
			case 'avg':
				$iCount = $oSet->Count();
				$iTotalValue = null;
				while($oObject = $oSet->Fetch())
				{
					$iObjectValue = $oObject->Get($sAttr);
					$iObjectValue = is_numeric($iObjectValue) ? $iObjectValue : 0;
	
					$iTotalValue = ($iTotalValue === null ? $iObjectValue : $iTotalValue + $iObjectValue);
				}
				if($iCount !== 0)
				{
					$StatsValue = $iTotalValue/$iCount;
				}
				break;
			case 'sum':
				$iTotalValue = null;
				while($oObject = $oSet->Fetch())
				{
					$iObjectValue = $oObject->Get($sAttr);
					$iObjectValue = is_numeric($iObjectValue) ? $iObjectValue : 0;
					$iTotalValue = ($iTotalValue === null ? $iObjectValue : $iTotalValue + $iObjectValue);
				}
				$StatsValue = $iTotalValue;
				break;
			case 'percentage':
				$oCompareFilter = DBObjectSearch::FromOQL($sPercentageQuery, $aQueryParams);
				$oCompareFilter->SetShowObsoleteData(utils::ShowObsoleteData());
				$oCompareSet = new DBObjectSet($oCompareFilter);
				if($oCompareSet->Count() !== 0){
					$StatsValue = round((($oSet->Count() * 100) / $oCompareSet->Count()), 2);
				}
				break;
		}
		return $StatsValue;
	}
	public static function ComputeCompareStats($sFunction, $oSet, $oCompareSet, $aStatsExtraParams){
		$sAttr = $aStatsExtraParams['attr'];
		$sPercentageQuery = $aStatsExtraParams['percentage_query'];
		$aQueryParams = $aStatsExtraParams['query_params'] ;
		$sCompareUnit = $aStatsExtraParams['compare_unit'];
		$sPercentageCompareQuery = $aStatsExtraParams['percentage_compare_query'];


		$sDashletValue = Dict::S('UI:DashletStats:Value');
		$sDashletDelta = '0';

		switch($sFunction){
			case 'count':
				$iCount = $oSet->Count();
				$sDashletValue = $iCount;

				$iCompareCount = $oCompareSet->Count();
				if($sCompareUnit === 'delta' ){
					$sDashletDelta = $iCount - $iCompareCount;
				}
				elseif ($iCompareCount !== 0)
				{
					$sDashletDelta = round((($iCount * 100) / $iCompareCount), 2) - 100;
				}
				break;
			case 'max':
				$iMaxValue = null;
				while($oObject = $oSet->Fetch())
				{
					$iObjectValue = $oObject->Get($sAttr);
					$iObjectValue = is_numeric($iObjectValue) ? $iObjectValue : 0;

					$iMaxValue = ($iMaxValue === null ? $iObjectValue : max($iMaxValue, $iObjectValue));
				}
				$iCompareMaxValue = null;
				while($oCompareObject = $oCompareSet->Fetch())
				{
					$iObjectValue = $oCompareObject->Get($sAttr);
					$iObjectValue = is_numeric($iObjectValue) ? $iObjectValue : 0;

					$iCompareMaxValue = ($iCompareMaxValue === null ? $iObjectValue : max($iCompareMaxValue, $iObjectValue));

				}

				$sDashletValue = $iMaxValue;
				if($sCompareUnit === 'delta' ){
					$sDashletDelta = $iMaxValue - $iCompareMaxValue;
				}
				elseif ($iCompareMaxValue !== 0)
				{
					$sDashletDelta = round((($iMaxValue * 100) / $iCompareMaxValue), 2) - 100;
				}
				break;
			case 'min':
				$iMinValue = null;
				while($oObject = $oSet->Fetch())
				{
					$iObjectValue = $oObject->Get($sAttr);
					$iObjectValue = is_numeric($iObjectValue) ? $iObjectValue : 0;

					$iMinValue = ($iMinValue === null ? $iObjectValue : min($iMinValue, $iObjectValue));
				}
				$iCompareMinValue = null;
				while($oCompareObject = $oCompareSet->Fetch())
				{
					$iObjectValue = $oCompareObject->Get($sAttr);
					$iObjectValue = is_numeric($iObjectValue) ? $iObjectValue : 0;

					$iCompareMinValue = ($iCompareMinValue === null ? $iObjectValue : min($iCompareMinValue, $iObjectValue));

				}

				$sDashletValue = $iMinValue;
				if($sCompareUnit === 'delta' ){
					$sDashletDelta = $iMinValue - $iCompareMinValue;
				}
				elseif ($iCompareMinValue !== 0)
				{
					$sDashletDelta = round((($iMinValue * 100) / $iCompareMinValue), 2) - 100;
				}
				break;
			case 'avg':
				$iCount = $oSet->Count();
				$iTotalValue = null;
				while($oObject = $oSet->Fetch())
				{
					$iObjectValue = $oObject->Get($sAttr);
					$iObjectValue = is_numeric($iObjectValue) ? $iObjectValue : 0;

					$iTotalValue = ($iTotalValue === null ? $iObjectValue : $iTotalValue + $iObjectValue);

				}

				$iCompareCount = $oCompareSet->Count();
				$iCompareTotalValue = null;
				while($oCompareObject = $oCompareSet->Fetch())
				{
					$iObjectValue = $oCompareObject->Get($sAttr);
					$iObjectValue = is_numeric($iObjectValue) ? $iObjectValue : 0;

					$iCompareTotalValue = ($iCompareTotalValue === null ? $iObjectValue : $iCompareTotalValue + $iObjectValue);
				}
				if($iCount !== 0){
					$sDashletValue = round($iTotalValue/$iCount, 2);
				}
				if($sCompareUnit === 'delta' && $iCount !== 0 && $iCompareCount !== 0){
					$sDashletDelta =  round(($iTotalValue/$iCount - $iCompareTotalValue/$iCompareCount), 2);
				}
				elseif ($sCompareUnit === 'percentage' && $iCount !== 0 && $iCompareCount !== 0)
				{
					$sDashletDelta = round(((($iTotalValue/$iCount) * 100) / ($iCompareTotalValue/$iCompareCount)), 2) - 100;
				}
				break;
			case 'sum':
				$iTotalValue = null;
				while($oObject = $oSet->Fetch())
				{
					$iObjectValue = $oObject->Get($sAttr);
					$iObjectValue = is_numeric($iObjectValue) ? $iObjectValue : 0;

					$iTotalValue = ($iTotalValue === null ? $iObjectValue : $iTotalValue + $iObjectValue);
				}
				$iCompareTotalValue = 0;
				while($oCompareObject = $oCompareSet->Fetch())
				{
					$iObjectValue = $oCompareObject->Get($sAttr);
					$iObjectValue = is_numeric($iObjectValue) ? $iObjectValue : 0;

					$iCompareTotalValue = ($iCompareTotalValue === null ? $iObjectValue : $iCompareTotalValue + $iObjectValue);
				}

				$sDashletValue = $iTotalValue;
				if($sCompareUnit === 'delta'){
					$sDashletDelta =  $iTotalValue - $iCompareTotalValue;
				}
				elseif ($sCompareUnit === 'percentage' && $iCompareTotalValue !== 0)
				{
					$sDashletDelta = round((($iTotalValue * 100) / $iCompareTotalValue), 2) - 100;
				}
				break;
			case 'percentage':
				$oPercentageFilter = DBObjectSearch::FromOQL($sPercentageQuery, $aQueryParams);
				$oPercentageFilter->SetShowObsoleteData(utils::ShowObsoleteData());
				$oPercentageSet = new DBObjectSet($oPercentageFilter);

				$oComparePercentageFilter = DBObjectSearch::FromOQL($sPercentageCompareQuery, $aQueryParams);
				$oComparePercentageFilter->SetShowObsoleteData(utils::ShowObsoleteData());
				$oComparePercentageSet = new DBObjectSet($oComparePercentageFilter);



				if($oPercentageSet->Count()){
					$sDashletValue = round((($oSet->Count() * 100) / $oPercentageSet->Count()), 2);
				}
				if($oPercentageSet->Count() && $oComparePercentageSet->Count()){
					$sDashletDelta = round((($oSet->Count() * 100) / $oPercentageSet->Count()) - (($oCompareSet->Count() * 100) / $oComparePercentageSet->Count()), 2);
				}
				$sCompareUnit = 'percentage';
				break;
		}
		
		return [$sDashletValue, $sDashletDelta];
	}
}