<?php
#
# check_environment.php
#

$_WARNRULE = '#FFFF00';
$_CRITRULE = '#FF0000';
$_COLOR = array("#FF6C00", "#6CFF00", "#006CFF", "#FF6CFF", "#6CFFFF", "#AAAA00", "#AA006C", "#00FF6C", "#6C00FF");

// find maximum label length and if it has warning or critical thresholds for each graph
foreach ($this->DS as $KEY=>$VAL) {
  $GKEY = $VAL['UNIT'];
  $max_label_length[$GKEY] = 0;
  $has_warn[$GKEY] = false;
  $has_crit[$GKEY] = false;
  $color_index[$GKEY] = 0;
}
foreach ($this->DS as $KEY=>$VAL) {
  $GKEY = $VAL['UNIT'];

  if (strlen($VAL['LABEL']) > $max_label_length[$GKEY])  {
    $max_label_length[$GKEY] = strlen($VAL['LABEL']);
  }
  if ($VAL['WARN'] != "" && is_numeric($VAL['WARN']) ){
    $has_warn[$GKEY] = true;
  }
  if ($VAL['CRIT'] != "" && is_numeric($VAL['CRIT']) ){
    $has_crit[$GKEY] = true;
  }
}

foreach ($this->DS as $KEY=>$VAL) {

	$maximum  = "";
	$minimum  = "";
	$critical = "";
	$crit_min = "";
	$crit_max = "";
	$warning  = "";
	$warn_max = "";
	$warn_min = "";
	$vlabel   = "";
	$lower    = "";
	$upper    = "";
	
	if ($VAL['UNIT'] == "C" && preg_match("/^en-US/", $_SERVER['HTTP_ACCEPT_LANGUAGE'] )) {
		if ($VAL['WARN'] != "" && is_numeric($VAL['WARN']) ){
			$warning = round ($VAL['WARN'] * 9 / 5 + 32);
		}
		if ( $VAL['CRIT'] != "" && is_numeric($VAL['CRIT']) ) {
			$critical = round ($VAL['CRIT'] * 9 / 5 + 32);
		}
	} else {
		if ($VAL['WARN'] != "" && is_numeric($VAL['WARN']) ){
			$warning = $VAL['WARN'];
		}
		if ( $VAL['CRIT'] != "" && is_numeric($VAL['CRIT']) ) {
			$critical = $VAL['CRIT'];
		}
	}
	if ( $VAL['MIN'] != "" && is_numeric($VAL['MIN']) ) {
		$lower = " --lower=" . $VAL['MIN'];
		$minimum = $VAL['MIN'];
	}
	if ( $VAL['MAX'] != "" && is_numeric($VAL['MAX']) ) {
		$maximum = $VAL['MAX'];
	}

	$vlabel = $VAL['UNIT'];
	$title = "ENVIRONMENT";
	if ($VAL['UNIT'] == "%%") {
		$vlabel = "%";
		$upper = " --upper=101 ";
		$lower = " --lower=0 ";
	}
	elseif ($VAL['UNIT'] == "C") {
		if (preg_match("/^en-US/", $_SERVER['HTTP_ACCEPT_LANGUAGE'] )) {
			$vlabel = "°F";
		} else {
			$vlabel = "°C";
		}
		$title = "TEMPERATURE";
	}
	elseif ($VAL['UNIT'] == "mV") {
		$title = "VOLTAGE";
	}
	elseif ($VAL['UNIT'] == "A") {
		$title = "CURRENT";
	}
	elseif ($VAL['UNIT'] == "W") {
		$title = "POWER";
	}
	elseif ($VAL['UNIT'] == "rpm") {
		$title = "FAN";
	}

	// combine all data sources with the same unit of measure into one graph with index $GKEY
        $GKEY = $VAL['UNIT'];

	// shorten variable labels if needed
	$var_label = $VAL['LABEL'];
	if ($has_warn[$GKEY] && $has_crit[$GKEY]) {
	  $var_label_cutoff = 23;
        } elseif ($has_warn[$GKEY] || $has_crit[$GKEY]) {
	  $var_label_cutoff = 36;
        } else {
	  $var_label_cutoff = 49;
	}
	if (strlen($var_label) > $var_label_cutoff) {
          $var_label = str_replace(" Temp Sensor.", "", $var_label);
          $var_label = substr($var_label, 0, $var_label_cutoff);
        }
	// make all labels same length if multiline
	if (sizeof($this->DS) > 1) { 
          if ($max_label_length[$GKEY] > $var_label_cutoff) {
            $var_label = str_pad($var_label,$var_label_cutoff," ");
	  } else {
            $var_label = str_pad($var_label,$max_label_length[$GKEY]," ");
	  }
        }
	// Escape colon for RRDTOOL
	$var_label = str_replace(":", "\:", $var_label);

	// start new graph if this is the first graph for a new unit of measure
        if (!isset($opt[$GKEY])) { 
	  $opt[$GKEY]  = '--slope-mode --vertical-label "' . $vlabel . '" --title "';
          $opt[$GKEY] .= $this->MACRO['DISP_HOSTNAME'] . ' / ' . $title . '"';
          $opt[$GKEY] .= $upper . $lower;
        }
        if (!isset($def[$GKEY])) { $def[$GKEY] = ""; }
	if (!isset($ds_name[$GKEY])) { $ds_name[$GKEY] = $title; }

	// crate the graph
	if ( $vlabel == "°F" ) {
	  $def[$GKEY] .= rrd::def ("celvar$KEY", $VAL['RRDFILE'], $VAL['DS'], "AVERAGE");
          $def[$GKEY] .= rrd::cdef ("var$KEY", "9,5,/,celvar$KEY,*,32,+");
        } else {
	  $def[$GKEY] .= rrd::def ("var$KEY", $VAL['RRDFILE'], $VAL['DS'], "AVERAGE");
        }
	$def[$GKEY] .= rrd::line3 ("var$KEY", $_COLOR[$color_index[$GKEY]], $var_label );
	$def[$GKEY] .= "GPRINT:var$KEY:LAST:\"%.0lf$vlabel now\" ";
	$def[$GKEY] .= "GPRINT:var$KEY:MAX:\"%.0lf$vlabel max\" ";
	$def[$GKEY] .= "GPRINT:var$KEY:AVERAGE:\"%.0lf$vlabel avg\" ";
	if ($warning != "") {
		$def[$GKEY] .= rrd::hrule($warning, $_WARNRULE, "Warn $warning$vlabel");
	}
	if ($critical != "") {
		$def[$GKEY] .= rrd::hrule($critical, $_CRITRULE, "Crit $critical$vlabel");
	}

        $def[$GKEY] .= "COMMENT:\\j ";

	// rotate colors
        $color_index[$GKEY]++;
        $color_index[$GKEY] = $color_index[$GKEY] % sizeof($_COLOR);
}
?>
