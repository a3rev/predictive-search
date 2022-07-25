<?php
/**
 * Generate styles from global components for frontend
 *
 */

namespace A3Rev\WPPredictiveSearch\Blocks;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Styles {

	public function __construct() {
	}

	public function minimizeCSSsimple( $css ) {
		if(trim($css) === "") return $css;
		$css = preg_replace(
			array(
				// Remove comment(s)
				'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)|^\s*|\s*$#s',
				// Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`
				'#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',
				// Replace `:0 0 0 0` with `:0`
				'#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',
				// Replace `background-position:0` with `background-position:0 0`
				'#(background-position):0(?=[;\}])#si',
				// Replace `0.6` with `.6`, but only when preceded by `:`, `,`, `-` or a white-space
				'#(?<=[\s:,\-])0+\.(\d+)#s',
				// Minify string value
				'#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][a-z0-9\-_]*?)\2(?=[\s\{\}\];,])#si',
				'#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',
				// Minify HEX color code
				'#(?<=[\s:,\-]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',
				// Replace `(border|outline):none` with `(border|outline):0`
				'#(?<=[\{;])(border|outline):none(?=[;\}\!])#',
				// Remove empty selector(s)
				'#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#s'
			),
			array(
				'$1',
				'$1',
				':0',
				'$1:0 0',
				'.$1',
				'$1$3',
				'$1$2$4$5',
				'$1$2$3',
				'$1:0',
				'$1$2'
			),
		$css);

		$css = preg_replace('/\/\*((?!\*\/).)*\*\//', '', $css); // negative look ahead
		$css = preg_replace('/\s{2,}/', ' ', $css);
		$css = preg_replace('/\s*([:;{}])\s*/', '$1', $css);
		$css = preg_replace('/;}/', '}', $css);

		return $css;
	}

    public function hex2rgba( $color, $opacity = false ) {

		$default = 'rgb(0,0,0)';
		//Return default if no color provided
		if(empty($color))
	          return $default;

		//Sanitize $color if "#" is provided
        if ($color[0] == '#' ) {
        	$color = substr( $color, 1 );
        }

        //Check if color has 6 or 3 characters and get values
        if (strlen($color) == 6) {
                $hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
        } elseif ( strlen( $color ) == 3 ) {
                $hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
        } else {
                return $default;
        }

        //Convert hexadec to rgb
        $rgb =  array_map('hexdec', $hex);

        //Check if opacity is set(rgba or rgb)
        if($opacity !== false){
        	if(abs($opacity) > 1)
        		$opacity = 1.0;
        	$output = 'rgba('.implode(",",$rgb).','.$opacity.')';
        } else {
        	$output = 'rgb('.implode(",",$rgb).')';
        }
        //Return rgb(a) color string
        return $output;
	}

	/**
	 * Export Styles for Spacing Component
	 */
	public function spacing_styles( $fieldName, $attributes ) {
        $marginLeft = $fieldName . 'marginLeft';
		$marginTop = $fieldName . 'marginTop';
		$marginRight = $fieldName . 'marginRight';
		$marginBottom = $fieldName . 'marginBottom';
		$marginUnit = $fieldName . 'marginUnit';

	    $paddingLeft = $fieldName . 'paddingLeft';
		$paddingTop = $fieldName . 'paddingTop';
		$paddingRight = $fieldName . 'paddingRight';
		$paddingBottom = $fieldName . 'paddingBottom';
		$paddingUnit = $fieldName . 'paddingUnit';

	    $marginUnitValue = isset( $attributes[ $marginUnit ] ) ? $attributes[ $marginUnit ] : 'px';
	    $paddingUnitValue = isset( $attributes[ $paddingUnit ] ) ? $attributes[ $paddingUnit ] : 'px';

        $styleCSS = '';

        $styleCSS .= isset( $attributes[ $marginLeft ] ) ? 'margin-left:'.$attributes[ $marginLeft ].$marginUnitValue.'!important;' : '';

        $styleCSS .= isset( $attributes[ $marginTop ] ) ? 'margin-top:'.$attributes[ $marginTop ].$marginUnitValue.'!important;' : '';

        $styleCSS .= isset( $attributes[ $marginRight ] ) ? 'margin-right:'.$attributes[ $marginRight ].$marginUnitValue.'!important;' : '';

        $styleCSS .= isset( $attributes[ $marginBottom ] ) ? 'margin-bottom:'.$attributes[ $marginBottom ].$marginUnitValue.'!important;' : '';

        $styleCSS .= isset( $attributes[ $paddingLeft ] ) ? 'padding-left:'.$attributes[ $paddingLeft ].$paddingUnitValue.'!important;' : '';

        $styleCSS .= isset( $attributes[ $paddingTop ] ) ? 'padding-top:'.$attributes[ $paddingTop ].$paddingUnitValue.'!important;' : '';

        $styleCSS .= isset( $attributes[ $paddingRight ] ) ? 'padding-right:'.$attributes[ $paddingRight ].$paddingUnitValue.'!important;' : '';

        $styleCSS .= isset( $attributes[ $paddingBottom ] ) ? 'padding-bottom:'.$attributes[ $paddingBottom ] .$paddingUnitValue.'!important;' : '';

        return $styleCSS;
    }

