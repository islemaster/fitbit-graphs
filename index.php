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
    <script language="javascript" type="text/javascript">
      var sleepInfo = <?php echo json_encode($sleepInfo); ?>;

      $(function () {

        // D3 generate sleep start time chart
        var scaleFactor = 1.0;
        var offsetMinutes = 20 * 60;
        var wrapperDiv = d3.select('.sleep_start_times');

        wrapperDiv.style('width', (scaleFactor * (16 * 60)) + 'px');

        var singleBar = wrapperDiv.selectAll('div')
          .data(sleepInfo)
          .enter()
          .append('div');
        
        singleBar
          .style('width', function (d) {
            return (scaleFactor * (d.timeInBed)) + 'px';
          })
          .style('margin-left', function (d) {
            if (d.startTime === '') {
              return '0px';
            }
            var parts = d.startTime.split(':');
            var hours = parseInt(parts[0], 10);
            var minutes = parseInt(parts[1], 10);
            if (hours < 12) {
              hours += 24;
            }
            return (scaleFactor * (hours * 60 + minutes - offsetMinutes)) + 'px';
          })
          .text(function (d) {
            if (d.startTime === '') {
              return "\xA0";
            }
            var hours = Math.floor(d.timeInBed / 60);
            var minutes = d.timeInBed % 60;
            return d.dateTime + ' - ' + hours + 'h ' + minutes + 'm';
          });

        singleBar.append('div')
          .style('float', 'left')
          .text(function (d) {
            if (d.startTime === '') {
              return '';
            }

            var parts = d.startTime.split(':');
            var hours = parseInt(parts[0], 10);
            var minutes = parseInt(parts[1], 10);
            var ampm = hours >= 12 ? 'pm' : 'am';
            hours = (hours % 12 === 0) ? 12 : hours % 12;
            minutes = minutes < 10 ? '0' + minutes : minutes;
            return hours + ':' + minutes + ampm;
          });

        singleBar.append('div')
          .style('float', 'right')
          .text(function (d) {
            if (d.startTime === '') {
              return '';
            }

            var parts = d.startTime.split(':');
            var hours = parseInt(parts[0], 10);
            var minutes = parseInt(parts[1], 10);
            var startTimeMinutes = hours * 60 + minutes;
            var endTimeMinutes = (startTimeMinutes + parseInt(d.timeInBed, 10)) % (24 * 60);

            hours = Math.floor(endTimeMinutes / 60);
            var ampm = hours >= 12 ? 'pm' : 'am';
            minutes = endTimeMinutes % 60;
            minutes = minutes < 10 ? '0' + minutes : minutes;
            
            return hours + ':' + minutes + ampm;
          });


        // Set up auto-hide for XML
        $('.xml').click(function (event) {
          var preTag = $(event.target).find('pre');
          if (preTag.is(':visible')) {
            preTag.hide();
          } else {
            preTag.show();
          }
        });
        $('.xml pre').hide();

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

