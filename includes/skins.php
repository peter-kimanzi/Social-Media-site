<?php
class skin {
	var $filename;

	public function __construct($filename) {
		$this->filename = $filename;
	}

	public function mk($filename) {
		$this->filename = $filename;
		return $this->make();
	}
	
	public function make() {
		global $CONF;
		$file = sprintf('./'.$CONF['theme_path'].'/'.$CONF['theme_name'].'/html/%s.html', $this->filename);
		$fh_skin = fopen($file, 'r');
		$skin = @fread($fh_skin, filesize($file));
		fclose($fh_skin);
		
		return $this->parse($skin);
	}
	
	public static function parse($skin) {
        $skin = preg_replace_callback('/{\$lng->(.+?)}/i', function($matches) {
            global $LNG;
            return $LNG[$matches[1]];
        }, $skin);

        $skin = preg_replace_callback('/{\$([a-zA-Z0-9_]+)}/', function($matches) {
            global $TMPL;
            return (isset($TMPL[$matches[1]])?$TMPL[$matches[1]]:"");
        }, $skin);

        return $skin;
	}
}
?>