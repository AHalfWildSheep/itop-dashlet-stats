<?php
/**
 * @copyright   Copyright (C) 2010-2023 Combodo SARL
 * @license     http://opensource.org/licenses/AGPL-3.0
 */


namespace AHalfWildSheep\iTop\Extension\DashletStats\Controller;


use Combodo\iTop\Application\UI\Base\Component\Dashlet\DashletContainer;
use Combodo\iTop\Application\UI\Base\tJSRefreshCallback;
use MetaModel;
use utils;

class DashletStatsView extends DashletContainer
{
	use tJSRefreshCallback;

	public const BLOCK_CODE = 'ahws-dashlet-stats';
	public const DEFAULT_HTML_TEMPLATE_REL_PATH = 'ahws-dashlet-stats/view/DashletStatsView';
	
	/** @var $sValue string*/
	private $sValue;
	/** @var $sTitle string*/
	private $sTitle;
	/** @var $sClass string*/
	private $sClass;
	/** @var $sFilter string*/
	private $sFilter;
	/** @var $sValueUnit string*/
	private $sValueUnit;
	/** @var $sValueUnitPosition string*/
	private $sValueUnitPosition;
	/** @var $sIconSrc string*/
	private $sIconSrc;
	/** @var $sLinkUrl string*/
	private $sLinkUrl;

	/**
	 * DashletStatsView constructor.
	 *
	 * @param string $sId
	 * @param string $sTitle
	 * @param string $sValue
	 * @param string $sClass
	 * @param string $sFilter
	 * @param string $sValueUnit
	 * @param string $sValueUnitPosition
	 *
	 * @throws \CoreException
	 */
	public function __construct($sId, $sTitle, $sValue, $sClass, $sFilter, $sValueUnit, $sValueUnitPosition)
	{
		parent::__construct();
		
		$this->sValue = $sValue;
		$this->sTitle = $sTitle;
		$this->sClass = $sClass;
		$this->sFilter = $sFilter;
		$this->sValueUnit = $sValueUnit;
		$this->sValueUnitPosition = $sValueUnitPosition;
		$this->sIconSrc = MetaModel::GetClassIcon($this->sClass, false);
		$this->sLinkUrl = utils::GetAbsoluteUrlAppRoot()."pages/UI.php?operation=search&filter=".$sFilter = rawurlencode($this->sFilter->serialize());	
	}

	/**
	 * @return string
	 */
	public function GetValue()
	{
		return $this->sValue;
	}

	/**
	 * @param string $sValue
	 */
	public function SetValue($sValue)
	{
		$this->sValue = $sValue;
		return $this;
	}

	/**
	 * @return string
	 */
	public function GetTitle()
	{
		return $this->sTitle;
	}

	/**
	 * @param string $sTitle
	 */
	public function SetTitle($sTitle)
	{
		$this->sTitle = $sTitle;
		return $this;
	}

	/**
	 * @return string
	 */
	public function GetClass()
	{
		return $this->sClass;
	}

	/**
	 * @param string $sClass
	 */
	public function SetClass($sClass)
	{
		$this->sClass = $sClass;
		return $this;
	}

	/**
	 * @return string
	 */
	public function GetFilter()
	{
		return $this->sFilter;
	}

	/**
	 * @param string $sFilter
	 */
	public function SetFilter($sFilter)
	{
		$this->sFilter = $sFilter;
		return $this;
	}

	/**
	 * @return string
	 */
	public function GetValueUnit()
	{
		return $this->sValueUnit;
	}

	/**
	 * @param string $sValueUnit
	 */
	public function SetValueUnit($sValueUnit)
	{
		$this->sValueUnit = $sValueUnit;
		return $this;
	}

	/**
	 * @return string
	 */
	public function GetValueUnitPosition()
	{
		return $this->sValueUnitPosition;
	}

	/**
	 * @param string $sValueUnitPosition
	 */
	public function SetValueUnitPosition($sValueUnitPosition)
	{
		$this->sValueUnitPosition = $sValueUnitPosition;
		return $this;
	}

	/**
	 * @return string
	 */
	public function GetIconSrc()
	{
		return $this->sIconSrc;
	}

	/**
	 * @param string $sIconSrc
	 */
	public function SetIconSrc($sIconSrc)
	{
		$this->sIconSrc = $sIconSrc;
		return $this;
	}

	/**
	 * @return string
	 */
	public function GetLinkUrl()
	{
		return $this->sLinkUrl;
	}

	/**
	 * @param string $sLinkUrl
	 */
	public function SetLinkUrl($sLinkUrl)
	{
		$this->sLinkUrl = $sLinkUrl;
		
		return $this;
	}
}