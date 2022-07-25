<?php
/**
 * Blocks Patterns
 *
 * Built some patterns for customer reuse.
 *
 * @since   1.0.0
 * @package CGB
 */

namespace A3Rev\WPPredictiveSearch\Blocks;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Patterns {

	public function __construct() {
		$this->register_patterns();
		$this->register_pattern_categories();
	}

	public function register_patterns() {
		if ( class_exists( 'WP_Block_Patterns_Registry' ) && ! \WP_Block_Patterns_Registry::get_instance()->is_registered( 'text-two-columns' ) ) {
			register_block_pattern( 'wp-predictive-search/pattern-1', $this->load_block_pattern( 'pattern-1' ) );
			register_block_pattern( 'wp-predictive-search/pattern-2', $this->load_block_pattern( 'pattern-2' ) );
		}
	}

	public function register_pattern_categories() {
		if ( class_exists( 'WP_Block_Pattern_Categories_Registry' ) ) {
			register_block_pattern_category( 'predictive-search', array( 'label' => _x( 'Predictive Search', 'Block pattern category', 'wp-predictive-search' ) ) );
		}
	}

	public function load_block_pattern( $pattern_name) {
		return require( __DIR__ . '/patterns/' . $pattern_name . '.php' );
	}
}
