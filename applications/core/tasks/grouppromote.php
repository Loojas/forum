<?php
/**
 * @brief		Group Promotion Task
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
 * @since		16 Sep 2016
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\core\tasks;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Task to promote users based on group promotion settings
 */
class _grouppromote extends \IPS\Task
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
		/* Retrieve all groups but exclude guest group as these cannot auto promote */
		foreach ( \IPS\Member\Group::groups( TRUE, FALSE ) as $group)
		{
			if ( $group->g_promotion != '-1&-1' )
			{
				list( $gid, $gUnit ) = explode( '&', $group->g_promotion );

				if ( $gid > 0 and $gUnit > 0 )
				{
					/* Is this post, date or reputation based promotion? */
					if ( $group->g_promotion_type == 1 )
					{
						/* Update query for members by date */
						$upgradeThresholdTimestamp = time() - ( $gUnit * 86400 );
						\IPS\Db::i()->update( "core_members", array( 'member_group_id' => $gid ), array( "joined <= ? AND member_group_id = ?", $upgradeThresholdTimestamp, $group->g_id ) );
					}
					elseif ( $group->g_promotion_type == 2 )
					{
						/* Update query for members by reputation */
						\IPS\Db::i()->update( "core_members", array( 'member_group_id' => $gid ), array( "pp_reputation_points >= ? AND member_group_id = ?", $gUnit, $group->g_id ) );
					}
					else
					{
						/* Update query for members by content count */
						\IPS\Db::i()->update( "core_members", array( 'member_group_id' => $gid ), array( "member_posts >= ? AND member_group_id = ?", $gUnit, $group->g_id ) );
					}
				}
			}
		}
		return NULL;
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