//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class hetrixapi_hook_announcement extends _HOOK_CLASS_
{

/* !Hook Data - DO NOT REMOVE */
public static function hookData() {
 return array_merge_recursive( array (
  'globalTemplate' => 
  array (
    0 => 
    array (
      'selector' => '#ipsLayout_mainArea',
      'type' => 'add_inside_start',
      'content' => '{template="globalMessage" group="hooks" location="front" app="hetrixapi"}',
    ),
  ),
), parent::hookData() );
}
/* End Hook Data */


}
