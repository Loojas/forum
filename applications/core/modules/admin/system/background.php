<?php
/**
 * @brief		Background processes 'Run Now'
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
 * @since		28 Jan 2015
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\core\modules\admin\system;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Background processes 'Run Now'
 */
class _background extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		if ( \IPS\CIC )
		{
			\IPS\Output::i()->error( 'no_writes', '2C347/1', 403, '' );
		}
		
		parent::execute();
	}

	/**
	 * Manage
	 *
	 * @return	void
	 */
	protected function manage()
	{
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('background_process_run_title');
		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'system' )->backgroundProcessesRunNow();
	}
	
	/**
	 * Process
	 *
	 * @return	void
	 */
	protected function process()
	{
		$self = $this;
		$multiRedirect = new \IPS\Helpers\MultipleRedirect(
			\IPS\Http\Url::internal('app=core&module=system&controller=background&do=process'),
			function( $data ) use ( $self )
			{
				/* Make sure the task is locked */
				$task = \IPS\Task::load('queue', 'key');
				$task->running = TRUE;
				$task->next_run = time() + 900;
				$task->save();
				
				if ( ! is_array( $data ) )
				{
					$count = $self->getCount();
					 
					return array( array( 'count' => $count, 'done' => 0 ), 'Starting...' );
				}
				else
				{
					try
					{
						/* Run the next queue task, if any */
						$queueData = \IPS\Task::runQueue();

						/* Update count */
						$data['count'] = $self->getCount();
					}
					catch ( \UnderflowException $e )
					{
						/* If we're here it means there were no rows in core_queue and we are done */
						return NULL;
					}
					
					$data['done']	= $data['done'] + ( $queueData['offset'] - $queueData['_originalOffset'] );
					$json			= json_decode( $queueData['data'], TRUE );

					$lang = array( $queueData['key'] );
					
					if ( isset( $json['class'] ) )
					{
						$lang[] = $json['class'];
					}
					else if ( isset( $json['extension'] ) )
					{
						$lang[] = $json['extension'];
					}
					else if ( isset( $json['storageExtension'] ) )
					{
						$lang[] = $json['storageExtension'];
					}
					
					if ( isset( $json['count'] ) )
					{
						/* If the offset is larger than the count, then we should just show the count instead (to avoid situations where it will display 150 / 139, for example) */
						$offset = intval( $queueData['offset'] );
						if ( $offset > $json['count'] )
						{
							$offset = $json['count'];
						}
						$lang[] = " " . $offset . ' / ' . $json['count'];
					}
					
					return array( $data, \IPS\Member::loggedIn()->language()->addToStack('background_processes_processing', FALSE, array( 'sprintf' => array( implode( ' - ', $lang ) ) ) ), ( $data['count'] ) ? round( ( 100 / $data['count'] * $data['done'] ), 2 ) : 100 );
				}
			},
			function()
			{
				/* Make sure the task is unlocked */
				$task = \IPS\Task::load('queue', 'key');
				$task->running = FALSE;
				$task->next_run = time() + 60;
				$task->save();
				
				\IPS\Output::i()->redirect( \IPS\Http\Url::internal('app=core&module=overview&controller=dashboard'), 'completed' );
			}
		);
		
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('background_process_run_title');
		\IPS\Output::i()->output = $multiRedirect;
	}
	
	/**
	 * Get the count of items to process
	 *
	 * @return int
	 */
	public function getCount()
	{
		$count = 0;
		foreach( \IPS\Db::i()->select( '*', 'core_queue' ) as $row )
		{
			if ( ! empty( $row['data'] ) )
			{
				$data = json_decode( $row['data'], TRUE );
				
				if ( isset( $data['count'] ) )
				{
					$count += intval( $data['count'] );
				}
				else
				{
					$count++;
				}
			}
		}
		
		return $count;
	}
}