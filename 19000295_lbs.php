###[DEF]###
[name		=openweather (v1.0)		]

[e#1     =Trigger            ]
[e#2     =APIKey #init=1a96b1bd6b0e1ab45340c10f17xxxxxx       ]
[e#3     =Ort   #init=Wien        ]
[e#4     =lang   #init=de        ]
[e#5     =untis   #init=metric        ]
[e#6     =Log level   #init=8       ]


[a#1		=Ort		]
[a#2		=##Heute##		]
[a#3		=Temperatur		]
[a#4		=max Temperatur		]
[a#5		=min Temperatur	]
[a#6		=Lufteuchte			]
[a#7		=Luftdruck]
[a#8		=Wind km/h			]
[a#9		=Wind m/s	]
[a#10	=Wind Description			]
[a#11	=Richtung in &deg;			]
[a#12	=Richtung kurz			]
[a#13	=Richtung Kurz + &deg; 		]
[a#14	=Bewölkung 		]
[a#15	=Bewölkung Text		]
[a#16	=Icon	]
[a#17	=Regenmenge	]
[a#18	=Niederschlagsart	]
[a#19	=Weather ID	]
[a#20	=Sunrise]
[a#21	=Sunset	]
[a#22	=last Update	]

   
[v#1		=1.0						] (Version)

###[/DEF]###

###[HELP]###
Installation of openweather
============================
### Login into your EDOMI server as root ###

cd ~
yum install -y git wget php-process php-xml
wget --no-check-certificate https://getcomposer.org/installer
php installer
mv composer.phar /usr/local/bin/composer
cd /usr/local/edomi/main/include/php
composer require "cmfcmf/openweathermap-php-api"


Weather Condition Codes:
http://bugs.openweathermap.org/projects/api/wiki/Weather_Condition_Codes

E1: = !=0 = Trigger
E2: = APIKey
E3: = Ort
E4: = lang   #init=de
E5: = untis   #init=metric 
E6: = Log level


[a#1		=Ort		]
[a#2		=##Heute##		]
[a#3		=Temperatur		]
[a#4		=max Temperatur		]
[a#5		=min Temperatur	]
[a#6		=Lufteuchte			]
[a#7		=Luftdruck]
[a#8		=Wind km/h			]
[a#9		=Wind m/s	]
[a#10	=Wind Description			]
[a#11	=Richtung in &deg;			]
[a#12	=Richtung kurz			]
[a#13	=Richtung Kurz + &deg; 		]
[a#14	=Bewölkung 		]
[a#15	=Bewölkung Text		]
[a#16	=Icon	]
[a#17	=Regenmenge	]
[a#18	=Niederschlagsart	]
[a#19	=Weather ID	]
[a#20	=Sunrise]
[a#21	=Sunset	]
[a#22	=last Update	]
   
###[/HELP]###


###[LBS]###
<?
function LB_LBSID($id) {
	if ($E=getLogicEingangDataAll($id)) {
		if ($E[1]['refresh']==1) {
			callLogicFunctionExec(LBSID,$id);
		}
	}
}
?>
###[/LBS]###


###[EXEC]###
<?
require(dirname(__FILE__)."/../../../../main/include/php/incl_lbsexec.php");
require(dirname(__FILE__)."/../../../../main/include/php/vendor/autoload.php");


use Cmfcmf\OpenWeatherMap;
use Cmfcmf\OpenWeatherMap\Exception as OWMException;

sql_connect();
//-------------------------------------------------------------------------------------
if ($E=getLogicEingangDataAll($id)) {
	$api = $E[2]['value'];
	$ort = $E[3]['value'];
	$lang = $E[4]['value'];
	$units = $E[5]['value'];
 }
if($E[6]['value']==1){debug($id,"openweather Exec -> gestartet");}
$owm = new OpenWeatherMap($api);


try {
    $weather = $owm->getWeather($ort, $units, $lang);
} catch(OWMException $e) {
			_log("openweather Exec -> OpenWeatherMap exception:  ". $e->getMessage() . "(Code)"  . $e->getCode());

} catch(\Exception $e) {
			_log("openweather Exec -> General exception:  ". $e->getMessage() . "(Code )" . $e->getCode());

}
	
	
            if (  isset($weather)) {
							setLogicLinkAusgang($id,1,$weather->city->name);
							setLogicLinkAusgang($id,3,$weather->temperature);
							$max=$weather->temperature->max->getvalue();
							setLogicLinkAusgang($id,4,round($max,0)."&deg;");
							$min=$weather->temperature->min->getvalue();
							setLogicLinkAusgang($id,5,round($min,0)."&deg;");
							setLogicLinkAusgang($id,6,$weather->humidity);
							$pressure=$weather->pressure->getvalue();
							setLogicLinkAusgang($id,7,number_format($pressure,0, ".","")." hPA");
							$wind=$weather->wind->speed->getvalue();
							setLogicLinkAusgang($id,8,($wind*3.6)." km/h");
							setLogicLinkAusgang($id,9,$weather->wind->speed);
							setLogicLinkAusgang($id,10,$weather->wind->speed->getDescription());
							$direction=$weather->wind->direction->getvalue();
							$direction=number_format($direction,0);
							$directiont=_txt($weather->wind->direction->getunit());
							setLogicLinkAusgang($id,11,$direction."&deg;");
							setLogicLinkAusgang($id,12,$directiont);
							setLogicLinkAusgang($id,13,$directiont." "."($direction&deg;)");
							setLogicLinkAusgang($id,14,$weather->clouds);
							setLogicLinkAusgang($id,15,$weather->weather);
							setLogicLinkAusgang($id,16,$weather->weather->icon);
							setLogicLinkAusgang($id,17,$weather->precipitation);
							setLogicLinkAusgang($id,18,$weather->precipitation->getDescription());
							setLogicLinkAusgang($id,19,$weather->weather->id);
							setLogicLinkAusgang($id,22,$weather->lastUpdate->setTimezone(new \DateTimezone('Europe/Berlin'))->format('d.m.Y H:i'));
							setLogicLinkAusgang($id,20,$weather->sun->rise->setTimezone(new \DateTimezone('Europe/Berlin'))->format("H:i:s"));
							setLogicLinkAusgang($id,21,$weather->sun->set->setTimezone(new \DateTimezone('Europe/Berlin'))->format("H:i:s"));
            				
			}

 setLogicElementStatus($id,0);
//-------------------------------------------------------------------------------------
sql_disconnect();
function _txt($name)
    {
        $ret = '';
        $txt['SSE'] = 'SSO';  
        $txt['SE'] = 'SO'; 
        $txt['S'] = 'S'; 
        $txt['N'] = 'N'; 
        $txt['W'] = 'W'; 
        $txt['E'] = 'O'; 
        $txt['SSW'] = 'SSW'; 
        $txt['SW'] = 'SW';
        $txt['ESE'] = 'OSO'; 
        $txt['ENE'] = 'ONO'; 
		$txt['NE'] = 'NO'; 
		$txt['NNE'] = 'NNO'; 
		$txt['NNW'] = 'NNW'; 
		$txt['NW'] = 'NW'; 
		$txt['WNW'] = 'WNW'; 
		$txt['WSW'] = 'WSW'; 
		$txt[''] = '';
        
        

        $ret = $txt[$name];

        return $ret;
    }
function _log($msg, $priority=8)
{
global $id;
$E=getLogicEingangDataAll($id);
$logLevel = getLogicElementVar($id,103);
if (is_int($priority) && $priority<=$logLevel && $priority>0)
{
$logLevelNames = array('none','emerg','alert','crit','err','warning','notice','info','debug');
$version = getLogicElementVar($id,100);
$lbsNo = getLogicElementVar($id,101);
$logName = getLogicElementVar($id,102) . ' --- LBS'.$lbsNo;
strpos($_SERVER['SCRIPT_NAME'],$lbsNo) ? $scriptname='EXE'.$lbsNo : $scriptname = 'LBS'.$lbsNo;
writeToCustomLog($logName,str_pad($logLevelNames[$logLevel],7), $scriptname.":\t".$msg."\t[v$version]");
}
}


?>
###[/EXEC]###
