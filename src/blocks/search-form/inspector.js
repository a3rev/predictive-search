/*
 * Inspector Settings
 */
import InspectorSearchBoxContainer from './components/inspector/search-box-container';
import InspectorNumberItems from './components/inspector/number-items';

const { Component } = wp.element;
const { InspectorControls } = wp.blockEditor;

/**
 * Inspector controls
 */
export default class Inspector extends Component {
	render() {
		return (
			<InspectorControls>
				<InspectorSearchBoxContainer { ...this.props } />
				<InspectorNumberItems { ...this.props } />
			</InspectorControls>
		);
	}
}
