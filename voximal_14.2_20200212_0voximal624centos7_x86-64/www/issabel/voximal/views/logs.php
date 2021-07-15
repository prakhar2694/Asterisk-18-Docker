<?php
$html = '';
$html = '<div id="logfiles_header">';
$html .= heading(_('Voximal Log Files'), 2);

$html .= form_dropdown('logfile', $files, $logfile);

$lines = array(
			'name'			=> 'lines',
			'id'			=> 'lines',
			'value'			=> 500,
			'placeholder'	=> _('lines')
);
$html .= form_input($lines);

$show = array(
		'name'		=> 'show',
		'content'	=> _('Show'),
		'id'		=> 'show',
);
$html .= form_button($show);
$html .= '</div>';
$html .= '<div id="log_view" class="pre"></div>';
$html .= '<script type="text/javascript" src="/admin/modules/voximal/assets/js/views/logs.js"></script>';
echo $html;
?>
