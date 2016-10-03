<?php
use fay\helpers\Html;
use fay\services\File;

$element_id = $config['element_id'] ? $config['element_id'] : $alias;
?>
<div id="<?php echo $element_id?>">
	<div class="nivo-slider">
	<?php foreach($files as $f){
		if(empty($f['link'])){
			$f['link'] = 'javascript:;';
		}
		echo Html::link(Html::img($f['file_id'], ($config['width'] || $config['height']) ? File::PIC_RESIZE : File::PIC_ORIGINAL, array(
			'alt'=>Html::encode($f['title']),
			'title'=>Html::encode($f['title']),
			'dw'=>empty($config['width']) ? false : $config['width'],
			'dh'=>empty($config['height']) ?  false : $config['height'],
		)), $f['link'], array(
			'encode'=>false,
			'title'=>Html::encode($f['title']),
		));
	}?>
	</div>
</div>
<link type="text/css" rel="stylesheet" href="<?php echo $this->assets('css/nivo-slider.css')?>" >
<script src="<?php echo $this->assets('js/jquery.nivo.slider.pack.js')?>"></script>
<script>
$(function(){
	$("#<?php echo $element_id?> .nivo-slider").nivoSlider({
		'animSpeed':<?php echo $config['animSpeed']?>,
		'pauseTime':<?php echo $config['pauseTime']?>,
		'directionNav':<?php echo $config['directionNav'] ? 'true' : 'false'?>,
		'effect':'<?php echo $config['effect']?>'
	});
});
</script>