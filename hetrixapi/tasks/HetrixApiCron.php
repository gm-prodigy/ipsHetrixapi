<?php
/**
 * @brief		HetrixApiCron Task
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @subpackage	hetrixapi
 * @since		15 Feb 2021
 */

namespace IPS\hetrixapi\tasks;

use IPS\Application;
use IPS\Task;
use IPS\hetrixapi\Request;
/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * HetrixApiCron Task
 */
class _HetrixApiCron extends \IPS\Task
{
	/**
	 * Execute
	 *
	 * If ran successfully, should return anything worth logging. Only log something
	 * worth mentioning (don't log "task ran successfully"). Return NULL (actual NULL, not '' or 0) to not log (which will be most cases).
	 * If an error occurs which means the task could not finish running, throw an \IPS\Task\Exception - do not log an error as a normal log.
	 * Tasks should execute within the time of a normal HTTP request.
	 *
	 * @return	mixed	Message to log or NULL
	 * @throws	\IPS\Task\Exception
	 */
	public function execute()
	{
        if (!Application::appIsEnabled('hetrixapi')) {
            return null;
        }

        if (\IPS\Settings::i()->hetrixapi_key !== null){
//            \IPS\Task::queue('hetrixapi', 'tasks', [\IPS\hetrixapi\extensions\core\Queue\tasks]);
            try{
                $hetrixapi = new Request();
                $getData = $hetrixapi->uptimeReport();
                $statsData = $hetrixapi->gStats();

                $highLoad = [];
                $Offline = [];

//                dd($getData);

                if ($getData){
                    foreach ($getData as $i){
                        if ($i->Uptime_Status === \IPS\Settings::i()->hetrixapi_setStatus){
                            $object = new \stdClass();
                            $object->Name = $i->Name;
                            $object->ID = $i->ID;
                            $object->Uptime_Status = $i->Uptime_Status;
                            array_push($Offline, $object);

                        }
                    };


                    \IPS\Settings::i()->changeValues( array( 'hetrixapi_globalMessage_content_2' => \count($Offline) > 0 ? json_encode($Offline) : null ) );
                }

                if($statsData){
                    foreach($statsData as $stats){
                        if(round($stats->cpu) > 2)
                        {
                            array_push($highLoad, $stats);
                        }

                    }
                    \IPS\Settings::i()->changeValues( array( 'hetrixapi_cpu_load_stats' => \count($highLoad) > 0 ? json_encode($highLoad) : null ) );
                }


            } catch (\Exception $e){
                throw new Task\Exception($this, $e);
            }

        }

		return null;
	}
	
	/**
	 * Cleanup
	 *
	 * If your task takes longer than 15 minutes to run, this method
	 * will be called before execute(). Use it to clean up anything which
	 * may not have been done
	 *
	 * @return	void
	 */
	public function cleanup()
	{
		
	}
}