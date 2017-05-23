<?php
/**
 * @brief		Background Task: Rebuild database reciprocal maps
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
 * @subpackage	Content
 * @since		31 May 2016
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\cms\extensions\core\Queue;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Background Task: Rebuild database reciprocal maps
 */
class _RebuildReciprocalMaps
{
	/**
	 * @brief Number of content items to rebuild per cycle
	 */
	public $rebuild	= 500;
	
	/**
	 * Parse data before queuing
	 *
	 * @param	array	$data
	 * @return	array
	 */
	public function preQueueData( $data )
	{
		$databaseId = $data['database'];
		$fieldId    = $data['field'];

		try
		{
			$data['count'] = (int) \IPS\Db::i()->select( 'COUNT(*)', 'cms_custom_database_' . $databaseId, array( 'field_' . $fieldId . ' != \'\' or field_' . $fieldId . ' IS NOT NULL' ) )->first();
		}
		catch( \Exception $ex )
		{
			throw new \OutOfRangeException;
		}
		
		if( $data['count'] == 0 )
		{
			return null;
		}
		
		return $data;
	}
	/**
	 * Run Background Task
	 *
	 * @param	mixed					$data	Data as it was passed to \IPS\Task::queue()
	 * @param	int						$offset	Offset
	 * @return	int|null				New offset or NULL if complete
	 * @throws	\OutOfRangeException	Indicates offset doesn't exist and thus task is complete
	 */
	public function run( $data, $offset )
	{
		$databaseId = $data['database'];
		$fieldId	= $data['field'];
		$parsed = 0;
		
		if ( \IPS\Db::i()->checkForTable( 'cms_custom_database_' . $databaseId ) )
		{
			$fieldsClass = 'IPS\cms\Fields' . $databaseId;
			$field = $fieldsClass::load( $fieldId );
		
			foreach ( \IPS\Db::i()->select( '*', 'cms_custom_database_' . $databaseId, array( 'field_' . $fieldId . ' != \'\' or field_' . $fieldId . ' IS NOT NULL' ), 'primary_id_field asc', array( $offset, $this->rebuild ) ) as $row )
			{
				$extra = $field->extra;
				if ( $row[ 'field_' . $fieldId ] and ! empty( $extra['database'] ) )
				{
					foreach( explode( ',', $row[ 'field_' . $fieldId ] ) as $foreignId )
					{
						if ( $foreignId )
						{
							\IPS\Db::i()->insert( 'cms_database_fields_reciprocal_map', array(
								'map_origin_database_id'	=> $databaseId,
								'map_foreign_database_id'   => $extra['database'],
								'map_origin_item_id'		=> $row['primary_id_field'],
								'map_foreign_item_id'		=> $foreignId,
								'map_field_id'				=> $fieldId
							) );
						}
					}
				}
				
				$parsed++;
			}
		}
		
		if ( ! $parsed )
		{
			throw new \IPS\Task\Queue\OutOfRangeException;
		}
		
		return $offset + $this->rebuild;
	}
	
	/**
	 * Get Progress
	 *
	 * @param	mixed					$data	Data as it was passed to \IPS\Task::queue()
	 * @param	int						$offset	Offset
	 * @return	array( 'text' => 'Doing something...', 'complete' => 50 )	Text explaining task and percentage complete
	 * @throws	\OutOfRangeException	Indicates offset doesn't exist and thus task is complete
	 */
	public function getProgress( $data, $offset )
	{
		$databaseId = $data['database'];
		return array( 'text' => \IPS\Member::loggedIn()->language()->addToStack('rebuilding_cms_database_reciprocal_map', FALSE, array( 'sprintf' => array( \IPS\cms\Databases::load( $databaseId )->_title, $data['field'] ) ) ), 'complete' => $data['count'] ? ( round( 100 / $data['count'] * $offset, 2 ) ) : 100 );
	}	
}