    /**
	 * Export Styles for Border Component
	 */
	public function border_styles( $fieldName, $attributes, $isImportant = false ) {
        $borderStyle = $fieldName . 'borderStyle';
		$borderLeft = $fieldName . 'borderLeft';
		$borderTop = $fieldName . 'borderTop';
		$borderRight = $fieldName . 'borderRight';
		$borderBottom = $fieldName . 'borderBottom';
		$borderColor = $fieldName . 'borderColor';
        $borderOpacity = $fieldName . 'borderOpacity';

        $important = $isImportant ? '!important' : '';

        $borderStyleValue = isset( $attributes[ $borderStyle ] ) ? $attributes[ $borderStyle ] : 'solid';
	    $borderOpacityValue = ! isset( $attributes[ $borderOpacity ] ) ? 1 : $attributes[ $borderOpacity ] / 100;

        $styleCSS = '';

        $styleCSS .= isset( $attributes[ $borderLeft ] ) ? 'border-left-width:'.$attributes[ $borderLeft ].'px'.$important.';' : '';

        $styleCSS .= isset( $attributes[ $borderTop ] ) ? 'border-top-width:'.$attributes[ $borderTop ].'px'.$important.';' : '';

        $styleCSS .= isset( $attributes[ $borderRight ] ) ? 'border-right-width:'.$attributes[ $borderRight ].'px'.$important.';' : '';

        $styleCSS .= isset( $attributes[ $borderBottom ] ) ? 'border-bottom-width:'.$attributes[ $borderBottom ].'px'.$important.';' : '';

        $styleCSS .= 'border-style:'.$borderStyleValue.$important.';';

        $styleCSS .= isset( $attributes[ $borderColor ] ) ? 'border-color:'. $this->hex2rgba( $attributes[ $borderColor ], $borderOpacityValue ).$important.';' : '';

        return $styleCSS;
    }

    /**
	 * Export Styles for Border Radius Component
	 */
	public function border_radius_styles( $fieldName, $attributes, $isImportant = false ) {
        $radiusTopLeft = $fieldName . 'radiusTopLeft';
		$radiusTopRight = $fieldName . 'radiusTopRight';
		$radiusBottomRight = $fieldName . 'radiusBottomRight';
        $radiusBottomLeft = $fieldName . 'radiusBottomLeft';

        $important = $isImportant ? '!important' : '';

        $styleCSS = '';

        $styleCSS .= isset( $attributes[ $radiusTopLeft ] ) ? 'border-top-left-radius:'.$attributes[ $radiusTopLeft ].'px'.$important.';' : '';

        $styleCSS .= isset( $attributes[ $radiusTopRight ] ) ? 'border-top-right-radius:'.$attributes[ $radiusTopRight ].'px'.$important.';' : '';

        $styleCSS .= isset( $attributes[ $radiusBottomRight ] ) ? 'border-bottom-right-radius:'.$attributes[ $radiusBottomRight ].'px'.$important.';' : '';

        $styleCSS .= isset( $attributes[ $radiusBottomLeft ] ) ? 'border-bottom-left-radius:'.$attributes[ $radiusBottomLeft ].'px'.$important.';' : '';

        return $styleCSS;
    }

