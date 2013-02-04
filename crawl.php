<?php
    $schools = array(
    'stjan' => array(
    'full_name' => 'Sint-Jan',
    'town' => 'Hoensbroek',
    'website' => 'http://www.sintjan-lvo.nl/',
    'rooster_system' => 'untis2011',
    'rooster_classes' => 'http://www.carbooncollege.nl/carbooncollege/stjan/klassenrooster/Kla1a.htm',
    'rooster_url' => 'http://www.carbooncollege.nl/carbooncollege/stjan/klassenrooster/Kla1a_%class%.htm'
    ),
    'broekland' => array(
    'full_name' => 'Broekland College',
    'town' => 'Hoensbroek',
    'website' => 'http://www.broekland-lvo.nl/',
    'rooster_system' => 'untis2012',
    'rooster_classes' => 'http://www.carbooncollege.nl/carbooncollege/broekland/klassenrooster/Kla1A.htm',
    'rooster_url' => 'http://www.carbooncollege.nl/carbooncollege/broekland/klassenrooster/Kla1a_%class%.htm',
    'hasapp' => true
    ),
    'rombouts' => array(
    'full_name' => 'Rombouts',
    'town' => 'Brunssum',
    'website' => 'http://www.rombouts-lvo.nl/',
    'rooster_system' => 'untis2012-r1', // Revision 1 - multiple lessons at the same hour have different layout
    'rooster_classes' => 'http://www.carbooncollege.nl/carbooncollege/rombouts/klassenrooster/klas_1_Internet.htm',
    'rooster_url' => 'http://www.carbooncollege.nl/carbooncollege/rombouts/klassenrooster/klas_1_Internet_%class%.htm',
    'hasapp' => true
    ),
    'grotius' => array(
    'full_name' => 'Grotiuscollege',
    'town' => 'Heerlen',
    'website' => 'http://www.grotius-lvo.nl/',
    'rooster_system' => 'untis2012',
    'rooster_classes' => 'http://oud.grotius.nl/portals/0/html/lesroosters/klas/kla1a.htm',
    'rooster_url' => 'http://oud.grotius.nl/portals/0/html/lesroosters/klas/Kla1A_G%class%.htm',
    'hasapp' => true
    ),
    'sintermeerten1' => array(
    'full_name' => 'Sintermeerten onderbouw',
    'town' => 'Heerlen',
    'website' => 'http://www.sintermeerten.nl/',
    'rooster_system' => 'untis2011',
    'rooster_classes' => 'http://www.sintermeerten.nl/roosteronline/onderbouw.htm',
    'rooster_url' => 'http://www.sintermeerten.nl/roosteronline/onderbouw_%class%.htm'
    ),
    'sintermeerten2' => array(
    'full_name' => 'Sintermeerten bovenbouw',
    'town' => 'Heerlen',
    'website' => 'http://www.sintermeerten.nl/',
    'rooster_system' => 'untis2011',
    'rooster_classes' => 'http://www.sintermeerten.nl/roosteronline/bovenbouw.htm',
    'rooster_url' => 'http://www.sintermeerten.nl/roosteronline/bovenbouw_%class%.htm'
    ),
    'sintermeerten' => array(
    'full_name' => 'Sintermeerten',
    'town' => 'Heerlen',
    'website' => 'http://www.sintermeerten.nl/',
    'rooster_system' => 'untis2011',
    'rooster_classes' => array('http://www.sintermeerten.nl/roosteronline/onderbouw.htm','http://www.sintermeerten.nl/roosteronline/bovenbouw.htm'),
    'rooster_url' => 'http://www.sintermeerten.nl/roosteronline/onderbouw_%class%.htm'
    )
    );

    // Sintermeerten fix (selecting Sintermeerten OB or BB)
    if(@$_GET['school'] == 'sintermeerten' && @substr(@$_GET['class'],1,1) > 3){
        $schools['sintermeerten']['rooster_url'] = 'http://www.sintermeerten.nl/roosteronline/bovenbouw_%class%.htm';
    }

    function output_xml($array){
        foreach($array as $a => $b){
            if(is_array($b)){ $type = 'array'; }
            elseif(is_int($b)){ $type = 'integer'; }
            elseif(is_float($b)){ $type = 'float'; }
            elseif(is_string($b)){ $type = 'string'; }
            elseif(is_bool($b)){ $type = 'bool'; }
            elseif(empty($b)){ $type = 'empty'; }
            else{ $type='undefined'; }

            // XML fix: Tag can't start with number
            if(is_numeric(substr($a,0,1))){$a = 'array'; }

            echo '<'.$a.'>';
            if($type == 'array'){ output_xml($b); }
            elseif($type == 'bool'){ echo $b ? 'true' : 'false'; }
            else{ echo htmlentities($b); }
            echo '</'.$a.'>';
        }
    }

    if(!isset($_GET['school']) || !isset($schools[strtolower(@$_GET['school'])])){
        die('[err] School niet bekend.');
    }
    if(!isset($_GET['class'])){
        die('[err] Geef een klas op.');
    }

    if(!isset($_GET['format']) || !in_array(strtolower(@$_GET['format']),array('json','html','php','txt','xml'))){
        $_GET['format'] = 'json';
    }

    $school = $schools[$_GET['school']];

    if($_GET['class'] == 'classes'){
        $filename = 'temp/classes_'. strtolower(@$_GET['school']) . '_'. date('Y') . '.' . strtolower(@$_GET['format']);
        if(@filemtime($filename) > (time() - 31*2*86400) && !isset($_GET['reset'])){
            switch(strtolower(@$_GET['format'])){
                case 'json':
                    header('Content-type: application/json');
                    break;
                case 'xml':
                    header('Content-type: text/xml; charset=UTF-8');
                    break;       
            }
            $c = @file_get_contents($filename);
            echo $c; exit;
        }

        if(!is_array($school['rooster_classes'])){
            $school['rooster_classes'] = array($school['rooster_classes']);
        }

        $data = array(
        'timestamp' => time(),
        'expires' => time() + 31*2*86400,
        'date_readable' => date('d-m-Y H:i'),
        'school_name' => $school['full_name'],
        'school_town' => $school['town'],
        'sources' => $school['rooster_classes'],
        'classes' => array()
        );
        switch($school['rooster_system']){
            case 'untis2011':
            case 'untis2011-r1':
            case 'untis2012':
            case 'untis2012-r1':
                foreach($school['rooster_classes'] as $u){
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL,$u);
                    @curl_setopt($ch, CURLOPT_FAILONERROR, 1); 
                    @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                    @curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                    @curl_setopt($ch, CURLOPT_TIMEOUT, 8);
                    $cd = curl_exec($ch);
                    if(empty($cd)){ die('[err] Kon het klassenoverzicht niet ophalen. Server error.'); }
                    curl_close($ch);
                    $dom = new domDocument;
                    @$dom->loadHTML($cd);
                    $as = $dom->getElementsByTagName('a');
                    foreach($as as $a){
                    	if($a->nodeValue != 'Untis roostersoftware'){
                        	$data['classes'][] = $a->nodeValue;
                        }
                    }
                }
                break;
        }
    }else{
        $w = date('W');
        $filename = 'temp/rooster_'. strtolower(@$_GET['school']) . '_'. trim(@$_GET['class']) . '_' . date('W') . date('N') . '.' . strtolower(@$_GET['format']);
        $c = @file_get_contents($filename);
        if($c != '' && !isset($_GET['reset'])){
            // Rooster seems to be in cache. Let's show it and quit.
            switch(strtolower(@$_GET['format'])){
                case 'json':
                    header('Content-type: application/json');
                    if(isset($_GET['callback'])){
	                    $c = $_GET['callback'] . '(' . $c . ')';
	                }
                    break;
                case 'xml':
                    header('Content-type: text/xml; charset=UTF-8');
                    break;       
            }
            echo $c; exit;
        }

        $data = array(
       	  'timestamp' => time(),
          'expires' => time()+86400,
          'date_readable' => date('d-m-Y H:i'),
          'school_name' => $school['full_name'],
          'school_town' => $school['town'],
          'original_url' => str_replace('%class%',trim(@$_GET['class']),$school['rooster_url']),
          'rooster' => array()
        );

        switch($school['rooster_system']){
            case 'untis2011':
            case 'untis2011-r1':
            case 'untis2012':
            case 'untis2012-r1':
                //Yes, I know. You're using that German software, version 2011. And indeed, it looks like version 1995. Fail...
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL,str_replace('%class%',trim(@$_GET['class']),$school['rooster_url']));
                @curl_setopt($ch, CURLOPT_FAILONERROR, 1); 
                @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                @curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                @curl_setopt($ch, CURLOPT_TIMEOUT, 8);
                $cd = curl_exec($ch);
                if(empty($cd)){ die('[err] Kon het rooster niet ophalen. Server error.'); }
                curl_close($ch);
                $dom = new domDocument;
                @$dom->loadHTML($cd); 
                if($school['rooster_system'] == 'untis2012-r1' || $school['rooster_system'] == 'untis2012'){
                	$hours = $dom->getElementsByTagName('table')->item(1)->getElementsByTagName('tr'); 
                }else{
	                $hours = $dom->getElementsByTagName('table')->item(0)->getElementsByTagName('tr');
                }
                $hour = 0;
                foreach($hours as $h => $f){
                	if($h > 0 && @$f->getElementsByTagName('table')->item(0)){
                        $hour++;
                        $e = $f->getElementsByTagName('td');
                        $day = 0;
                        foreach($e as $d => $g){
                        	if($d > 0 && @$g->getElementsByTagName('table')->item(0)){
                                $day++;
                                $i = $g->getElementsByTagName('tr');
                                if($school['rooster_system'] == 'untis2011-r1' || $school['rooster_system'] == 'untis2012-r1'){
                                    $l = 0;
                                    $n = 0;
                                    $data['rooster'][$day][$hour][$l] = array();
                                    $k = $i->item(0)->getElementsByTagName('td');
                                    foreach($k as $m){
                                        $o = trim($m->nodeValue);
                                        if($n == 3){ $n = 0; $l++; }
                                        if($n == 0){ $data['rooster'][$day][$hour][$l]['subject'] = $o; }
                                        if($n == 1){ $data['rooster'][$day][$hour][$l]['teacher'] = $o; }
                                        if($n == 2){ $data['rooster'][$day][$hour][$l]['room'] = $o; }
                                        $n++;
                                    }   
                                }else{
                                    if(isset($data['rooster'][$day][$hour])){
                                        $day++;
                                    }
                                    foreach($i as $j){
                                        $k = $j->getElementsByTagName('td');
                                        $data['rooster'][$day][$hour][] = array(
                                        'subject' => trim($k->item(0)->nodeValue),
                                        'teacher' => trim($k->item(1)->nodeValue),
                                        'room' => trim($k->item(2)->nodeValue)
                                        );
                                    }

                                    $p = $g->getAttribute('rowspan')/2;
                                    if($p > 1){
                                        for($q=0;$q<$p;$q++){
                                            $data['rooster'][$day][($hour+$q)] = $data['rooster'][$day][$hour];
                                        }
                                        $day+$p;
                                    }

                                }
                            }
                        }
                    }
                }
                if($hour == 0){
	                die('[err] Er is geen rooster beschikbaar voor deze klas.');
                }
                break;
        }

    }
            
    switch(strtolower(@$_GET['format'])){
        case 'json':
            header('Content-type: application/json');
            $output = json_encode($data);
            break;
        case 'php':
            $output = serialize($data);
            break;
        case 'html':
        	header('Content-type: text/html; charset=UTF-8');
            $output = '<pre>'.print_r($data,true).'</pre>';
            break;
        case 'txt':
            $output = print_r($data,true);
            break; 
        case 'xml':
            header('Content-type: text/xml; charset=UTF-8');
            echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><result>';
            output_xml($data);
            echo '</result>';       
    }
    
    $outputraw = $output;
    if(isset($_GET['callback'])){
    	$output = $_GET['callback'] . '(' . $output . ')';
    }
            
    echo $output;
    @file_put_contents($filename,$outputraw);
    exit;
?>