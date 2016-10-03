<?php
use fay\helpers\Html;
?>
<div class="box">
	<div class="box-title">
		<h4>标题</h4>
	</div>
	<div class="box-content">
		<div class="form-field">
			<?php echo F::form('widget')->inputText('title', array(
				'class'=>'form-control',
			))?>
			<p class="fc-grey">是否用到标题视模版而定，并不一定会显示。</p>
		</div>
	</div>
</div>
<div class="box">
	<div class="box-title">
		<a class="tools toggle" title="点击以切换"></a>
		<h4>属性集</h4>
	</div>
	<div class="box-content">
		<div class="dragsort-list" id="widget-attr-list">
		<?php if(isset($config['data'])){?>
			<?php foreach($config['data'] as $d){?>
			<div class="dragsort-item cf">
				<a class="dragsort-item-selector"></a>
				<div class="dragsort-item-container"><?php 
					echo Html::inputText('keys[]', $d['key'], array(
						'class'=>'form-control fl',
						'placeholder'=>'名称',
						'wrapper'=>array(
							'tag'=>'span',
							'class'=>'ib wp38 fl',
						),
					));
					echo Html::textarea('values[]', $d['value'], array(
						'class'=>'form-control autosize',
						'placeholder'=>'值',
						'wrapper'=>array(
							'tag'=>'span',
							'class'=>'ib wp62 fr pl20',
						),
					));
					echo Html::link('删除', 'javascript:;', array(
						'class'=>'btn btn-grey mt5 btn-sm fl widget-remove-attr-link',
					));
				?></div>
			</div>
			<?php }?>
		<?php }?>
		</div>
	</div>
</div>
<div class="box">
	<div class="box-title">
		<a class="tools toggle" title="点击以切换"></a>
		<h4>添加属性</h4>
	</div>
	<div class="box-content">
		<div class="cf"><?php 
			echo Html::inputText('', '', array(
				'class'=>'form-control fl',
				'placeholder'=>'名称',
				'id'=>'widget-add-attr-key',
				'wrapper'=>array(
					'tag'=>'span',
					'class'=>'ib wp38 fl',
				),
			));
			echo Html::textarea('', '', array(
				'class'=>'form-control autosize',
				'placeholder'=>'值',
				'id'=>'widget-add-attr-value',
				'wrapper'=>array(
					'tag'=>'span',
					'class'=>'ib wp62 fr pl20',
				),
			));
			echo Html::link('添加', 'javascript:;', array(
				'class'=>'btn mt5 btn-sm fl',
				'id'=>'widget-add-attr-link',
			));
		?></div>
	</div>
</div>
<div class="box">
	<div class="box-title">
		<a class="tools toggle" title="点击以切换"></a>
		<h4>渲染模板</h4>
	</div>
	<div class="box-content">
		<?php echo Html::textarea('template', isset($config['template']) ? $config['template'] : '', array(
			'class'=>'form-control h90 autosize',
			'id'=>'code-editor',
		))?>
		<p class="fc-grey mt5">
			若模版内容符合正则<code>/^[\w_-]+(\/[\w_-]+)+$/</code>，
			即类似<code>frontend/widget/template</code><br />
			则会调用当前application下符合该相对路径的view文件。<br />
			否则视为php代码<code>eval</code>执行。若留空，会调用默认模版。
		</p>
	</div>
</div>
<script>
var widget_options = {
	'addAttr':function(){
		$(document).on('click', '#widget-add-attr-link', function(){
			if($("#widget-add-attr-key").val() == ""){
				common.alert('名称不能为空');
			}else{
				$('#widget-attr-list').append(['<div class="dragsort-item cf">',
					'<a class="dragsort-item-selector" style="cursor: pointer;"></a>',
					'<div class="dragsort-item-container">',
						'<span class="ib wp38 fl">',
							'<input name="keys[]" type="text" class="form-control fl" placeholder="名称" value="', $("#widget-add-attr-key").val(), '">',
						'</span>',
						'<span class="ib wp62 fr pl20">',
							'<textarea name="values[]" class="form-control autosize" placeholder="值">',
								$("#widget-add-attr-value").val(),
							'</textarea>',
						'</span>',
						'<a class="btn btn-grey mt5 btn-sm fl widget-remove-attr-link" href="javascript:;" title="删除">删除</a></div>',
				'</div>'].join(''));
				$("#widget-add-attr-key, #widget-add-attr-value").val('');
			}
		});
	},
	'removeAttr':function(){
		$(document).on('click', '.widget-remove-attr-link', function(){
			if(confirm("您确定要删除此属性吗？")){
				$(this).parent().parent().remove();
			}
		});
	},
	'init':function(){
		this.addAttr();
		this.removeAttr();
	}
};
$(function(){
	widget_options.init();
});
</script>