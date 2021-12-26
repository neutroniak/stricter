<?php

class ImageHandler
{
	public $maxWidth;
	public $maxHeight;
	public $input;
	public $output;

	public function __construct() {

	}

	function createThumb()
	{
		if(!file_exists($this->input))
			return false;

		$size = getimagesize($this->input);

		$prop = $size[0]/$size[1];

		//resize values
		if($size[0]>$this->maxWidth || $size[1]>$this->maxHeight) {
			if($prop > 1) {
				$new_w = $this->maxWidth;
				$new_h = $new_w/$prop;
			}
			else if($prop < 1) {
				$new_h = $this->maxHeight;
				$new_w = $new_h*$prop;
			}
			else {
				$new_w = $this->maxWidth;
				$new_h = $new_w/$prop;
			}
		}

		if($size[0] < $this->maxWidth && $size[1] < $this->maxHeight) {
			$new_w = $size[0];
			$new_h = $size[1];
		}

		if(!$new_h) $new_h = $size[1];
		if(!$new_w) $new_w = $size[0];
		
		$background = imagecreatetruecolor($new_w, $new_h);
		$foreground = imagecreatefromjpeg($this->input);
		imagecopyresampled($background, $foreground, 0, 0, 0, 0, $new_w, $new_h, $size[0], $size[1]);
		imagejpeg($background, $this->output, 92);

		return $this->input;
	}
}

?>
