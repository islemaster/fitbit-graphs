<?php
error_reporting(E_ALL);
require 'lib/fitbitphp.php';
require 'config.php';

$fitbit = new FitBitPHP(FITBIT_KEY, FITBIT_SECRET);

function prettyPrintXML($xml) {
    $dom = dom_import_simplexml($xml)->ownerDocument;
    $dom->formatOutput = true;
    echo '<div class="xml">' . 
        'Click border to hide XML.' .
        '<pre>' . 
        str_replace(
            array('<','>'),
            array('&lt;','&gt;'),
            $dom->saveXML()
        ) .
        '</pre></div>';
}

function prettyPrintArray($rray) {
    echo '<div class="xml">' .
        'Click border to hide XML.' .
        '<pre>' .
        print_r($rray, true) .
        '</pre></div>';
}

$fitbit->initSession("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
$profileXml = $fitbit->getProfile();
$sleepXml = $fitbit->getSleep(new DateTime());
$sleepStartTimes = $fitbit->getTimeSeries('startTime', 'today', '30d');
$sleepDurations = $fitbit->getTimeSeries('timeInBed', 'today', '30d');

// Zip the two arrays into one
$mapFunc = function ($startTimeInfo, $durationInfo) {
  return array(
    'dateTime' => $startTimeInfo->dateTime,
    'startTime' => $startTimeInfo->value,
    'timeInBed' => $durationInfo->value
  );
};
$sleepInfo = array_map($mapFunc, $sleepStartTimes, $sleepDurations);
?>
<!DOCTYPE html>
<html>
  <head>
    <title>FitBit Graphs</title>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
    <link rel="stylesheet" type="text/css" href="build/css/master.css" />
    <script src="lib/jquery-2.1.3.min.js"></script>
    <script src="http://d3js.org/d3.v3.min.js" charset="utf-8"></script>
    <script src="build/js/sleepGraph.js" charset="utf-8"></script>
    <script language="javascript" type="text/javascript">
      var sleepInfo = <?php echo json_encode($sleepInfo); ?>;
      $(function () {
        window.fitbitGraphs.generateChart('.sleep_start_times', sleepInfo);
        window.fitbitGraphs.autoHide('.xml');
      });
    </script>
  </head>
  <body>
    <h1>Fitbit Graphs</h1>
    <img class="avatar" src="<?php echo $profileXml->user->avatar; ?>" />
    <h2>What's up, <?php echo $profileXml->user->displayName; ?>?</h2>
    <hr />
    <h3>Profile</h3>
    <?php prettyPrintXML($profileXml); ?>
    <hr />
    <h3>Sleep Info (30d)</h3>
    <div class="graph_wrapper">
      <div class="graph_header">
        <div class="start_label">8:00pm</div>
        <div class="end_label">12 Noon</div>
        <span class="clear_both"></span>
      </div>
      <div class="sleep_start_times"></div>
    </div>
    <?php prettyPrintArray($sleepInfo); ?>
    <hr />
    <h3>Today sleep</h3>
    </div>
    <?php prettyPrintXML($sleepXml); ?>
  </body>
</html>