    /**
	 * Export Styles for Typography Component
	 */
	public function typography_styles( $fieldName = 'typography', $attributes = array(), $isImportant = false ) {
        $color = $fieldName . 'Color';
		$size = $fieldName . 'Size';
		$sizeType = $fieldName . 'SizeType';
		$lineHeight = $fieldName . 'LineHeight';
		$lineHeightType = $fieldName . 'LineHeightType';
		$letterSpacing = $fieldName . 'LetterSpacing';
		$textTransform = $fieldName . 'TextTransform';
		$fontFamily = $fieldName . 'FontFamily';
		$fontWeight = $fieldName . 'FontWeight';
		$fontStyle = $fieldName . 'FontStyle';

	    $sizeTypeValue = isset( $attributes[ $sizeType ] ) ? $attributes[ $sizeType ] : 'px';
		$lineHeightTypeValue = isset( $attributes[ $lineHeightType ] ) ? $attributes[ $lineHeightType ] : 'em';

		$important = $isImportant ? '!important' : '';

        $styleCSS = '';

        $styleCSS .= isset( $attributes[ $color ] ) ? 'color:'.$attributes[ $color ].$important.';' : '';

        $styleCSS .= isset( $attributes[ $size ] ) ? 'font-size:'.$attributes[ $size ].$sizeTypeValue.$important.';' : '';

        $styleCSS .= isset( $attributes[ $fontFamily ] ) ? 'font-family:'.$attributes[ $fontFamily ].$important.';' : '';

        $styleCSS .= isset( $attributes[ $fontStyle ] ) ? 'font-style:'.$attributes[ $fontStyle ].$important.';' : '';

        $styleCSS .= isset( $attributes[ $fontWeight ] ) ? 'font-weight:'.$attributes[ $fontWeight ].$important.';' : '';

        $styleCSS .= isset( $attributes[ $lineHeight ] ) ? 'line-height:'.$attributes[ $lineHeight ].$lineHeightTypeValue.$important.';' : '';

        $styleCSS .= isset( $attributes[ $textTransform ] ) ? 'text-transform:'.$attributes[ $textTransform ].$important.';' : '';

        $styleCSS .= isset( $attributes[ $letterSpacing ] ) ? 'letter-spacing:'.$attributes[ $letterSpacing ].'px'.$important.';' : '';

        return $styleCSS;
    }

    /**
	 * Export Styles for Background Component
	 */
	public function background_styles( $fieldName, $attributes ) {
        $bgImage      = $fieldName . 'bgImage';
		$bgColor            = $fieldName . 'bgColor';
		$bgColorOpacity     = $fieldName . 'bgColorOpacity';
		$bgSize             = $fieldName . 'bgSize';
		$bgPosition         = $fieldName . 'bgPosition';
		$bgRepeat           = $fieldName . 'bgRepeat';
        $bgAttachment = $fieldName . 'bgAttachment';

        $bgColorOpacityValue = ! isset( $attributes[ $bgColorOpacity ] ) ? 1:                        $attributes[ $bgColorOpacity ] / 100;
        $bgSizeValue         = isset( $attributes[ $bgSize ] ) ? $attributes[ $bgSize ]:             'cover';
        $bgPositionValue     = isset( $attributes[ $bgPosition ] ) ? $attributes[ $bgPosition ]:     'center center';
        $bgRepeatValue       = isset( $attributes[ $bgRepeat ] ) ? $attributes[ $bgRepeat ]:         'no-repeat';
        $bgAttachmentValue   = isset( $attributes[ $bgAttachment ] ) ? $attributes[ $bgAttachment ]: 'scroll';

        $styleCSS = '';

        $styleCSS .= isset( $attributes[ $bgColor ] ) ? 'background-color:'. $this->hex2rgba( $attributes[ $bgColor ], $bgColorOpacityValue ). ';' : '';

        if ( ! empty( $attributes[ $bgImage ] ) ) {
            $styleCSS .= 'background-image:url('. $attributes[ $bgImage ]['url'].');';

            $styleCSS .= 'background-size:'. $bgSizeValue. ';';

            $styleCSS .= 'background-position:'. $bgPositionValue. ';';

            $styleCSS .= 'background-repeat:'. $bgRepeatValue. ';';

            $styleCSS .= 'background-attachment:'. $bgAttachmentValue. ';';
        }

        return $styleCSS;
    }

