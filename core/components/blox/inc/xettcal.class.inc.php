<?php
class xettcal {
	// Declaring private variables
	var $xetconfig;
	
	//
	// Constructor class
	//
	function xettcal($xetID) {
		$this->xetID = $xetID;
		$this->events_grouped='0';
		$this->xetconfig=array();
	}

//////////////////////////////////////////////////
//Kalenderarray generieren 
/////////////////////////////////////////////////
function makecalendar($xetconfig) {
	$firstmonth = abs($xetconfig['month']);
	$timestampfirstday = xetadodb_mktime(0, 0, 0, $firstmonth, 01, $xetconfig['year']);
	$monate = array ();
	for ($i = 0; $i < $xetconfig['monthcount']; $i++) {
		$monate[] = $firstmonth + $i;
	}
	$output = '';
	$gmonth['year'] = $xetconfig['year'];

	if ($xetconfig['display'] == 'yearcal'){
			$events = $this->gettheevents($timestampfirstday);
	}	
	foreach ($monate as $monat) {
		$monthcal = $this->getMonthCal($xetconfig['year'], $monat);
		$output .= $this->displaymonth($monthcal,$events);
	}
	$tpl = new mgChunkie($this->xettpl['xetouterTpl']);
	$xetouterTplData = array ();
	$xetouterTplData['monthTpls'] = $output;
	$xetouterTplData['tsconfig'] = $timestampfirstday;
	$xetouterTplData['config'] = $xetconfig;
	$xetouterTplData['userID'] = $xetconfig['userID'];
	$tpl->addVar('xeventtable', $xetouterTplData);
	$this->regSnippetScriptsAndCSS();
	return $tpl->Render();
}	
	
//////////////////////////////////////////////////
//Monatskalender als Array erstellen
/////////////////////////////////////////////////
function getMonthCal($year, $month) {
	$format2 = "%A"; //Ausgabe Wochentag$format3 = "%m"; //Ausgabe Monat als Zahl$format4 = "%B"; //Ausgabe Monatsname $format5 = "%Y"; //Ausgabe Jahr  $format6 = "%u"; //Ausgabe Wochentag als Zahl$format7 = "%d.%m.%Y";
	$monthcal = array ();
	$monthcal['month'] = $month;
	$monthcal['year'] = $year;
	$timestampfirstday = xetadodb_mktime(0, 0, 0, $monthcal['month'], 01, $monthcal['year']);
	$firstweekdaynum = ($w = xetadodb_date("w", $timestampfirstday)) ? $w : 7;
	$days_in_month = xetadodb_date("t", $timestampfirstday);
	$monthcal['firstdate'] = $timestampfirstday;
	$weekcount = 1; //Woche des Monats
	$day_in_month = 1; //Tag des Monats
	$dayisempty = 1; //leere Wochentage vor dem ersten und nach dem letzten Monatstag 
	$afterlastday = 0; //letzter Tag des Monats erreicht
    while ($afterlastday == 0) {
    	for ($daycount = 1; $daycount <= 7; $daycount++) {
    		if (($daycount == $firstweekdaynum) && ($afterlastday == 0))
    			$dayisempty = 0;
    
    		if ($dayisempty == 1) {
    			$monthcal['weeks'][$weekcount]['days'][$daycount]['timestamp'] = 'dayisempty';
    		} else {
    			$date = xetadodb_mktime(0, 0, 0, $monthcal['month'], $day_in_month, $monthcal['year']);
    			$monthcal['weeks'][$weekcount]['days'][$daycount]['timestamp'] = $date;
    			$day_in_month++;
    		}
    		if ($day_in_month == $days_in_month +1) {
    			$dayisempty = 1;
    			$afterlastday = 1;
    		}
    	}
    	$monthcal['weeks'][$weekcount]['timestamp'] = $date;
    	$weekcount++;
    }
    return $monthcal;
}	
//////////////////////////////////////////////////
//Monatstage als Array erstellen
/////////////////////////////////////////////////
function getMonthDays($year, $month)
{
    //$format2 = "%A"; //Ausgabe Wochentag$format3 = "%m"; //Ausgabe Monat als Zahl$format4 = "%B"; //Ausgabe Monatsname $format5 = "%Y"; //Ausgabe Jahr  $format6 = "%u"; //Ausgabe Wochentag als Zahl$format7 = "%d.%m.%Y";
    $monthcal = array ();
    $monthcal['month'] = $month;
    $monthcal['year'] = $year;
    $timestampfirstday = xetadodb_mktime(0, 0, 0, $monthcal['month'], 01, $monthcal['year']);
    $firstweekdaynum = ($w = xetadodb_date("w", $timestampfirstday))?$w:7;
    $days_in_month = xetadodb_date("t", $timestampfirstday);
    $monthcal['firstdate'] = $timestampfirstday;
    $weekcount = 1; //Woche des Monats
    $day_in_month = 1; //Tag des Monats
    $dayisempty = 1; //leere Wochentage vor dem ersten und nach dem letzten Monatstag
    //$afterlastday = 0; //letzter Tag des Monats erreicht
    for ($daycount = 1; $daycount <= $days_in_month; $daycount++)
    {
        $monthcal['days'][$daycount]['isfirstday'] = '0';
        $monthcal['days'][$daycount]['islastday'] = '0';
        $date = xetadodb_mktime(0, 0, 0, $monthcal['month'], $daycount, $monthcal['year']);
        $monthcal['days'][$daycount]['timestamp'] = $date;
		$monthcal['days'][$daycount]['tsday'] = $date;
		$monthcal['days'][$daycount]['daynum'] = $daycount;
        if ($daycount == '1')
        {
            $monthcal['firstdate'] = $date;
            $monthcal['days'][$daycount]['isfirstday'] = '1';
        }
        if ($daycount == $days_in_month)
        {
            $monthcal['lastdate'] = $date;
            $monthcal['days'][$daycount]['islastday'] = '1';
        }

    }

    return $monthcal;
}
////////////////////////////////////////////////////////
//Monatskalender anzeigen (monthTpl,weedaysTpl)
////////////////////////////////////////////////////////
function makeMonthArray($xetconfig,$monthcal,$output_arr=array(),$events=array()) {
	global $modx;
	$weeks = $monthcal['weeks'];
	$tmpmonth = $monthcal['month'];
	$tmpyear = $monthcal['year'];
	$monthtimestamp = xetadodb_mktime(0, 0, 0, $tmpmonth, 01, $tmpyear);
	/*
	if ($xetconfig['display'] == 'yearcal'){
		$monthevents = $this->extractevents($events, $this->get_ts_monthstart($monthtimestamp), $this->get_ts_monthend($monthtimestamp));
	}else{
		$monthevents = $this->gettheevents($monthtimestamp);
	}
	*/
	$weeks_arr=array();		
	foreach ($weeks as $week) {
		$weeks_arr[]= $this->makeWeekArray($xetconfig,$week, $weekdays, $events);
	}
	$month_arr=array();
	$weekdays=array();
	//$month_arr['weekdays'] = $weekdays;



$timestamp = xetadodb_mktime(0, 0, 0, xetadodb_date("m", $monthtimestamp)-1, '01', xetadodb_date("Y", $monthtimestamp));
$link['month'] = xetadodb_date("m", $timestamp);
$link['year'] = xetadodb_date("Y", $timestamp);
$month_arr['link_prevmonth']=$this->blox->smartModxUrl($modx->documentObject["id"],NULL, $link);
$timestamp = xetadodb_mktime(0, 0, 0, xetadodb_date("m", $monthtimestamp)+1, '01', xetadodb_date("Y", $monthtimestamp));
$link['month'] = xetadodb_date("m", $timestamp);
$link['year'] = xetadodb_date("Y", $timestamp);
$month_arr['link_nextmonth']=$this->blox->smartModxUrl($modx->documentObject["id"],NULL, $link);
$timestamp = xetadodb_mktime(0, 0, 0, xetadodb_date("m", $monthtimestamp), '01', xetadodb_date("Y", $monthtimestamp)-1);
$link['month'] = xetadodb_date("m", $timestamp);
$link['year'] = xetadodb_date("Y", $timestamp);
$month_arr['link_prevyear']=$this->blox->smartModxUrl($modx->documentObject["id"],NULL, $link);
$timestamp = xetadodb_mktime(0, 0, 0, xetadodb_date("m", $monthtimestamp), '01', xetadodb_date("Y", $monthtimestamp)-1);
$link['month'] = xetadodb_date("m", $timestamp);
$link['year'] = xetadodb_date("Y", $timestamp);
$month_arr['link_nextyear']=$this->blox->smartModxUrl($modx->documentObject["id"],NULL, $link);	
$link=array();
$removearray=array('tsday');
$link['tsmonth'] = $monthtimestamp;
$month_arr['link_tsmonth']=$this->blox->smartModxUrl($modx->documentObject["id"],NULL, $link,$removearray);		
	
	$month_arr['tsmonth'] = $monthtimestamp;
	$month_arr['innerrows']['week'] = $weeks_arr;
	$month_arr['innerrows']['weekdays'][] = $weekdays;
	//$monthTplData['config'] = $xetconfig;
	//$tpl->addVar('xeventtable', $monthTplData);
	$output_arr['innerrows']['month'][]=$month_arr;
	unset($month_arr);
	return $output_arr;
}	
//////////////////////////////////////////////////
//Daten-Template generieren (weekdataTpl)
/////////////////////////////////////////////////
function makeWeekArray($xetconfig,$week = array (), $weekdays = array (), $events = array ())
{
    $output = '';
    $date = $week['timestamp'];
    $dateday = xetadodb_strftime("%d", $date);
    $datemonth = xetadodb_strftime("%m", $date);
    $dateyear = xetadodb_strftime("%Y", $date);
    $days_arr = array ();
    for ($daycount = 1; $daycount <= 7; $daycount++)
    {
        $daytimestamp = $week['days'][$daycount]['timestamp'];
        if ($daytimestamp == 'dayisempty')
        {
            $dayevents = '';
        } else
        {
            if ($this->events_grouped == '1')
            {
                $dayevents = array ();
				//print_r($events);
                foreach ($events as $key=>$groupevents)
                
                {
                    if (($key !== 'groupdatas') && (is_array($groupevents)))
                    {
                        $tmpevents = $this->extractevents($groupevents, $this->get_ts_daystart($daytimestamp), $this->get_ts_dayend($daytimestamp));
                    }
                
                    $group = $groupevents['groupdatas'];
                    $group['groupeventscount'] = count($tmpevents);
                
                    if (is_array($tmpevents[0]))
                    $group = array_merge($group, $tmpevents[0]);
                    $dayevents[] = $group;
                }
                //print_r($dayevents);
                }
            else
            {
                $dayevents = $this->extractevents($events, $this->get_ts_daystart($daytimestamp), $this->get_ts_dayend($daytimestamp));
            }
        }
        $days_arr[] = $this->makeDayArray($xetconfig,$dayevents, $daytimestamp);
        //print_r($days_arr);
    }
    //$tpl = new mgChunkie($this->xettpl['weekdataTpl']);
    $weekdataTplData = array ();
    $weekdataTplData['tsweek'] = $date;
    $weekdataTplData['innerrows']['day'] = $days_arr;
    $weekdataTplData['innerrows']['weekdays'][] = $weekdays;
    //$weekdataTplData['config'] = $xetconfig;
    //$tpl->addVar('xeventtable', $weekdataTplData);
    return $weekdataTplData;
}	

//////////////////////////////////////////////////////
//Daten-Template generieren (datarowTpl,dataouterTpl)
//////////////////////////////////////////////////////
function makeDayArray($xetconfig,$events, $date, $sortOrder='ASC') {
	global $modx;
	
	if (is_array($events)) {
		
		$timestampdaystart = $this->get_ts_daystart($date);
		$timestampmonthstart = $this->get_ts_monthstart($date);
		$timestampweekstart = $this->get_ts_weekstart($date);
		$timestampyearstart = $this->get_ts_yearstart($date);
		if ($this->xetconfig['counteventstarts'] == '1') {
			$this->counteventstarts($events);
		}
		$data_array=array();
		$rowid = 0;
		$rowscount = 0;
		$dayeventscount = 0;
		$weekeventscount = 0;
		$montheventscount = 0;
		$yeareventscount = 0;
		$theyearend = $themonthend = $theweekend = $thedayend = $sortOrder=='ASC'? -10000000000000:10000000000000;
		$theyearstart = $themonthstart = $theweekstart = $thedaystart = $sortOrder=='ASC'? -10000000000000:10000000000000;
		$rowscount = count($events);
		foreach ($events as $event) {
			$ID = $event['ID'];
			/*
			$rowTpl = $this->xettpl['datarowTpl'];
			if (isset ($event['tpl'])) {
				$tplfilename = $xetconfig['tplpath'] . "/" . $event['tpl'];
				if (($event['tpl'] !== '') && (file_exists($tplfilename))) {
					$rowTpl = "@FILE:" . $tplfilename;
				}
			}
			*/
			$eventdaystart = $this->get_ts_daystart($event['Time']);
			$eventweekstart = $this->get_ts_weekstart($event['Time']);
			$eventmonthstart = $this->get_ts_monthstart($event['Time']);
			$eventyearstart = $this->get_ts_yearstart($event['Time']);
			$eventdayend = $this->get_ts_dayend($event['Time']);
			$eventweekend = $this->get_ts_weekend($event['Time']);
			$eventmonthend = $this->get_ts_monthend($event['Time']);
			$eventyearend = $this->get_ts_yearend($event['Time']);

            if ($sortOrder == 'ASC')
            {
                if ($event['Time'] > $thedayend)
                {
                    $dayeventscount = 1;
                    $thedayend = $eventdayend;
                } else
                {
                    $dayeventscount++;
                }
                if ($event['Time'] > $theweekend)
                {
                    $weekeventscount = 1;
                    $theweekend = $eventweekend;
                } else
                {
                    $weekeventscount++;
                }
                if ($event['Time'] > $themonthend)
                {
                    $montheventscount = 1;
                    $themonthend = $eventmonthend;
                } else
                {
                    $montheventscount++;
                }
                if ($event['Time'] > $theyearend)
                {
                    $yeareventscount = 1;
                    $theyearend = $eventyearend;
                } else
                {
                    $yeareventscount++;
                }
            
            
            } else
            {
                if ($event['Time'] < $thedaystart)
                {
                    $dayeventscount = 1;
                    $thedaystart = $eventdaystart;
                } else
                {
                    $dayeventscount++;
                }
                if ($event['Time'] < $theweekstart)
                {
                    $weekeventscount = 1;
                    $theweekstart = $eventweekstart;
                } else
                {
                    $weekeventscount++;
                }
                if ($event['Time'] < $themonthstart)
                {
                    $montheventscount = 1;
                    $themonthstart = $eventmonthstart;
                } else
                {
                    $montheventscount++;
                }
                if ($event['Time'] < $theyearstart)
                {
                    $yeareventscount = 1;
                    $theyearstart = $eventyearstart;
                } else
                {
                    $yeareventscount++;
                }
            
            }



			$rowid++;
			$event['rowid'] = $rowid;
			$event['rowscount'] = $rowscount;
			$event['daydatarowid'] = $dayeventscount;
			$event['dayeventsid'] = $dayeventscount;
			$event['weekeventsid'] = $weekeventscount;
			$event['montheventsid'] = $montheventscount;
			$event['yeareventsid'] = $yeareventscount;
			if ($xetconfig['countdayevents'] == '1') {
				$this->countdayevents($events, $event['Time']);
				$event['dayeventscount'] = $this->eventscount['day'][$eventdaystart];
			}
			if ($xetconfig['countweekevents'] == '1') {
				$this->countweekevents($events, $event['Time']);
				$event['weekeventscount'] = $this->eventscount['week'][$eventweekstart];
			}
			if ($xetconfig['countmonthevents'] == '1') {
				$this->countmonthevents($events, $event['Time']);
				$event['montheventscount'] = $this->eventscount['month'][$eventmonthstart];
			}
			if ($xetconfig['countyearevents'] == '1') {
				$this->countyearevents($events, $event['Time']);
				$event['yeareventscount'] = $this->eventscount['year'][$eventyearstart];
			}
			if ($xetconfig['counteventstarts'] == '1') {
				$event['daystartscount'] = $this->eventscount['daystarts'][$eventdaystart];
				$event['weekstartscount'] = $this->eventscount['weekstarts'][$eventweekstart];
				$event['monthstartscount'] = $this->eventscount['monthstarts'][$eventmonthstart];
				$event['yearstartscount'] = $this->eventscount['yearstarts'][$eventyearstart];
			}
			$event['fromprevday'] = ($event['Time'] < $timestampdaystart) ? 1 : 0;
			$event['fromprevweek'] = ($event['Time'] < $timestampweekstart) ? 1 : 0;
			$event['fromprevmonth'] = ($event['Time'] < $timestampmonthstart) ? 1 : 0;
			$event['fromprevyear'] = ($event['Time'] < $timestampyearstart) ? 1 : 0;
			$event['tsday'] = $date;
			$data_array[]= $event;
		}
	}
 	//$tpl = new mgChunkie($this->xettpl['dataouterTpl']);
	$day_array = array ();
    $link = array ();
	$removearray=array('tsmonth');
    $link['tsday'] = $date;
    $day_array['link_tsday'] = $this->blox->smartModxUrl($modx->documentObject["id"], NULL, $link,$removearray);
	$day_array['date'] = $date;
	$day_array['daytimestamp'] = $date;
	$day_array['tsday'] = $date;
	$day_array['innerrows']['datarow'] = $data_array;
	$day_array['datarowscount'] = $rowscount;
	$day_array['dayeventscount'] = $dayeventscount;
	//$day_array['config'] = $xetconfig;
	//$tpl->addVar('xeventtable', $dataouterTplData);
	//$output = $tpl->Render();
	return $day_array;
}

//////////////////////////////////////////////////
//Events innerhalb einer Zeitspanne filtern
/////////////////////////////////////////////////
function extractevents($events, $start, $end) {
	$extractevents = array ();
    if (count($events)>0){
	foreach ($events as $event) {
		$startday = xetadodb_date("d", $event['Time']);
		$startmonth = xetadodb_date("m", $event['Time']);
		$startyear = xetadodb_date("Y", $event['Time']);
		$starthour = xetadodb_date("H", $event['Time']);
		$startminute = xetadodb_date("i", $event['Time']);
		$endday = xetadodb_date("d", $event['Timeend']);
		$endmonth = xetadodb_date("m", $event['Timeend']);
		$endyear = xetadodb_date("Y", $event['Timeend']);
		$endhour = xetadodb_date("H", $event['Timeend']);
		$endminute = xetadodb_date("i", $event['Timeend']);
		$eventstart = xetadodb_mktime($starthour, $startminute, 0, $startmonth, $startday, $startyear);
		$eventend = xetadodb_mktime($endhour, $endminute, 0, $endmonth, $endday, $endyear);
		if ((($eventstart <= $start) && ($eventend >= $start) || ($eventstart >= $start) && ($eventstart <= $end) || ($eventend >= $start) && ($eventend <= $end))) {
			$extractevents[] = $event;
		}
	}    	
    }

	return $extractevents;
}

function getISOkw($timestamp) {
	$tsweekThu = $this->get_ts_weekstart($timestamp) + 3 * 86400; //Donnerstag=Montag+3 Tage
	$kwyear = xetadodb_strftime("%Y", $tsweekThu);
	$ts4Jan = xetadodb_mktime(0, 0, 0, 01, 04, $kwyear);
	$tsfirstDo = $this->get_ts_weekstart($ts4Jan) + 3 * 86400;
	$isokw = ceil(($tsweekThu - $tsfirstDo) / 86400 / 7 + 1);
	$weekID = array ();
	$weekID['weekID'] = $isokw;
	$weekID['year'] = $kwyear;
	return $weekID;
}

function get_ts_weekstart($timestamp) {
	$ts_dow = ($w = xetadodb_date("w", $timestamp)) ? $w : 7;
	$ts_day = xetadodb_strftime("%d", $timestamp);
	$ts_month = xetadodb_strftime("%m", $timestamp);
	$ts_year = xetadodb_strftime("%Y", $timestamp);
	$timestampweekstart = xetadodb_mktime(0, 0, 0, $ts_month, $ts_day - $ts_dow +1, $ts_year);
	return $timestampweekstart;
}

function get_ts_weekend($timestamp) {
	$ts_dow = ($w = xetadodb_date("w", $timestamp)) ? $w : 7;
	$ts_day = xetadodb_strftime("%d", $timestamp);
	$ts_month = xetadodb_strftime("%m", $timestamp);
	$ts_year = xetadodb_strftime("%Y", $timestamp);
	$timestampweekend = xetadodb_mktime(23, 59, 59, $ts_month, $ts_day +7 - $ts_dow, $ts_year);
	return $timestampweekend;
}

function get_ts_daystart($timestamp) {
		$dateday = xetadodb_strftime("%d", $timestamp);
		$datemonth = xetadodb_strftime("%m", $timestamp);
		$dateyear = xetadodb_strftime("%Y", $timestamp);
		$timestampdaystart = xetadodb_mktime(0, 0, 0, $datemonth, $dateday, $dateyear);
	return $timestampdaystart;
}

function get_ts_dayend($timestamp) {
		$dateday = xetadodb_strftime("%d", $timestamp);
		$datemonth = xetadodb_strftime("%m", $timestamp);
		$dateyear = xetadodb_strftime("%Y", $timestamp);
		$timestampdayend = xetadodb_mktime(23, 59, 59, $datemonth, $dateday, $dateyear);
	return $timestampdayend;
}

function get_ts_monthstart($timestamp) {
		$dateday = xetadodb_strftime("%d", $timestamp);
		$datemonth = xetadodb_strftime("%m", $timestamp);
		$dateyear = xetadodb_strftime("%Y", $timestamp);
		$timestampmonthstart = xetadodb_mktime(0, 0, 0, $datemonth, 01, $dateyear);
	return $timestampmonthstart;
}

function get_ts_monthend($timestamp) {
		$dateday = xetadodb_strftime("%d", $timestamp);
		$datemonth = xetadodb_strftime("%m", $timestamp);
		$dateyear = xetadodb_strftime("%Y", $timestamp);
		$days_in_month = xetadodb_date("t", $timestamp);		
		$timestampmonthend = xetadodb_mktime(23, 59, 59, $datemonth, $days_in_month, $dateyear);
	return $timestampmonthend;
}

function get_ts_yearstart($timestamp) {
		$dateday = xetadodb_strftime("%d", $timestamp);
		$datemonth = xetadodb_strftime("%m", $timestamp);
		$dateyear = xetadodb_strftime("%Y", $timestamp);
		$timestampyearstart = xetadodb_mktime(0, 0, 0, 01, 01, $dateyear);
	return $timestampyearstart;
}

function get_ts_yearend($timestamp) {
		$dateday = xetadodb_strftime("%d", $timestamp);
		$datemonth = xetadodb_strftime("%m", $timestamp);
		$dateyear = xetadodb_strftime("%Y", $timestamp);
		$timestampyearend = xetadodb_mktime(23, 59, 59, 12, 31, $dateyear);
	return $timestampyearend;
}
/**
* @name getDateFromTV
*
* Extracts the date in YYYY-MM-DD format from the TV field.
*/
function getDateFromTV($tvVal = '')
{
    $date = substr($tvVal, 6, 4).'-'.substr($tvVal, 3, 2).'-'.substr($tvVal, 0, 2);
    return $date;
}

/**
 * @name getTimeFromTV
 *
 * Extracts the time in HH:MM:SS format from the TV field.
 */
function getTimeFromTV($tvVal = '')
{
    $time = substr($tvVal, 11, 8);
    return $time;
}
    /**
     * @name getEvents
     * Retrieves all published events the user has access to under a given root
     * folder (or folders) and within a specified time period.
     *
     * @param string $startDate REQUIRED. The first date in the acceptable date
     * range in YYYY-MM-DD format.
     * @param string $endDate REQUIRED. The last date in the acceptable date
     * range in YYYY-MM-DD format.
     * @param string $contentFields Fields you'd like to have returned from
     * the content table.
     * @param mixed $limit The maximum number of events to return. Default 0
     * will not impose a limit.
     *
     */
    function getEvents($startDate='0000-00-00',$endDate='0000-00-00',$contentFields=null,$limit=0,$orderDir='ASC',$nodes=0,$getAll='0',$getTVs='')
    {   global $modx;
	   // Initialize events
	   $contentFields=(is_null($contentFields)?'id,pagetitle,description,published':$contentFields);
	   
        $events = array();

        // Verify parameters
        if ((
            '0000-00-00' != $startDate && '0000-00-00' != $endDate &&
            $startDate == date('Y-m-d',strtotime($startDate)) ||
            $endDate == date('Y-m-d',strtotime($endDate))
        )||($getAll!=='0'))
		
        {   
            // Get all possible events by node(s)
            
           	$parents = implode(',',$this->getSubParents($nodes));
          
            if ( !$parents )
            {
                return $events;
            }
            // Query helps
            $t_sc = $modx->getFullTableName('site_content');
            $t_t = $modx->getFullTableName('site_templates');
            $t_tv = $modx->getFullTableName('site_tmplvars');
            $t_cv = $modx->getFullTableName('site_tmplvar_contentvalues');
            $t_dg = $modx->getFullTableName('document_groups');
            $tmplVarEnd = $this->xetconfig['custom']['tmplVarEnd'];
            $tmplVarHideTime = $this->xetconfig['custom']['tmplVarHideTime'];
            $tmplVarStart = $this->xetconfig['custom']['tmplVarStart'];
            $allowedTemplates = "'".implode("','",$this->xetconfig['custom']['templateNames'])."'";
            if ( $docGroup = $modx->getUserDocGroups() )
            {
                $docGroup = implode(',',$docGroup);
            }
            else
            {
                $docGroup = 0;
            }

            $limit = ( $limit && is_numeric($limit) ) ? "LIMIT $limit" : '';

            $contentFields = explode(',',$contentFields);
            $cFields = '';
            foreach ( $contentFields as $cf )
            {
                $cFields .= "sc.$cf,";
            }
            //Done: foreach for TVfields for my csv-export-module 
			$tvValues = '';
			$tvFroms = '';
			$tvJoins = '';
			$tvNames = '';
			$getTVs=($getTVs !=='')?explode(',',$getTVs):array();
            if (count($getTVs) > 0)
            {
                foreach ($getTVs as $tv)
                {
                    $tvValues .= $tv."_tvcv.value AS $tv,";
                    $tvFroms .= "$t_tv AS ".$tv."_tv,";
                    $tvJoins .= "
                            LEFT JOIN
                            $t_cv AS ".$tv."_tvcv ON ".$tv."_tvcv.contentid = sc.id AND ".$tv."_tvcv.tmplvarid = ".$tv."_tv.id";
                    $tvNames .= "
                            AND
                            ".$tv."_tv.name = '$tv'";
                }
            }
            if ($getAll=='0'){
              $timeRange="
                # Start date
                AND
                CONCAT(SUBSTRING(s_tvcv.value,7,4),'-',SUBSTRING(s_tvcv.value,4,2),'-',SUBSTRING(s_tvcv.value,1,2)) <= '$endDate'
                # End date
                AND
                (
                    CONCAT(SUBSTRING(e_tvcv.value,7,4),'-',SUBSTRING(e_tvcv.value,4,2),'-',SUBSTRING(e_tvcv.value,1,2)) >= '$startDate'
                    OR
                    (
                        e_tvcv.value IS NULL
                        AND
                        CONCAT(SUBSTRING(s_tvcv.value,7,4),'-',SUBSTRING(s_tvcv.value,4,2),'-',SUBSTRING(s_tvcv.value,1,2)) >= '$startDate'
                    )
                )			  
			  ";           	
            }

            // Build monster query
            $sql = "
                SELECT DISTINCT
                $cFields
				$tvValues
                s_tvcv.value AS startDate,
                e_tvcv.value AS endDate
                FROM
                ($t_sc AS sc,
				$tvFroms
                $t_t AS t,
                $t_tv AS s_tv,
                $t_tv AS e_tv,
                $t_tv AS h_tv)
                # Start Date
                JOIN
                $t_cv AS s_tvcv ON s_tvcv.contentid = sc.id AND s_tv.id = s_tvcv.tmplvarid
                $tvJoins
				# End Date
                LEFT JOIN
                $t_cv AS e_tvcv ON e_tvcv.contentid = sc.id AND e_tvcv.tmplvarid = e_tv.id

                LEFT JOIN
                $t_dg AS dg ON dg.document = sc.id
                WHERE
                (sc.privateweb = 0 OR dg.document_group IN ($docGroup))
                AND
                sc.parent IN ($parents)
                AND
                sc.published = 1
                AND
                sc.deleted <> 1				
                AND
                t.templatename IN ($allowedTemplates)
                # Start date
                AND
                s_tv.name = '$tmplVarStart'
                # End date
                AND
                e_tv.name = '$tmplVarEnd'
                $timeRange
				$tvNames
                ORDER BY
                CONCAT(SUBSTRING(s_tvcv.value,7,4),'-',SUBSTRING(s_tvcv.value,4,2),'-',SUBSTRING(s_tvcv.value,1,2),' ',RIGHT(s_tvcv.value,8))
                $orderDir $limit";
		
                // Get events
				//echo $sql;
                $rs = $modx->db->query($sql);
                $events = $modx->db->makeArray($rs);
        }

        return $events;
    }
    /**
     * @name getSubParents
     *
     * Improved version of getSubParents which should reduce the number of
     * queries substantially.
     *
     * @param mixed $nodeIds Numeric string or integer ID of top parent folder
     * or folders. For multiple parents, a comma separated list or array is
     * acceptable.
     */
    function getSubParents($nodeIds=0)
    { global $modx;
        
        $parents = $this->makeIntegerArray($nodeIds);

		$children = $modx->getChildIds($nodeIds);
		
        $tempSql = "SELECT parent, count(*) AS qty FROM ".$modx->getFullTableName('site_content')." GROUP BY parent";
        $rs=$modx->db->query($tempSql);
        $rows=$modx->db->makeArray($rs);
            
            if ( count($rows)>0 )
            {
                foreach ($rows as $row)
                {
                	if (in_array($row['parent'],$children))
                    {$parents[] = $row['parent'];}
                }

            }
       
        return $parents;
    }
    /**
     * @name makeIntegerArray
     *
     * Turns a comma separated list, integer, array, or string of IDs into a
     * comma separated list.
     */
    function makeIntegerArray()
    {
        // Initialize collector array
        $collection = array();

        // Get passed in args
        $args = func_get_args();

        foreach ($args as $a)
        {
            // Strings
            if ( is_string($a) )
            {
                // Explode into array
                $a = explode(',',$a);
            }

            // Misc numeric (integer, float)
            if ( is_numeric($a) )
            {
                // Assign value to single array
                $a = array($a);
            }

            // Arrays
            if ( is_array($a) )
            {
                // Cycle thru and collect new integer values
                foreach ( $a as $aVal )
                {
                    if ( is_numeric($aVal))
                    {
                        // Add integer
                        $collection[] = (int)$aVal;
                    }
                    else if ( is_array($aVal) )
                    {
                        $collection = array_merge($collection,$this->makeIntegerArray($aVal));
                    }
                }
            }
            $collection = array_unique($collection);
        }

        return $collection;
    }		
//////////////////////////////////////////////////
//Tages-Events zaehlen
/////////////////////////////////////////////////
function countdayevents($events, $date) {
	$start = $this->get_ts_daystart($date);
	$end = $this->get_ts_dayend($date);
	$this->eventscount['day'][$start] = $this->countevents($events, $start, $end);
	return;
}
//////////////////////////////////////////////////
//Wochen-Events zaehlen
/////////////////////////////////////////////////
function countweekevents($events, $date) {
	$start = $this->get_ts_weekstart($date);
	$end = $this->get_ts_weekend($date);
	$this->eventscount['week'][$start] = $this->countevents($events, $start, $end);
	return;
}

//////////////////////////////////////////////////
//Monats-Events zaehlen
/////////////////////////////////////////////////
function countmonthevents($events, $date) {
	$start = $this->get_ts_monthstart($date);
	$end = $this->get_ts_monthend($date);
	$this->eventscount['month'][$start] = $this->countevents($events, $start, $end);
	return;
}

//////////////////////////////////////////////////
//Jahres-Events zaehlen
/////////////////////////////////////////////////
function countyearevents($events, $date) {
	$start = $this->get_ts_yearstart($date);
	$end = $this->get_ts_yearend($date);
	$this->eventscount['year'][$start] = $this->countevents($events, $start, $end);
	return;
}

//////////////////////////////////////////////////
//Events innerhalb einer Zeitspanne zaehlen
/////////////////////////////////////////////////
function countevents($events, $start, $end) {
	$eventscount = 0;
	foreach ($events as $event) {
		$startday = xetadodb_date("d", $event['Time']);
		$startmonth = xetadodb_date("m", $event['Time']);
		$startyear = xetadodb_date("Y", $event['Time']);
		$starthour = xetadodb_date("H", $event['Time']);
		$startminute = xetadodb_date("i", $event['Time']);
		$endday = xetadodb_date("d", $event['Timeend']);
		$endmonth = xetadodb_date("m", $event['Timeend']);
		$endyear = xetadodb_date("Y", $event['Timeend']);
		$endhour = xetadodb_date("H", $event['Timeend']);
		$endminute = xetadodb_date("i", $event['Timeend']);
		$eventstart = xetadodb_mktime($starthour, $startminute, 0, $startmonth, $startday, $startyear);
		$eventend = xetadodb_mktime($endhour, $endminute, 0, $endmonth, $endday, $endyear);
		if ((($eventstart <= $start) && ($eventend >= $start) || ($eventstart >= $start) && ($eventstart <= $end) || ($eventend >= $start) && ($eventend <= $end))) {
			$eventscount++;
		}
	}
	return $eventscount;
}
//////////////////////////////////////////////////
//Event-beginns zaehlen
/////////////////////////////////////////////////
function counteventstarts($events) {
	$dayeventscount = 0;
	$weekeventscount = 0;
	$montheventscount = 0;
	$yeareventscount = 0;
	$theyearend = $themonthend = $theweekend = $thedayend = -10000000000000;
	foreach ($events as $event) {
		if (isset ($event['Time'])) {
			$eventdaystart = $this->get_ts_daystart($event['Time']);
			$eventweekstart = $this->get_ts_weekstart($event['Time']);
			$eventmonthstart = $this->get_ts_monthstart($event['Time']);
			$eventyearstart = $this->get_ts_yearstart($event['Time']);
			$eventdayend = $this->get_ts_dayend($event['Time']);
			$eventweekend = $this->get_ts_weekend($event['Time']);
			$eventmonthend = $this->get_ts_monthend($event['Time']);
			$eventyearend = $this->get_ts_yearend($event['Time']);
			if ($event['Time'] > $thedayend) {
				$dayeventscount = 1;
				$thedayend = $eventdayend;
			} else {
				$dayeventscount++;
			}
			if ($event['Time'] > $theweekend) {
				$weekeventscount = 1;
				$theweekend = $eventweekend;
			} else {
				$weekeventscount++;
			}
			if ($event['Time'] > $themonthend) {
				$montheventscount = 1;
				$themonthend = $eventmonthend;
			} else {
				$montheventscount++;
			}
			if ($event['Time'] > $theyearend) {
				$yeareventscount = 1;
				$theyearend = $eventyearend;
			} else {
				$yeareventscount++;
			}
			$this->eventscount['daystarts'][$eventdaystart] = $dayeventscount;
			$this->eventscount['weekstarts'][$eventweekstart] = $weekeventscount;
			$this->eventscount['monthstarts'][$eventmonthstart] = $montheventscount;
			$this->eventscount['yearstarts'][$eventyearstart] = $yeareventscount;
		}
	}
	return;
}

//////////////////////////////////////////////////
//Zeiten generieren
/////////////////////////////////////////////////
function makeevents() {
	$rows = $this->getformfields();
	foreach ($rows as $row) {
		if (($this->xetconfig['lasthour'] == '') || ($this->xetconfig['lastminute'] == '')) {
			$this->xetconfig['lasthour'] = $this->xetconfig['starthour'];
			$this->xetconfig['lastminute'] = $this->xetconfig['startminute'];
		}
		if (($this->xetconfig['lastday'] == '') || ($this->xetconfig['lastmonth'] == '') || ($this->xetconfig['lastyear'] == '')) {
			$this->xetconfig['lastday'] = $this->xetconfig['startday'];
			$this->xetconfig['lastmonth'] = $this->xetconfig['startmonth'];
			$this->xetconfig['lastyear'] = $this->xetconfig['startyear'];
		}
		$starttimestamp = $row['`Time`'];
		if (isset ($_POST['lastdate']) || isset ($_POST['lasttime'])) {
			$dateformatarr = explode(',', $this->xetconfig['date_format']);
			$datearr = explode($this->xetconfig['date_divider'], $_POST['lastdate']);
			$key = array_search('d', $dateformatarr);
			$this->xetconfig['lastday'] = $datearr[$key];
			$key = array_search('m', $dateformatarr);
			$this->xetconfig['lastmonth'] = $datearr[$key];
			$key = array_search('y', $dateformatarr);
			$this->xetconfig['lastyear'] = $datearr[$key];
			$timearr = explode(':', $_POST['lasttime']);
			$this->xetconfig['lasthour'] = $timearr[0];
			$this->xetconfig['lastminute'] = $timearr[1];
		}
		$lasttimestamp = xetadodb_mktime($this->xetconfig['lasthour'], $this->xetconfig['lastminute'], 0, $this->xetconfig['lastmonth'], $this->xetconfig['lastday'], $this->xetconfig['lastyear']);
		$adddays = 0;
		$thetime = $starttimestamp;
		$theDate = $thetime; while ($thetime <= $lasttimestamp) {
			$theday = xetadodb_strftime("%d", $theDate);
			$theyear = xetadodb_strftime("%Y", $theDate);
			$themonth = xetadodb_strftime("%m", $theDate);
			$lasttime = xetadodb_mktime($this->xetconfig['lasthour'], $this->xetconfig['lastminute'], 0, $themonth, $theday, $theyear);
			$i = 0;
			$thetime = $theDate; while ($thetime <= $lasttime) {
				$Time = $thetime;
				$fields = $row;
				$fields['`Time`'] = $Time;
				if (isset ($_POST['lenminutes']) || isset ($_POST['lenhours']) || isset ($_POST['lendays'])) {
					$datelen = $_POST['lenminutes'] * 60 + $_POST['lenhours'] * 3600 + $_POST['lendays'] * 86400;
					$fields['`Timeend`'] = $Time + $datelen;
				} else {
					$fields['`Timeend`'] = $row['`Timeend`'];
				}
				$this->dbinsert($fields);
				$i++;
				if (isset ($_POST['shiftminutes']) || isset ($_POST['shifthours']) || isset ($_POST['shiftdays'])) {
					$timeshift = $_POST['shiftminutes'] * 60 + $_POST['shifthours'] * 3600 + $_POST['shiftdays'] * 86400;
				} else {
					$timeshift = $this->xetconfig['timeshift'];
				}
				$addtime = $i * $timeshift;
				$thetime = $theDate + $addtime;
			}
			$adddays++;
			$theDate = $starttimestamp + $adddays * 86400;
		}

	}
	return;
}
}	
?>