<?php
/**
 * class for display author info div
 *
 * @file
 * @ingroup Extensions
 *
 * @author Pierre Boutet
 */

class Carousel {

	private static $carouselCounter = 0;

	private static function removeNoImages(&$imgs) {
		$first=true;

		$imgsToRemove = array(
			"[[File:No-image-yet.jpg]]"
		);

		foreach ($imgs as $key => $value) {
			if(in_array($value, $imgsToRemove) && ! $first) {
				unset($imgs[$key]);
				continue;
			}
			if (preg_match('/\[\[File:\{\{\{(.*)\}\}\}[\|a-zA-Z]*\]\]/', $imgs[$key])){
				unset($imgs[$key]);
			}
			$first=false;
		}
	}


	public static function addParser( $input, $img1='') {

		self::$carouselCounter++;
		$carouselId = self::$carouselCounter;

		$imgs = func_get_args ();
		array_shift($imgs);
		self::removeNoImages($imgs);

		$out = '<div data-interval="false" data-ride="carousel" class="carousel" id="myCarousel'.$carouselId.'">';

		$out .= '<div class="carousel-inner">';
		$active = 'active';
		foreach ($imgs as $img) {
			$out .= '<div class="item '.$active.'">'.$img.'</div>';
			$active = '';
		}
		$out .= '</div>';

		if(count($imgs) > 1) {
			// carousel indicator :
			$out .= '<ol class="carousel-indicators">';
			$count = 0;
			$active = 'class="active"';
			foreach ($imgs as $img) {
				$out .= '<li '.$active.' data-slide-to="'.$count.'" data-target="#myCarousel'.$carouselId.'">'.$img.'</li>';
				$active = '';
				$count++;
			}
			$out .= '</ol>';
		}

		$out .= '</div>';

		return array( $out );
	}

}