    /**
	 * Export Styles for Gradient Component
	 */
	public function gradient_styles( $fieldName, $attributes ) {
        $gradientType = $fieldName . 'gradientType';
		$gradientStartColor = $fieldName . 'gradientStartColor';
		$gradientStartLocation = $fieldName . 'gradientStartLocation';
		$gradientEndColor = $fieldName . 'gradientEndColor';
		$gradientEndLocation = $fieldName . 'gradientEndLocation';
		$gradientAngle = $fieldName . 'gradientAngle';
        $gradientPosition = $fieldName . 'gradientPosition';

        if ( ! $attributes[ $gradientStartColor ] || ! $attributes[ $gradientEndColor ] ) {
            return '';
        }

        $gradientStartLocationValue = isset( $attributes[ $gradientStartLocation ] ) ? $attributes[ $gradientStartLocation ] : 0;
        $gradientEndLocationValue = isset( $attributes[ $gradientEndLocation ] ) ? $attributes[ $gradientEndLocation ] : 100;
        $gradientAngleValue = isset( $attributes[ $gradientAngle ] ) ? $attributes[ $gradientAngle ] : 180;
        $gradientPositionValue = isset( $attributes[ $gradientPosition ] ) ? $attributes[ $gradientPosition ] : 'center center';

        $styleCSS = 'background-image:';

        $styleCSS .= isset( $attributes[ $gradientType ] ) ? $attributes[ $gradientType ].'-gradient' : 'linear-gradient';

        $styleCSS .= '(';

        $styleCSS .= isset( $attributes[ $gradientType ] ) && 'radial' === $attributes[ $gradientType ] ? 'at ' . $gradientPositionValue : $gradientAngleValue . 'deg';

        $styleCSS .= ',';

        $styleCSS .= $attributes[ $gradientStartColor ] . ' ' . $gradientStartLocationValue . '%';

        $styleCSS .= ',';

        $styleCSS .= $attributes[ $gradientEndColor ] . ' ' . $gradientEndLocationValue . '%';

        $styleCSS .= ');';

        return $styleCSS;
    }

    /**
	 * Export Styles for Shadow Component
	 */
	public function shadow_styles( $fieldName = 'shadow', $attributes = array() ) {
        $active = $fieldName . 'Active';
		$inside = $fieldName . 'Inside';
		$opacity = $fieldName . 'Opacity';
		$color = $fieldName . 'Color';
		$v = $fieldName . 'V';
		$h = $fieldName . 'H';
        $blur = $fieldName . 'Blur';
        $spread = $fieldName . 'Spread';

        if ( ! isset( $attributes[ $active ] ) || ! $attributes[ $active ] ) {
            return '';
        }

        $insideValue = isset( $attributes[ $inside ] ) ? $attributes[ $inside ] : false;
        $opacityValue = isset( $attributes[ $opacity ] ) ? $attributes[ $opacity ] : 1;
        $colorValue = isset( $attributes[ $color ] ) ? $attributes[ $color ] : '#dbdbdb';
        $vValue = isset( $attributes[ $v ] ) ? $attributes[ $v ] : 0;
        $hValue = isset( $attributes[ $h ] ) ? $attributes[ $h ] : 0;
        $blurValue = isset( $attributes[ $blur ] ) ? $attributes[ $blur ] : 0;
        $spreadValue = isset( $attributes[ $spread ] ) ? $attributes[ $spread ] : 0;

        $shadowStyle = $hValue . 'px ' .$vValue . 'px ' . $blurValue . 'px ' . $spreadValue . 'px ' . $this->hex2rgba( $colorValue, $opacityValue ) . ' ' . ( $insideValue ? 'inset' : '' );

        $styleCSS = 'box-shadow:' . $shadowStyle . ';';

        return $styleCSS;
    }

    /**
	 * Export Styles for Drop Shadow Component
	 */
	public function dropshadow_styles( $fieldName = 'dropShadow', $attributes = array() ) {
        $active = $fieldName . 'Active';
		$opacity = $fieldName . 'Opacity';
		$color = $fieldName . 'Color';
		$dx = $fieldName . 'Dx';
		$dy = $fieldName . 'Dy';
        $deviation = $fieldName . 'Deviation';

        if ( ! isset( $attributes[ $active ] ) || ! $attributes[ $active ] ) {
            return '';
        }

        $opacityValue = isset( $attributes[ $opacity ] ) ? $attributes[ $opacity ] : 1;
        $colorValue = isset( $attributes[ $color ] ) ? $attributes[ $color ] : '#cccccc';
        $dxValue = isset( $attributes[ $dx ] ) ? $attributes[ $dx ] : 0;
        $dyValue = isset( $attributes[ $dy ] ) ? $attributes[ $dy ] : 0;
        $deviationValue = isset( $attributes[ $deviation ] ) ? $attributes[ $deviation ] : 0;

        $dropShadowStyle = 'drop-shadow(' . $dxValue . 'px ' . $dyValue . 'px ' . $deviationValue . 'px ' . $this->hex2rgba( $colorValue, $opacityValue ) . ')';

        $styleCSS = 'filter:' . $dropShadowStyle . ';';
        $styleCSS .= '-webkit-filter:' . $dropShadowStyle . ';';

        return $styleCSS;
	}
}
