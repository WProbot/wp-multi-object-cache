<?php

namespace WordPress\Cache;

use Psr\Cache\CacheItemPoolInterface;
use ReflectionClass;

class PoolManager {
	/** @var  array */
	protected $pools = array();

	/** @var PoolGroupConnector Pool Group Connector */
	protected $pool_group_connector;

	public function __construct( PoolGroupConnector $pool_group_connector ) {
		$this->pool_group_connector = $pool_group_connector;
	}

	/**
	 * Reads configuration
	 * @throws \Exception
	 */
	public function initialize() {
		require_once OBJECT_CACHE_PATH . '/object-cache.config.php';

		/** @var array $config */
		$this->register_pools( $config['pools'] );
	}

	/**
	 * Gets the controller for a group.
	 *
	 * @param string $group Group to get controller for.
	 *
	 * @return PSRCacheAdapter
	 */
	public function get( $group = '' ) {
		$pool = $this->pool_group_connector->get_pool( $group );

		// Create a new Key Pool with initial group name.
		return new PSRCacheAdapter( $pool, $group );
	}

	/**
	 * Gets all registered pools.
	 *
	 * @return CacheItemPoolInterface[]
	 */
	public function get_pools() {
		return $this->pools;
	}

	/**
	 * Registers the pools for the groups they specified.
	 *
	 * @param array $pools List of pools to load.
	 *
	 * @throws \Exception
	 */
	protected function register_pools( $pools ) {
		// Register pools.
		foreach ( $pools as $pool => $data ) {
			$this->register_pool( $pool, $data );
		}
	}

	/**
	 * Registers a pool.
	 *
	 * @param string $pool_class Class name of the Pool to register.
	 * @param array $data Configuration to use on the pool.
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function register_pool( $pool_class, $data ) {
		if ( ! class_exists( $pool_class ) ) {
			throw new \InvalidArgumentException( sprintf( 'Class %s not found while loading Object Cache pools.',
				$pool_class ) );
		}

		if ( ! is_array( $data['groups'] ) || 0 === count( $data['groups'] ) ) {
			throw new \InvalidArgumentException( sprintf( 'The pool %s must have at least one group definition.',
				$pool_class ) );
		}

		if ( $this->check_prerequisites( $data['prerequisites'] ) ) {
			$args                       = ( isset( $data['config'] ) ? $data['config'] : null );
			$this->pools[ $pool_class ] = $this->get_pool_instance( $pool_class, $args );
		} else {
			trigger_error( 'Pool prerequisites not met, using Null implementation.', E_USER_WARNING );
		}

		foreach ( $data['groups'] as $group ) {
			$this->pool_group_connector->add( $this->pools[ $pool_class ], $group );
		}
	}

	/**
	 * Checks for all prerequisites
	 *
	 * @param array $prerequisites Prerequisites to check.
	 *
	 * @return bool
	 */
	protected function check_prerequisites( array $prerequisites = array() ) {
		$met = true;

		foreach ( $prerequisites as $prerequisite ) {
			switch ( $prerequisite ) {
				case 'class':
					$met = class_exists( $prerequisite ) && $met;
					if ( ! $met ) {
						return false;
					}
					break;
				case 'function':
					$met = function_exists( $prerequisite ) && $met;
					if ( ! $met ) {
						return false;
					}
					break;
			}
		}

		return $met;
	}

	/**
	 * Gets the Pool instance.
	 *
	 * @param string $pool Class name of the pool to instance.
	 * @param array $args Optional. Class arguments.
	 *
	 * @return object
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function get_pool_instance( $pool, $args = null ) {
		$reflection_class = new ReflectionClass( $pool );

		$interfaces = $reflection_class->getInterfaceNames();
		if ( ! in_array( CacheItemPoolInterface::class, $interfaces ) ) {
			throw new \InvalidArgumentException( sprintf( 'Every pool needs to be extending the %s interface.',
				CacheItemPoolInterface::class ) );
		}

		if ( null !== $args ) {
			return $reflection_class->newInstanceArgs( $args );
		}

		return $reflection_class->newInstance();
	}
}
