//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class hetrixapi_hook_content extends _HOOK_CLASS_
{



    public function showStatus()
    {
        \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('hooks', 'hetrixapi', 'front')->content();
    }

    public function getStats()
    {
        \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('hooks', 'hetrixapi', 'front')->content2();
    }


}
