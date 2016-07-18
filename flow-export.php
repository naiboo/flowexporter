<?php
$precent=$tcx=$zip='';
set_time_limit(600);
$mtime = microtime(); 
$mtime = explode(" ",$mtime); 
$mtime = $mtime[1] + $mtime[0]; 
$starttime = $mtime; 
$dir = '/home/pi/flow/flow-tcx';

// flow user and password
$_SESSION['$flowlogin'] = 'flowuser@abc.de';
$_SESSION['$flowpwd'] =  'flowpwd';
//$_SESSION['$datefrom'] = '01.01.2012';
$_SESSION['$datefrom'] = date("d.m.Y", strtotime( '-2 days' ));
$_SESSION['$dateto'] = date("d.m.Y");

$_SESSION['$filetype'] = "zip";
$_SESSION['$zip'] = "Y";

$post_fields = 'returnUrl=https%3A%2F%2Fflow.polar.com%2F&email=' . $_SESSION['$flowlogin'] . '&password=' . $_SESSION['$flowpwd'];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://flow.polar.com/ajaxLogin');
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cook');
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cook');
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$arr = curl_exec($ch); //get login page

curl_setopt($ch, CURLOPT_URL, 'https://flow.polar.com/login');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);

$arr = curl_exec($ch); //post credentials

curl_setopt($ch, CURLOPT_URL, 'https://flow.polar.com/training/getCalendarEvents?start=' . $_SESSION['$datefrom'] . '&end=' . $_SESSION['$dateto']);
curl_setopt($ch, CURLOPT_POST, 0);

$arr = curl_exec($ch); //get activity list

$activity_arr = json_decode($arr);
$counter = count($activity_arr); 
//print_r($activity_arr);

if ($counter > 0){

echo $counter." trainings-fitness data found between ".$_SESSION['$datefrom']." and ".$_SESSION['$dateto']."\n";
$total = $counter;
$counter=0;

foreach ($activity_arr as $activity) {                                                                                                                                                                                                                                                                                                                                                    
        if ($activity->type == 'EXERCISE') { 

	 //echo $activity->type." ".$activity->listItemId." ".$activity->iconUrl." ".$activity->datetime."\n";
	 $counter++;
}
}

$tz_fix_offset = '-02:00';
$count = 1;
                                                                                                                                                                                              
foreach ($activity_arr as $activity) {                                                                                                                                                        
  if ($activity->type == 'EXERCISE') {   
    if ( $_SESSION['$filetype'] == 'tcx' ){ 
		  // Export pure tcx files       
		  $tcxurl = 'https://flow.polar.com' . $activity->url . '/export/tcx/false';  
		  $tcxname =  $dir.'/'.$_SESSION['$flowlogin'].'-'. $activity->datetime . '.tcx';	 
	  }
	  if ( $_SESSION['$filetype'] == 'zip' ){ 	
		  // Export Zipped files                                                                                                                                                                                     
      $tcxurl = 'https://flow.polar.com' . $activity->url . '/export/tcx/true';   
		  $tcxname = $dir.'/'.$_SESSION['$flowlogin'].'-'. $activity->datetime . '.zip';                                                                                                          
    }      
	                                                                                                                                                                                 
  //   echo 'Fetching ' . $tcxurl . "...";                                                                                                                                                   
  curl_setopt($ch, CURLOPT_URL, $tcxurl);                                                                                                                                               
  $tcx = curl_exec($ch);                                                                                                                                                                  
  $tcxname =  $dir.'/'.$_SESSION['$flowlogin'].'-'. $activity->datetime . '.zip';                                                                                                                     
  $fixedtcx = str_replace('000Z', '000' . $tz_fix_offset, $tcx);                                                                                                                        
  file_put_contents($tcxname, $tcx);        

  // unZip flow file
  if ($_SESSION['$zip'] == 'Y')
	  {
	  	$zip = new ZipArchive;
     	$res = $zip->open($dir.'/'.$_SESSION['$flowlogin'].'-'. $activity->datetime . '.zip');
      if ($res === TRUE) {
        $zip->extractTo($dir.'/');
        $zip->close();
		  }
		  unlink($dir.'/'.$_SESSION['$flowlogin'].'-'. $activity->datetime . '.zip');
	  }
	 echo "https://flow.polar.com/training/analysis/".$activity->listItemId." exported\n";                                                                                                                                                                          
   $count++;
	}                                                                                                                                                                                                                        
}
}
?>
