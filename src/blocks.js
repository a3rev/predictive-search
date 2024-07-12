/**
 * Gutenberg Blocks
 *
 * All blocks related JavaScript files should be imported here.
 * You can create a new block folder in this dir and include code
 * for that block here as well.
 *
 * All blocks should be included here since this is the file that
 * Webpack is compiling as the input file.
 */

import './blocks/search-form/block';
import './blocks/search-bar/block';
import './blocks/search-bar/child-blocks/category-dropdown/block';
import './blocks/search-bar/child-blocks/mobile-icon/block';
import './blocks/search-bar/child-blocks/search-icon/block';
import './blocks/search-bar/child-blocks/search-input/block';
import './blocks/results-dropdown/block';
import './blocks/results-dropdown/child-blocks/dropdown-close-icon/block';
import './blocks/results-dropdown/child-blocks/dropdown-footer/block';
import './blocks/results-dropdown/child-blocks/dropdown-items/block';
import './blocks/results-dropdown/child-blocks/dropdown-title/block';

// For FSE
import './blocks/query-results/block';
import './blocks/results-heading/block';
import './blocks/results-filter-by/block';
import './blocks/item-title/block';
import './blocks/item-excerpt/block';
import './blocks/read-more/block';
import './blocks/item-featured-image/block';
import './blocks/item-terms/block';
import './blocks/item-template/block';