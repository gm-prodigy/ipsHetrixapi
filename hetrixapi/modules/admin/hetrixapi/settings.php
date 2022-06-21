<?php


namespace IPS\hetrixapi\modules\admin\hetrixapi;

use IPS\Dispatcher\Controller;
use IPS\Helpers\Form;
use IPS\Member;
use IPS\Settings;
use IPS\Output;
use IPS\Dispatcher;
use IPS\Http\Url;
use IPS\hetrixapi\Request;
/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * settings
 */
class _settings extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'settings_manage' );
		parent::execute();
	}

	/**
	 * ...
	 *
	 * @return	void
	 */
	protected function manage()
	{


        $taskIntervalOptions = [
            'P0Y0M0DT0H1M0S' => '1 Mins',
            'P0Y0M0DT0H2M0S' => '2 Mins',
            'P0Y0M0DT0H3M0S' => '3 Mins',
            'P0Y0M0DT0H4M0S' => '4 Mins',
            'P0Y0M0DT0H5M0S' => '5 Mins',
            'P1Y0M0DT0H0M0S' => '1 Year',
        ];



        $setStatus = [
            'Offline' => 'Offline',
            'Online' => 'Online'
        ];

        Output::i()->sidebar['actions']['reconfigure'] = [
            'primary' => false,
            'icon' => 'cogs',
            'title' => 'hetrixapi_configuration_configure',
            'link' => \IPS\Http\Url::internal("app=hetrixapi&module=hetrixapi&controller=settings&do=configuration")->csrf(),
        ];

        $form = new Form;
        $form->addHeader('hetrixapi_settings');
        $form->add(new Form\Select('hetrixapi_cron', Settings::i()->hetrixapi_cron, false, ['options' => $taskIntervalOptions]));

        $form->add(new Form\YesNo('hetrixapi_high_load_show', Settings::i()->hetrixapi_high_load_show, false, []));
//        $form->add(new Form\Select('hetrixapi_cpu_load_status', Settings::i()->hetrixapi_cpu_load_status, false, ['options' => $taskIntervalOptions]));

        $form->add(new Form\Select('hetrixapi_setStatus', Settings::i()->hetrixapi_setStatus, false, ['options' => $setStatus]));
        $form->add( new \IPS\Helpers\Form\Text( 'hetrixapi_globalMessage_title', Settings::i()->hetrixapi_globalMessage_title, FALSE, [ 'app' => 'hetrixapi', 'key' => 'editor', 'autoSaveKey' => 'hetrixapi_globalMessage_title' ] ) );
        $form->add( new \IPS\Helpers\Form\Editor( 'hetrixapi_setAnnouncement_message', Settings::i()->hetrixapi_setAnnouncement_message, FALSE, [ 'app' => 'hetrixapi', 'key' => 'editor', 'autoSaveKey' => 'hetrixapi_setAnnouncement_message' ] ) );
        $form->add( new \IPS\Helpers\Form\Editor( 'hetrixapi_cpu_load_stats', Settings::i()->hetrixapi_cpu_load_stats, FALSE, [ 'app' => 'hetrixapi', 'key' => 'editor', 'autoSaveKey' => 'hetrixapi_cpu_load_stats' ] ) );

        if ($values = $form->values(TRUE)) {

            if (isset($values['hetrixapi_cron'])) {
                \IPS\Db::i()->update('core_tasks', ['frequency'=>  $values['hetrixapi_cron']], [['`app`=?', 'hetrixapi'], ['`key`=?', 'HetrixApiCron']]);
            }

            $form->saveAsSettings();
            Output::i()->redirect(Url::internal('app=hetrixapi&module=hetrixapi&controller=settings')->csrf(), 'saved');
        }

        Output::i()->title = Member::loggedIn()->language()->addToStack('menu_hetrixapi_settings_title');
        \IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'apiContentTable.css', 'hetrixapi', 'front' ) );
        Output::i()->output = $form;
	}

    protected function configuration()
    {
        \IPS\Session::i()->csrfCheck();

        $form = new \IPS\Helpers\Form('form');
        $form->addHeader('hetrixapi_settings');
        $form->add(new Form\Text('hetrixapi_key', Settings::i()->hetrixapi_key, false, array(), null,
            null, null, 'hetrixapi_key'));

        if ($values = $form->values(TRUE)) {
            $form->saveAsSettings();
            Output::i()->redirect(Url::internal('app=hetrixapi&module=hetrixapi&controller=settings')->csrf(), 'saved');
        }

        Output::i()->title = Member::loggedIn()->language()->addToStack('menu_hetrixapi_settings_title');
        Output::i()->output = $form;
    }
	
	// Create new methods with the same name as the 'do' parameter which should execute it
}