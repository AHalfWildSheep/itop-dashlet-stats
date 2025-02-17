<?php
//
// iTop module definition file
//

SetupWebPage::AddModule(
	__FILE__, // Path to the current file, all other file names are relative to the directory containing this file
	'ahws-dashlet-stats/1.1.0-dev',
	array(
		// Identification
		//
		'label' => 'AHalfWildSheep\'s Dashlet stats',
		'category' => 'business',

		// Setup
		//
		'dependencies' => array(
			
		),
		'mandatory' => false,
		'visible' => true,

		// Components
		//
		'datamodel' => array(
			'model.ahws-dashlet-stats.php',
			'src/Application/DashletStats.php',
			'src/Application/DashletStatsCompare.php',
			'vendor/autoload.php',
		),
		'webservice' => array(
			
		),
		'data.struct' => array(
			// add your 'structure' definition XML files here,
		),
		'data.sample' => array(
			// add your sample data XML files here,
		),
		
		// Documentation
		//
		'doc.manual_setup' => '', // hyperlink to manual setup documentation, if any
		'doc.more_information' => '', // hyperlink to more information, if any 

		// Default settings
		//
		'settings' => array(
			// Module specific settings go here, if any
			'allow_string_attribute' => false,
		),
	)
);


?>
