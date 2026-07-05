<?php
namespace SmartPostAggregantor\Interfaces;

defined( 'ABSPATH' ) || exit;

/**
 * Entity interface for defining common methods for entity operations.
 */
interface Entity {

	/**
	 * Create a new entity in the database.
	 *
	 * @param array $data Data for creating the entity.
	 */
	public function create( array $data );

	/**
	 * Update an existing entity in the database.
	 *
	 * @param int   $id   Entity ID.
	 * @param array $data Data for updating the entity.
	 */
	public function update( $id, array $data );

	/**
	 * Delete the entity from the database.
	 *
	 * @param int $id Entity ID.
	 */
	public function delete( $id );

	/**
	 * Get an entity by its ID.
	 *
	 * @param int $id Entity ID.
	 */
	public function get( $id );

	/**
	 * List entities from the database.
	 *
	 * @param array $filters Optional filters for listing entities.
	 * @param int   $limit   Optional number of entities to return.
	 * @param int   $offset  Optional offset for listing entities.
	 */
	public function list( array $filters = array(), $limit = 100, $offset = 0 );
}
