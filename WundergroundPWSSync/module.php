<?php

if (!defined('vtBoolean')) {
    define('vtBoolean', 0);
    define('vtInteger', 1);
    define('vtFloat', 2);
    define('vtString', 3);
    define('vtArray', 8);
    define('vtObject', 9);
}


	class WundergroundPWSSync extends IPSModule
	{

		public function Create()
		{
			//Never delete this line!
			parent::Create();
			$this->RegisterPropertyInteger("SourceID", 0);
			$this->RegisterPropertyString("WU_ID", "");
			$this->RegisterPropertyString("WU_Password","");
			$this->RegisterPropertyString("WU_API","");
			$this->RegisterPropertyString("WU_StationKey","");
			$this->RegisterPropertyString("Mode","U");
			$this->RegisterPropertyString("Language","de-de");
			//$this->RegisterPropertyString("Latitude","");
			//$this->RegisterPropertyString("Longitude","");
			//$this->RegisterPropertyString("Location", '{"latitude":$this->RegisterPropertyString("Latitude"),"longitude":$this->RegisterPropertyString("Longitude")}');
			$this->RegisterPropertyString("Location", '{"latitude":0,"longitude":0}');
			$this->RegisterPropertyInteger("ForecastShort","0");
			$this->RegisterPropertyBoolean("CalculateUpcomingRain","0");
			$this->RegisterPropertyInteger("ForecastDP","0");
			$this->RegisterPropertyInteger("ForecastInterval",12);
			$this->RegisterPropertyBoolean("ForecastDPTemperature","0");
			$this->RegisterPropertyBoolean("ForecastDPRain","0");
			$this->RegisterPropertyBoolean("ForecastDPNarrative","0");
			$this->RegisterPropertyBoolean("ForecastDPWind","0");
			$this->RegisterPropertyBoolean("ForecastDPCloudCover", 0);
			$this->RegisterPropertyBoolean("ForecastDPUV","0");
			$this->RegisterPropertyBoolean("ForecastDPThunder","0");
			$this->RegisterPropertyBoolean("ForecastDPIcon","0");
			$this->RegisterPropertyBoolean("JSONRawForecast","0");
			$this->RegisterPropertyInteger("OutsideTemperature", 0);
			$this->RegisterPropertyInteger("Humidity", 0);
			$this->RegisterPropertyInteger("DewPoint", 0);
			$this->RegisterPropertyInteger("WindDirection", 0);
			$this->RegisterPropertyInteger("WindSpeed", 0);
			$this->RegisterPropertyInteger("WindGust", 0);
			$this->RegisterPropertyInteger("Rain_last_Hour", 0);
			$this->RegisterPropertyInteger("Rain24h", 0);
			$this->RegisterPropertyInteger("AirPressure", 0);
			$this->RegisterPropertyInteger("UVIndex", 0);
			$this->RegisterPropertyString("WindConversion", "ms");
			$this->RegisterPropertyInteger("Timer", 0);
			$this->RegisterPropertyString("DLT_WU_ID", "");
			$this->RegisterPropertyBoolean("DLTemperature","0");
			$this->RegisterPropertyBoolean("DLSolarRadiation","0");
			$this->RegisterPropertyBoolean("DLUV","0");
			$this->RegisterPropertyBoolean("DLWindDirection","0");
			$this->RegisterPropertyBoolean("DLHumidity","0");
			$this->RegisterPropertyBoolean("DLDewPT","0");
			$this->RegisterPropertyBoolean("DLWindchill","0");
			$this->RegisterPropertyBoolean("DLWindSpeed","0");
			$this->RegisterPropertyBoolean("DLWindGust","0");
			$this->RegisterPropertyBoolean("DLPressure","0");
			$this->RegisterPropertyBoolean("DLRainRate","0");
			$this->RegisterPropertyBoolean("DLRainTotal","0");
			$this->RegisterPropertyBoolean("JSONRawStation","0");
			$this->RegisterPropertyInteger("DLTimer", 0);
			$this->RegisterPropertyBoolean("Debug", 0);

			//Component sets timer, but default is OFF
			$this->RegisterTimer("ForecastTimer",0,"WUPWSS_Forecast(\$_IPS['TARGET']);");
			$this->RegisterTimer("UpdateTimer",0,"WUPWSS_UploadToWunderground(\$_IPS['TARGET']);");
			$this->RegisterTimer("PWSDownloadTimer",0,"WUPWSS_CurrentPWSData(\$_IPS['TARGET']);");


		}

		public function ApplyChanges() {

			//Never delete this line!
			parent::ApplyChanges();


		        //Timer Update - if greater than 0 = On


				//Timer for Data Upload
				$TimerMS = $this->ReadPropertyInteger("Timer") * 1000;

        		$this->SetTimerInterval("UpdateTimer",$TimerMS);

				//Timer for Data Download
				$TimerMSDL = $this->ReadPropertyInteger("DLTimer") * 1000 * 60;

        		$this->SetTimerInterval("PWSDownloadTimer",$TimerMSDL);

				//Timer for Forecast
				$ForecastInterval = $this->ReadPropertyInteger("ForecastInterval") * 1000 * 3600;

        		$this->SetTimerInterval("ForecastTimer",$ForecastInterval);


				$vpos = 1;

				//Statics Timer Creation - On - Off

				$sourceID = $this->ReadPropertyInteger("SourceID");
				$ForecastInterval = $this->ReadPropertyInteger("ForecastInterval");

				//Löscht alten externen Timer mit Namen Forecast falls vorhanden
				$eid = @IPS_GetObjectIDByIdent("Forecast", $this->InstanceID);
				if ($eid == 1) {
					$eid = IPS_DeleteEvent(1);
				}

				//Variablen anlegen


				$vpos = 10;

				$this->MaintainVariable('DP0DN', $this->Translate('Daypart 0 (Current 12h) Day or Night'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 0);
				$this->MaintainVariable('DP0Name', $this->Translate('Daypart 0 (Current 12h) Part of day'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 0);
				$this->MaintainVariable('DP0Narrative', $this->Translate('Daypart 0 (Current 12h) Weather Forecast'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 0);
				$this->MaintainVariable('DP0PrecipChance', $this->Translate('Daypart 0 (Current 12h) Precip Chance'), vtInteger, "~Humidity", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 0);
				$this->MaintainVariable('DP0PrecipType', $this->Translate('Daypart 0 (Current 12h) Precip Type'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 0);
				$this->MaintainVariable('DP0CloudCover', $this->Translate('Daypart 0 (Current 12h) Cloud Cover'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 0 and $this->ReadPropertyBoolean("ForecastDPCloudCover") == "1");
				$this->MaintainVariable('DP0QPF', $this->Translate('Daypart 0 (Current 12h) Precipitation Liquid'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 0 and $this->ReadPropertyBoolean("ForecastDPRain") == "1");
				$this->MaintainVariable('DP0QPFSNOW', $this->Translate('Daypart 0 (Current 12h) Precipitation Snow'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 0 and $this->ReadPropertyBoolean("ForecastDPRain") == "1");
				$this->MaintainVariable('DP0Temperature', $this->Translate('Daypart 0 (Current 12h) Temperature'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 0 and $this->ReadPropertyBoolean("ForecastDPTemperature") == "1");
				$this->MaintainVariable('DP0WindChill', $this->Translate('Daypart 0 (Current 12h) Wind Chill'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 0 and $this->ReadPropertyBoolean("ForecastDPTemperature") == "1");
				$this->MaintainVariable('DP0Thunder', $this->Translate('Daypart 0 (Current 12h) Thunder'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 0 and $this->ReadPropertyBoolean("ForecastDPThunder") == "1");
				$this->MaintainVariable('DP0UVDescription', $this->Translate('Daypart 0 (Current 12h) UV Description'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 0 and $this->ReadPropertyBoolean("ForecastDPUV") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP0UVIndex', $this->Translate('Daypart 0 (Current 12h) UV Index'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 0 and $this->ReadPropertyBoolean("ForecastDPUV") == "1");
				$this->MaintainVariable('DP0WINDDIR', $this->Translate('Daypart 0 (Current 12h) Wind Direction'), vtFloat, "~WindDirection.F", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 0 and $this->ReadPropertyBoolean("ForecastDPWind") == "1");
				$this->MaintainVariable('DP0WINDDIRText', $this->Translate('Daypart 0 (Current 12h) Wind Direction Text'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 0 and $this->ReadPropertyBoolean("ForecastDPWind") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP0WINDDIRPhrase', $this->Translate('Daypart 0 (Current 12h) Wind Phrase'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 0 and $this->ReadPropertyBoolean("ForecastDPWind") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP0WINDSpeed', $this->Translate('Daypart 0 (Current 12h) Wind Speed'), vtFloat, "~WindSpeed.kmh", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 0 and $this->ReadPropertyBoolean("ForecastDPWind") == "1");
				$this->MaintainVariable('DP0Icon', $this->Translate('Daypart 0 (Current 12h) Icon'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 0 and $this->ReadPropertyBoolean("ForecastDPIcon") == "1");

				$vpos = 50;

				$this->MaintainVariable('DP1DN', $this->Translate('Daypart 1 (Next 12h) Day or Night'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 1);
				$this->MaintainVariable('DP1Name', $this->Translate('Daypart 1 (Next 12h) Part of day'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 1);
				$this->MaintainVariable('DP1Narrative', $this->Translate('Daypart 1 (Next 12h) Weather Forecast'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 1);
				$this->MaintainVariable('DP1PrecipChance', $this->Translate('Daypart 1 (Next 12h) Precip Chance'), vtInteger, "~Humidity", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 1);
				$this->MaintainVariable('DP1PrecipType', $this->Translate('Daypart 1 (Next 12h) Precip Type'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 1);
				$this->MaintainVariable('DP1CloudCover', $this->Translate('Daypart 1 (Next 12h) Cloud Cover'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 1 AND $this->ReadPropertyBoolean("ForecastDPCloudCover") == "1");
				$this->MaintainVariable('DP1QPF', $this->Translate('Daypart 1 (Next 12h) Precipitation Liquid'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 1 AND $this->ReadPropertyBoolean("ForecastDPRain") == "1");
				$this->MaintainVariable('DP1QPFSNOW', $this->Translate('Daypart 1 (Next 12h) Precipitation Snow'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 1 AND $this->ReadPropertyBoolean("ForecastDPRain") == "1");
				$this->MaintainVariable('DP1Temperature', $this->Translate('Daypart 1 (Next 12h) Temperature'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 1 AND $this->ReadPropertyBoolean("ForecastDPTemperature") == "1");
				$this->MaintainVariable('DP1WindChill', $this->Translate('Daypart 1 (Next 12h) Wind Chill'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 1 AND $this->ReadPropertyBoolean("ForecastDPTemperature") == "1");
				$this->MaintainVariable('DP1Thunder', $this->Translate('Daypart 1 (Next 12h) Thunder'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 1 AND $this->ReadPropertyBoolean("ForecastDPThunder") == "1");
				$this->MaintainVariable('DP1UVDescription', $this->Translate('Daypart 1 (Next 12h) UV Description'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 1 AND $this->ReadPropertyBoolean("ForecastDPUV") == "1" AND $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP1UVIndex', $this->Translate('Daypart 1 (Next 12h) UV Index'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 1 AND $this->ReadPropertyBoolean("ForecastDPUV") == "1");
				$this->MaintainVariable('DP1WINDDIR', $this->Translate('Daypart 1 (Next 12h) Wind Direction'), vtFloat, "~WindDirection.F", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 1 AND $this->ReadPropertyBoolean("ForecastDPWind") == "1");
				$this->MaintainVariable('DP1WINDDIRText', $this->Translate('Daypart 1 (Next 12h) Wind Direction Text'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 1 AND $this->ReadPropertyBoolean("ForecastDPWind") == "1" AND $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP1WINDDIRPhrase', $this->Translate('Daypart 1 (Next 12h) Wind Phrase'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 1 AND $this->ReadPropertyBoolean("ForecastDPWind") == "1" AND $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP1WINDSpeed', $this->Translate('Daypart 1 (Next 12h) Wind Speed'), vtFloat, "~WindSpeed.kmh", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 1 AND $this->ReadPropertyBoolean("ForecastDPWind") == "1");
				$this->MaintainVariable('DP1Icon', $this->Translate('Daypart 1 (Next 12h) Icon'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 1 AND $this->ReadPropertyBoolean("ForecastDPIcon") == "1");

				$vpos = 100;

				$this->MaintainVariable('DP2DN',
					$this->Translate('Daypart 2 - Day or Night'),
					vtString,
					"",
					$vpos++,
					$this->ReadPropertyInteger("ForecastDP") > 2
				);
				$this->MaintainVariable('DP2Name', $this->Translate('Daypart 2 - Part of day'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 2);
				$this->MaintainVariable('DP2Narrative', $this->Translate('Daypart 2 - Weather Forecast'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 2);
				$this->MaintainVariable('DP2PrecipChance', $this->Translate('Daypart 2 - Precip Chance'), vtInteger, "~Humidity", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 2);
				$this->MaintainVariable('DP2PrecipType', $this->Translate('Daypart 2 - Precip Type'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 2);
				$this->MaintainVariable('DP2CloudCover', $this->Translate('Daypart 2 - Cloud Cover'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 2 and $this->ReadPropertyBoolean("ForecastDPCloudCover") == "1");
				$this->MaintainVariable('DP2QPF', $this->Translate('Daypart 2 - Precipitation Liquid'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 2 and $this->ReadPropertyBoolean("ForecastDPRain") == "1");
				$this->MaintainVariable('DP2QPFSNOW', $this->Translate('Daypart 2 - Precipitation Snow'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 2 and $this->ReadPropertyBoolean("ForecastDPRain") == "1");
				$this->MaintainVariable('DP2Temperature', $this->Translate('Daypart 2 - Temperature'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 2 and $this->ReadPropertyBoolean("ForecastDPTemperature") == "1");
				$this->MaintainVariable('DP2WindChill', $this->Translate('Daypart 2 - Wind Chill'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 2 and $this->ReadPropertyBoolean("ForecastDPTemperature") == "1");
				$this->MaintainVariable('DP2Thunder', $this->Translate('Daypart 2 - Thunder'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 2 and $this->ReadPropertyBoolean("ForecastDPThunder") == "1");
				$this->MaintainVariable('DP2UVDescription', $this->Translate('Daypart 2 - UV Description'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 2 and $this->ReadPropertyBoolean("ForecastDPUV") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP2UVIndex', $this->Translate('Daypart 2 - UV Index'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 2 and $this->ReadPropertyBoolean("ForecastDPUV") == "1");
				$this->MaintainVariable('DP2WINDDIR', $this->Translate('Daypart 2 - Wind Direction'), vtFloat, "~WindDirection.F", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 2 and $this->ReadPropertyBoolean("ForecastDPWind") == "1");
				$this->MaintainVariable('DP2WINDDIRText', $this->Translate('Daypart 2 - Wind Direction Text'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 2 and $this->ReadPropertyBoolean("ForecastDPWind") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP2WINDDIRPhrase', $this->Translate('Daypart 2 - Wind Phrase'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 2 and $this->ReadPropertyBoolean("ForecastDPWind") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP2WINDSpeed', $this->Translate('Daypart 2 - Wind Speed'), vtFloat, "~WindSpeed.kmh", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 2 and $this->ReadPropertyBoolean("ForecastDPWind") == "1");
				$this->MaintainVariable('DP2Icon', $this->Translate('Daypart 2 - Icon'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 2 and $this->ReadPropertyBoolean("ForecastDPIcon") == "1");

				$vpos = 150;

				$this->MaintainVariable('DP3DN',
					$this->Translate('Daypart 3 - Day or Night'),
					vtString,
					"",
					$vpos++,
					$this->ReadPropertyInteger("ForecastDP") > 3
				);
				$this->MaintainVariable('DP3Name', $this->Translate('Daypart 3 - Part of day'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 3);
				$this->MaintainVariable('DP3Narrative', $this->Translate('Daypart 3 - Weather Forecast'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 3);
				$this->MaintainVariable('DP3PrecipChance', $this->Translate('Daypart 3 - Precip Chance'), vtInteger, "~Humidity", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 3);
				$this->MaintainVariable('DP3PrecipType', $this->Translate('Daypart 3 - Precip Type'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 3);
				$this->MaintainVariable('DP3CloudCover', $this->Translate('Daypart 3 - Cloud Cover'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 0 and $this->ReadPropertyBoolean("ForecastDPCloudCover") == "1");
				$this->MaintainVariable('DP3QPF', $this->Translate('Daypart 3 - Precipitation Liquid'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 3 and $this->ReadPropertyBoolean("ForecastDPRain") == "1");
				$this->MaintainVariable('DP3QPFSNOW', $this->Translate('Daypart 3 - Precipitation Snow'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 3 and $this->ReadPropertyBoolean("ForecastDPRain") == "1");
				$this->MaintainVariable('DP3Temperature', $this->Translate('Daypart 3 - Temperature'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 3 and $this->ReadPropertyBoolean("ForecastDPTemperature") == "1");
				$this->MaintainVariable('DP3WindChill', $this->Translate('Daypart 3 - Wind Chill'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 3 and $this->ReadPropertyBoolean("ForecastDPTemperature") == "1");
				$this->MaintainVariable('DP3Thunder', $this->Translate('Daypart 3 - Thunder'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 3 and $this->ReadPropertyBoolean("ForecastDPThunder") == "1");
				$this->MaintainVariable('DP3UVDescription', $this->Translate('Daypart 3 - UV Description'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 3 and $this->ReadPropertyBoolean("ForecastDPUV") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP3UVIndex', $this->Translate('Daypart 3 - UV Index'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 3 and $this->ReadPropertyBoolean("ForecastDPUV") == "1");
				$this->MaintainVariable('DP3WINDDIR', $this->Translate('Daypart 3 - Wind Direction'), vtFloat, "~WindDirection.F", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 3 and $this->ReadPropertyBoolean("ForecastDPWind") == "1");
				$this->MaintainVariable('DP3WINDDIRText', $this->Translate('Daypart 3 - Wind Direction Text'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 3 and $this->ReadPropertyBoolean("ForecastDPWind") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP3WINDDIRPhrase', $this->Translate('Daypart 3 - Wind Phrase'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 3 and $this->ReadPropertyBoolean("ForecastDPWind") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP3WINDSpeed', $this->Translate('Daypart 3 - Wind Speed'), vtFloat, "~WindSpeed.kmh", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 3 and $this->ReadPropertyBoolean("ForecastDPWind") == "1");
				$this->MaintainVariable('DP3Icon', $this->Translate('Daypart 3 - Icon'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 3 and $this->ReadPropertyBoolean("ForecastDPIcon") == "1");

				$vpos = 200;

				$this->MaintainVariable('DP4DN',
					$this->Translate('Daypart 4 - Day or Night'),
					vtString,
					"",
					$vpos++,
					$this->ReadPropertyInteger("ForecastDP") > 4
				);
				$this->MaintainVariable('DP4Name', $this->Translate('Daypart 4 - Part of day'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 4);
				$this->MaintainVariable('DP4Narrative', $this->Translate('Daypart 4 - Weather Forecast'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 4);
				$this->MaintainVariable('DP4PrecipChance', $this->Translate('Daypart 4 - Precip Chance'), vtInteger, "~Humidity", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 4);
				$this->MaintainVariable('DP4PrecipType', $this->Translate('Daypart 4 - Precip Type'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 4);
				$this->MaintainVariable('DP4CloudCover', $this->Translate('Daypart 4 - Cloud Cover'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 4 and $this->ReadPropertyBoolean("ForecastDPCloudCover") == "1");
				$this->MaintainVariable('DP4QPF', $this->Translate('Daypart 4 - Precipitation Liquid'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 4 and $this->ReadPropertyBoolean("ForecastDPRain") == "1");
				$this->MaintainVariable('DP4QPFSNOW', $this->Translate('Daypart 4 - Precipitation Snow'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 4 and $this->ReadPropertyBoolean("ForecastDPRain") == "1");
				$this->MaintainVariable('DP4Temperature', $this->Translate('Daypart 4 - Temperature'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 4 and $this->ReadPropertyBoolean("ForecastDPTemperature") == "1");
				$this->MaintainVariable('DP4WindChill', $this->Translate('Daypart 4 - Wind Chill'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 4 and $this->ReadPropertyBoolean("ForecastDPTemperature") == "1");
				$this->MaintainVariable('DP4Thunder', $this->Translate('Daypart 4 - Thunder'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 4 and $this->ReadPropertyBoolean("ForecastDPThunder") == "1");
				$this->MaintainVariable('DP4UVDescription', $this->Translate('Daypart 4 - UV Description'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 4 and $this->ReadPropertyBoolean("ForecastDPUV") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP4UVIndex', $this->Translate('Daypart 4 - UV Index'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 4 and $this->ReadPropertyBoolean("ForecastDPUV") == "1");
				$this->MaintainVariable('DP4WINDDIR', $this->Translate('Daypart 4 - Wind Direction'), vtFloat, "~WindDirection.F", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 4 and $this->ReadPropertyBoolean("ForecastDPWind") == "1");
				$this->MaintainVariable('DP4WINDDIRText', $this->Translate('Daypart 4 - Wind Direction Text'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 4 and $this->ReadPropertyBoolean("ForecastDPWind") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP4WINDDIRPhrase', $this->Translate('Daypart 4 - Wind Phrase'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 4 and $this->ReadPropertyBoolean("ForecastDPWind") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP4WINDSpeed', $this->Translate('Daypart 4 - Wind Speed'), vtFloat, "~WindSpeed.kmh", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 4 and $this->ReadPropertyBoolean("ForecastDPWind") == "1");
				$this->MaintainVariable('DP4Icon', $this->Translate('Daypart 4 - Icon'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 4 and $this->ReadPropertyBoolean("ForecastDPIcon") == "1");

				$vpos = 250;

				$this->MaintainVariable('DP5DN',
					$this->Translate('Daypart 5 - Day or Night'),
					vtString,
					"",
					$vpos++,
					$this->ReadPropertyInteger("ForecastDP") > 5
				);
				$this->MaintainVariable('DP5Name', $this->Translate('Daypart 5 - Part of day'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 5);
				$this->MaintainVariable('DP5Narrative', $this->Translate('Daypart 5 - Weather Forecast'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 5);
				$this->MaintainVariable('DP5PrecipChance', $this->Translate('Daypart 5 - Precip Chance'), vtInteger, "~Humidity", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 5);
				$this->MaintainVariable('DP5PrecipType', $this->Translate('Daypart 5 - Precip Type'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 5);
				$this->MaintainVariable('DP5CloudCover', $this->Translate('Daypart 5 - Cloud Cover'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 5 and $this->ReadPropertyBoolean("ForecastDPCloudCover") == "1");
				$this->MaintainVariable('DP5QPF', $this->Translate('Daypart 5 - Precipitation Liquid'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 5 and $this->ReadPropertyBoolean("ForecastDPRain") == "1");
				$this->MaintainVariable('DP5QPFSNOW', $this->Translate('Daypart 5 - Precipitation Snow'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 5 and $this->ReadPropertyBoolean("ForecastDPRain") == "1");
				$this->MaintainVariable('DP5Temperature', $this->Translate('Daypart 5 - Temperature'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 5 and $this->ReadPropertyBoolean("ForecastDPTemperature") == "1");
				$this->MaintainVariable('DP5WindChill', $this->Translate('Daypart 5 - Wind Chill'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 5 and $this->ReadPropertyBoolean("ForecastDPTemperature") == "1");
				$this->MaintainVariable('DP5Thunder', $this->Translate('Daypart 5 - Thunder'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 5 and $this->ReadPropertyBoolean("ForecastDPThunder") == "1");
				$this->MaintainVariable('DP5UVDescription', $this->Translate('Daypart 5 - UV Description'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 5 and $this->ReadPropertyBoolean("ForecastDPUV") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP5UVIndex', $this->Translate('Daypart 5 - UV Index'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 5 and $this->ReadPropertyBoolean("ForecastDPUV") == "1");
				$this->MaintainVariable('DP5WINDDIR', $this->Translate('Daypart 5 - Wind Direction'), vtFloat, "~WindDirection.F", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 5 and $this->ReadPropertyBoolean("ForecastDPWind") == "1");
				$this->MaintainVariable('DP5WINDDIRText', $this->Translate('Daypart 5 - Wind Direction Text'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 5 and $this->ReadPropertyBoolean("ForecastDPWind") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP5WINDDIRPhrase', $this->Translate('Daypart 5 - Wind Phrase'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 5 and $this->ReadPropertyBoolean("ForecastDPWind") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP5WINDSpeed', $this->Translate('Daypart 5 - Wind Speed'), vtFloat, "~WindSpeed.kmh", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 5 and $this->ReadPropertyBoolean("ForecastDPWind") == "1");
				$this->MaintainVariable('DP5Icon', $this->Translate('Daypart 5 - Icon'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 2 and $this->ReadPropertyBoolean("ForecastDPIcon") == "1");

				$vpos = 300;

				$this->MaintainVariable('DP6DN',
					$this->Translate('Daypart 6 - Day or Night'),
					vtString,
					"",
					$vpos++,
					$this->ReadPropertyInteger("ForecastDP") > 6
				);
				$this->MaintainVariable('DP6Name', $this->Translate('Daypart 6 - Part of day'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 6);
				$this->MaintainVariable('DP6Narrative', $this->Translate('Daypart 6 - Weather Forecast'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 6);
				$this->MaintainVariable('DP6PrecipChance', $this->Translate('Daypart 6 - Precip Chance'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 6);
				$this->MaintainVariable('DP6PrecipType', $this->Translate('Daypart 6 - Precip Type'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 6);
				$this->MaintainVariable('DP6CloudCover', $this->Translate('Daypart 6 - Cloud Cover'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 6 and $this->ReadPropertyBoolean("ForecastDPCloudCover") == "1");
				$this->MaintainVariable('DP6QPF', $this->Translate('Daypart 6 - Precipitation Liquid'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 6 and $this->ReadPropertyBoolean("ForecastDPRain") == "1");
				$this->MaintainVariable('DP6QPFSNOW', $this->Translate('Daypart 6 - Precipitation Snow'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 6 and $this->ReadPropertyBoolean("ForecastDPRain") == "1");
				$this->MaintainVariable('DP6Temperature', $this->Translate('Daypart 6 - Temperature'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 6 and $this->ReadPropertyBoolean("ForecastDPTemperature") == "1");
				$this->MaintainVariable('DP6WindChill', $this->Translate('Daypart 6 - Wind Chill'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 6 and $this->ReadPropertyBoolean("ForecastDPTemperature") == "1");
				$this->MaintainVariable('DP6Thunder', $this->Translate('Daypart 6 - Thunder'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 6 and $this->ReadPropertyBoolean("ForecastDPThunder") == "1");
				$this->MaintainVariable('DP6UVDescription', $this->Translate('Daypart 6 - UV Description'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 6 and $this->ReadPropertyBoolean("ForecastDPUV") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP6UVIndex', $this->Translate('Daypart 6 - UV Index'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 6 and $this->ReadPropertyBoolean("ForecastDPUV") == "1");
				$this->MaintainVariable('DP6WINDDIR', $this->Translate('Daypart 6 - Wind Direction'), vtFloat, "~WindDirection.F", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 6 and $this->ReadPropertyBoolean("ForecastDPWind") == "1");
				$this->MaintainVariable('DP6WINDDIRText', $this->Translate('Daypart 6 - Wind Direction Text'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 6 and $this->ReadPropertyBoolean("ForecastDPWind") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP6WINDDIRPhrase', $this->Translate('Daypart 6 - Wind Phrase'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 6 and $this->ReadPropertyBoolean("ForecastDPWind") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP6WINDSpeed', $this->Translate('Daypart 6 - Wind Speed'), vtFloat, "~WindSpeed.kmh", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 6 and $this->ReadPropertyBoolean("ForecastDPWind") == "1");
				$this->MaintainVariable('DP6Icon', $this->Translate('Daypart 6 - Icon'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 6 and $this->ReadPropertyBoolean("ForecastDPIcon") == "1");

				$vpos = 350;

				$this->MaintainVariable('DP7DN',
					$this->Translate('Daypart 7 - Day or Night'),
					vtString,
					"",
					$vpos++,
					$this->ReadPropertyInteger("ForecastDP") > 7
				);
				$this->MaintainVariable('DP7Name', $this->Translate('Daypart 7 - Part of day'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 7);
				$this->MaintainVariable('DP7Narrative', $this->Translate('Daypart 7 - Weather Forecast'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 7);
				$this->MaintainVariable('DP7PrecipChance', $this->Translate('Daypart 7 - Precip Chance'), vtInteger, "~Humidity", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 7);
				$this->MaintainVariable('DP7PrecipType', $this->Translate('Daypart 7 - Precip Type'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 7);
				$this->MaintainVariable('DP7CloudCover', $this->Translate('Daypart 7 - Cloud Cover'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 7 and $this->ReadPropertyBoolean("ForecastDPCloudCover") == "1");
				$this->MaintainVariable('DP7QPF', $this->Translate('Daypart 7 - Precipitation Liquid'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 7 and $this->ReadPropertyBoolean("ForecastDPRain") == "1");
				$this->MaintainVariable('DP7QPFSNOW', $this->Translate('Daypart 7 - Precipitation Snow'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 7 and $this->ReadPropertyBoolean("ForecastDPRain") == "1");
				$this->MaintainVariable('DP7Temperature', $this->Translate('Daypart 7 - Temperature'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 7 and $this->ReadPropertyBoolean("ForecastDPTemperature") == "1");
				$this->MaintainVariable('DP7WindChill', $this->Translate('Daypart 7 - Wind Chill'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 7 and $this->ReadPropertyBoolean("ForecastDPTemperature") == "1");
				$this->MaintainVariable('DP7Thunder', $this->Translate('Daypart 7 - Thunder'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 7 and $this->ReadPropertyBoolean("ForecastDPThunder") == "1");
				$this->MaintainVariable('DP7UVDescription', $this->Translate('Daypart 7 - UV Description'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 7 and $this->ReadPropertyBoolean("ForecastDPUV") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP7UVIndex', $this->Translate('Daypart 7 - UV Index'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 7 and $this->ReadPropertyBoolean("ForecastDPUV") == "1");
				$this->MaintainVariable('DP7WINDDIR', $this->Translate('Daypart 7 - Wind Direction'), vtFloat, "~WindDirection.F", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 7 and $this->ReadPropertyBoolean("ForecastDPWind") == "1");
				$this->MaintainVariable('DP7WINDDIRText', $this->Translate('Daypart 7 - Wind Direction Text'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 7 and $this->ReadPropertyBoolean("ForecastDPWind") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP7WINDDIRPhrase', $this->Translate('Daypart 7 - Wind Phrase'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 7 and $this->ReadPropertyBoolean("ForecastDPWind") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP7WINDSpeed', $this->Translate('Daypart 7 - Wind Speed'), vtFloat, "~WindSpeed.kmh", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 7 and $this->ReadPropertyBoolean("ForecastDPWind") == "1");
				$this->MaintainVariable('DP7Icon', $this->Translate('Daypart 7 - Icon'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 7 and $this->ReadPropertyBoolean("ForecastDPIcon") == "1");

				$vpos = 400;

				$this->MaintainVariable('DP8DN',
					$this->Translate('Daypart 8 - Day or Night'),
					vtString,
					"",
					$vpos++,
					$this->ReadPropertyInteger("ForecastDP") > 8
				);
				$this->MaintainVariable('DP8Name', $this->Translate('Daypart 8 - Part of day'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 8);
				$this->MaintainVariable('DP8Narrative', $this->Translate('Daypart 8 - Weather Forecast'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 8);
				$this->MaintainVariable('DP8PrecipChance', $this->Translate('Daypart 8 - Precip Chance'), vtInteger, "~Humidity", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 8);
				$this->MaintainVariable('DP8PrecipType', $this->Translate('Daypart 8 - Precip Type'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 8);
				$this->MaintainVariable('DP8CloudCover', $this->Translate('Daypart 8 - Cloud Cover'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 8 and $this->ReadPropertyBoolean("ForecastDPCloudCover") == "1");
				$this->MaintainVariable('DP8QPF', $this->Translate('Daypart 8 - Precipitation Liquid'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 8 and $this->ReadPropertyBoolean("ForecastDPRain") == "1");
				$this->MaintainVariable('DP8QPFSNOW', $this->Translate('Daypart 8 - Precipitation Snow'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 8 and $this->ReadPropertyBoolean("ForecastDPRain") == "1");
				$this->MaintainVariable('DP8Temperature', $this->Translate('Daypart 8 - Temperature'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 8 and $this->ReadPropertyBoolean("ForecastDPTemperature") == "1");
				$this->MaintainVariable('DP8WindChill', $this->Translate('Daypart 8 - Wind Chill'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 8 and $this->ReadPropertyBoolean("ForecastDPTemperature") == "1");
				$this->MaintainVariable('DP8Thunder', $this->Translate('Daypart 8 - Thunder'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 8 and $this->ReadPropertyBoolean("ForecastDPThunder") == "1");
				$this->MaintainVariable('DP8UVDescription', $this->Translate('Daypart 8 - UV Description'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 8 and $this->ReadPropertyBoolean("ForecastDPUV") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP8UVIndex', $this->Translate('Daypart 8 - UV Index'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 8 and $this->ReadPropertyBoolean("ForecastDPUV") == "1");
				$this->MaintainVariable('DP8WINDDIR', $this->Translate('Daypart 8 - Wind Direction'), vtFloat, "~WindDirection.F", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 8 and $this->ReadPropertyBoolean("ForecastDPWind") == "1");
				$this->MaintainVariable('DP8WINDDIRText', $this->Translate('Daypart 8 - Wind Direction Text'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 8 and $this->ReadPropertyBoolean("ForecastDPWind") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP8WINDDIRPhrase', $this->Translate('Daypart 8 - Wind Phrase'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 8 and $this->ReadPropertyBoolean("ForecastDPWind") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP8WINDSpeed', $this->Translate('Daypart 8 - Wind Speed'), vtFloat, "~WindSpeed.kmh", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 8 and $this->ReadPropertyBoolean("ForecastDPWind") == "1");
				$this->MaintainVariable('DP8Icon', $this->Translate('Daypart 8 - Icon'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 8 and $this->ReadPropertyBoolean("ForecastDPIcon") == "1");

				$vpos = 450;

				$this->MaintainVariable('DP9DN',
					$this->Translate('Daypart 9 - Day or Night'),
					vtString,
					"",
					$vpos++,
					$this->ReadPropertyInteger("ForecastDP") > 9
				);
				$this->MaintainVariable('DP9Name', $this->Translate('Daypart 9 - Part of day'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 9);
				$this->MaintainVariable('DP9Narrative', $this->Translate('Daypart 9 - Weather Forecast'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 9);
				$this->MaintainVariable('DP9PrecipChance', $this->Translate('Daypart 9 - Precip Chance'), vtInteger, "~Humidity", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 9);
				$this->MaintainVariable('DP9PrecipType', $this->Translate('Daypart 9 - Precip Type'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 9);
				$this->MaintainVariable('DP9CloudCover', $this->Translate('Daypart 9 - Cloud Cover'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 9 and $this->ReadPropertyBoolean("ForecastDPCloudCover") == "1");
				$this->MaintainVariable('DP9QPF', $this->Translate('Daypart 9 - Precipitation Liquid'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 9 and $this->ReadPropertyBoolean("ForecastDPRain") == "1");
				$this->MaintainVariable('DP9QPFSNOW', $this->Translate('Daypart 9 - Precipitation Snow'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 9 and $this->ReadPropertyBoolean("ForecastDPRain") == "1");
				$this->MaintainVariable('DP9Temperature', $this->Translate('Daypart 9 - Temperature'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 9 and $this->ReadPropertyBoolean("ForecastDPTemperature") == "1");
				$this->MaintainVariable('DP9WindChill', $this->Translate('Daypart 9 - Wind Chill'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 9 and $this->ReadPropertyBoolean("ForecastDPTemperature") == "1");
				$this->MaintainVariable('DP9Thunder', $this->Translate('Daypart 9 - Thunder'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 9 and $this->ReadPropertyBoolean("ForecastDPThunder") == "1");
				$this->MaintainVariable('DP9UVDescription', $this->Translate('Daypart 9 - UV Description'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 9 and $this->ReadPropertyBoolean("ForecastDPUV") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP9UVIndex', $this->Translate('Daypart 9 - UV Index'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 9 and $this->ReadPropertyBoolean("ForecastDPUV") == "1");
				$this->MaintainVariable('DP9WINDDIR', $this->Translate('Daypart 9 - Wind Direction'), vtFloat, "~WindDirection.F", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 9 and $this->ReadPropertyBoolean("ForecastDPWind") == "1");
				$this->MaintainVariable('DP9WINDDIRText', $this->Translate('Daypart 9 - Wind Direction Text'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 9 and $this->ReadPropertyBoolean("ForecastDPWind") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP9WINDDIRPhrase', $this->Translate('Daypart 9 - Wind Phrase'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 9 and $this->ReadPropertyBoolean("ForecastDPWind") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP9WINDSpeed', $this->Translate('Daypart 9 - Wind Speed'), vtFloat, "~WindSpeed.kmh", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 9 and $this->ReadPropertyBoolean("ForecastDPWind") == "1");
				$this->MaintainVariable('DP9Icon', $this->Translate('Daypart 9 - Icon'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 9 and $this->ReadPropertyBoolean("ForecastDPIcon") == "1");

				$vpos = 500;

				$this->MaintainVariable('DP10DN', $this->Translate('Daypart 10 - Day or Night'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 10);
				$this->MaintainVariable('DP10Name', $this->Translate('Daypart 10 - Part of day'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 10);
				$this->MaintainVariable('DP10Narrative', $this->Translate('Daypart 10 - Weather Forecast'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 10);
				$this->MaintainVariable('DP10PrecipChance', $this->Translate('Daypart 10 - Precip Chance'), vtInteger, "~Humidity", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 10);
				$this->MaintainVariable('DP10PrecipType', $this->Translate('Daypart 10 - Precip Type'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 10);
				$this->MaintainVariable('DP10CloudCover', $this->Translate('Daypart 10 - Cloud Cover'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 10 and $this->ReadPropertyBoolean("ForecastDPCloudCover") == "1");
				$this->MaintainVariable('DP10QPF', $this->Translate('Daypart 10 - Precipitation Liquid'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 10 and $this->ReadPropertyBoolean("ForecastDPRain") == "1");
				$this->MaintainVariable('DP10QPFSNOW', $this->Translate('Daypart 10 - Precipitation Snow'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 10 and $this->ReadPropertyBoolean("ForecastDPRain") == "1");
				$this->MaintainVariable('DP10Temperature', $this->Translate('Daypart 10 - Temperature'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 10 and $this->ReadPropertyBoolean("ForecastDPTemperature") == "1");
				$this->MaintainVariable('DP10WindChill', $this->Translate('Daypart 10 - Wind Chill'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 10 and $this->ReadPropertyBoolean("ForecastDPTemperature") == "1");
				$this->MaintainVariable('DP10Thunder', $this->Translate('Daypart 10 - Thunder'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 10 and $this->ReadPropertyBoolean("ForecastDPThunder") == "1");
				$this->MaintainVariable('DP10UVDescription', $this->Translate('Daypart 10 - UV Description'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 10 and $this->ReadPropertyBoolean("ForecastDPUV") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP10UVIndex', $this->Translate('Daypart 10 - UV Index'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 10 and $this->ReadPropertyBoolean("ForecastDPUV") == "1");
				$this->MaintainVariable('DP10WINDDIR', $this->Translate('Daypart 10 - Wind Direction'), vtFloat, "~WindDirection.F", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 10 and $this->ReadPropertyBoolean("ForecastDPWind") == "1");
				$this->MaintainVariable('DP10WINDDIRText', $this->Translate('Daypart 10 - Wind Direction Text'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 10 and $this->ReadPropertyBoolean("ForecastDPWind") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP10WINDDIRPhrase', $this->Translate('Daypart 10 - Wind Phrase'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 10 and $this->ReadPropertyBoolean("ForecastDPWind") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP10WINDSpeed', $this->Translate('Daypart 10 - Wind Speed'), vtFloat, "~WindSpeed.kmh", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 10 and $this->ReadPropertyBoolean("ForecastDPWind") == "1");
				$this->MaintainVariable('DP10Icon', $this->Translate('Daypart 10 - Icon'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 10 and $this->ReadPropertyBoolean("ForecastDPIcon") == "1");

				$vpos = 550;

				$this->MaintainVariable('DP11DN', $this->Translate('Daypart 11 - Day or Night'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 11);
				$this->MaintainVariable('DP11Name', $this->Translate('Daypart 11 - Part of day'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 11);
				$this->MaintainVariable('DP11Narrative', $this->Translate('Daypart 11 - Weather Forecast'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 11);
				$this->MaintainVariable('DP11PrecipChance', $this->Translate('Daypart 11 - Precip Chance'), vtInteger, "~Humidity", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 11);
				$this->MaintainVariable('DP11PrecipType', $this->Translate('Daypart 11 - Precip Type'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 11);
				$this->MaintainVariable('DP11CloudCover', $this->Translate('Daypart 11 - Cloud Cover'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 11 and $this->ReadPropertyBoolean("ForecastDPCloudCover") == "1");
				$this->MaintainVariable('DP11QPF', $this->Translate('Daypart 11 - Precipitation Liquid'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 11 and $this->ReadPropertyBoolean("ForecastDPRain") == "1");
				$this->MaintainVariable('DP11QPFSNOW', $this->Translate('Daypart 11 - Precipitation Snow'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 11 and $this->ReadPropertyBoolean("ForecastDPRain") == "1");
				$this->MaintainVariable('DP11Temperature', $this->Translate('Daypart 11 - Temperature'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 11 and $this->ReadPropertyBoolean("ForecastDPTemperature") == "1");
				$this->MaintainVariable('DP11WindChill', $this->Translate('Daypart 11 - Wind Chill'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 11 and $this->ReadPropertyBoolean("ForecastDPTemperature") == "1");
				$this->MaintainVariable('DP11Thunder', $this->Translate('Daypart 11 - Thunder'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 11 and $this->ReadPropertyBoolean("ForecastDPThunder") == "1");
				$this->MaintainVariable('DP11UVDescription', $this->Translate('Daypart 11 - UV Description'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 11 and $this->ReadPropertyBoolean("ForecastDPUV") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP11UVIndex', $this->Translate('Daypart 11 - UV Index'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 11 and $this->ReadPropertyBoolean("ForecastDPUV") == "1");
				$this->MaintainVariable('DP11WINDDIR', $this->Translate('Daypart 11 - Wind Direction'), vtFloat, "~WindDirection.F", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 11 and $this->ReadPropertyBoolean("ForecastDPWind") == "1");
				$this->MaintainVariable('DP11WINDDIRText', $this->Translate('Daypart 11 - Wind Direction Text'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 11 and $this->ReadPropertyBoolean("ForecastDPWind") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP11WINDDIRPhrase', $this->Translate('Daypart 11 - Wind Phrase'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 11 and $this->ReadPropertyBoolean("ForecastDPWind") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
				$this->MaintainVariable('DP11WINDSpeed', $this->Translate('Daypart 11 - Wind Speed'), vtFloat, "~WindSpeed.kmh", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 11 and $this->ReadPropertyBoolean("ForecastDPWind") == "1");
				$this->MaintainVariable('DP11Icon', $this->Translate('Daypart 11 - Icon'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 11 and $this->ReadPropertyBoolean("ForecastDPIcon") == "1");
		
				/*
				$ForecastDP = $this->ReadPropertyInteger("ForecastDP");

				if ($ForecastDP > 1 OR $ForecastDP == 0) {
			        $this->SendDebug('Var Create: ', 'in create', 0);
					$i = 2;

					while ($i < $ForecastDP) {
						$vpos = 100;
						$this->SendDebug('Var Create: ', $i, 0);
						$this->MaintainVariable('DP'.$i.'DN', $this->Translate('Daypart '.$i.' - Day or Night'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > $i);
						$this->MaintainVariable('DP'.$i.'Name', $this->Translate('Daypart '.$i.' - Part of day'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > $i);
						$this->MaintainVariable('DP'.$i.'Narrative', $this->Translate('Daypart '.$i.' - Weather Forecast'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > $i);
						$this->MaintainVariable('DP'.$i.'PrecipChance', $this->Translate('Daypart '.$i.' - Precip Chance'), vtInteger, "~Humidity", $vpos++, $this->ReadPropertyInteger("ForecastDP") > $i);
						$this->MaintainVariable('DP'.$i.'PrecipType', $this->Translate('Daypart '.$i.' - Precip Type'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > $i);
						$this->MaintainVariable('DP'.$i.'CloudCover', $this->Translate('Daypart '.$i.' - Cloud Cover'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > $i and $this->ReadPropertyBoolean("ForecastDPCloudCover") == "1");
						$this->MaintainVariable('DP'.$i.'QPF', $this->Translate('Daypart '.$i.' - Precipitation Liquid'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastDP") > $i and $this->ReadPropertyBoolean("ForecastDPRain") == "1");
						$this->MaintainVariable('DP'.$i.'QPFSNOW', $this->Translate('Daypart '.$i.' - Precipitation Snow'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastDP") > $i and $this->ReadPropertyBoolean("ForecastDPRain") == "1");
						$this->MaintainVariable('DP'.$i.'Temperature', $this->Translate('Daypart '.$i.' - Temperature'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastDP") > $i and $this->ReadPropertyBoolean("ForecastDPTemperature") == "1");
						$this->MaintainVariable('DP'.$i.'WindChill', $this->Translate('Daypart '.$i.' - Wind Chill'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastDP") > $i and $this->ReadPropertyBoolean("ForecastDPTemperature") == "1");
						$this->MaintainVariable('DP'.$i.'Thunder', $this->Translate('Daypart '.$i.' - Thunder'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > $i and $this->ReadPropertyBoolean("ForecastDPThunder") == "1");
						$this->MaintainVariable('DP'.$i.'UVDescription', $this->Translate('Daypart '.$i.' - UV Description'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > $i and $this->ReadPropertyBoolean("ForecastDPUV") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
						$this->MaintainVariable('DP'.$i.'UVIndex', $this->Translate('Daypart '.$i.' - UV Index'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > $i and $this->ReadPropertyBoolean("ForecastDPUV") == "1");
						$this->MaintainVariable('DP'.$i.'WINDDIR', $this->Translate('Daypart '.$i.' - Wind Direction'), vtFloat, "~WindDirection.F", $vpos++, $this->ReadPropertyInteger("ForecastDP") > 2 and $this->ReadPropertyBoolean("ForecastDPWind") == "1");
						$this->MaintainVariable('DP'.$i.'WINDDIRText', $this->Translate('Daypart '.$i.' - Wind Direction Text'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > $i and $this->ReadPropertyBoolean("ForecastDPWind") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
						$this->MaintainVariable('DP'.$i.'WINDDIRPhrase', $this->Translate('Daypart '.$i.' - Wind Phrase'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > $i and $this->ReadPropertyBoolean("ForecastDPWind") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1");
						$this->MaintainVariable('DP'.$i.'WINDSpeed', $this->Translate('Daypart '.$i.' - Wind Speed'), vtFloat, "~WindSpeed.kmh", $vpos++, $this->ReadPropertyInteger("ForecastDP") > $i and $this->ReadPropertyBoolean("ForecastDPWind") == "1");
						$this->MaintainVariable('DP'.$i.'Icon', $this->Translate('Daypart '.$i.' - Icon'), vtInteger, "", $vpos++, $this->ReadPropertyInteger("ForecastDP") > $i and $this->ReadPropertyBoolean("ForecastDPIcon") == "1");
						$this->SendDebug('VPOS: ', $vpos, 0);
						$i++;
					}

				}
				*/

				$vpos = 1000;

				$this->MaintainVariable('D1Forecast', $this->Translate('Day 1 Weather Forecast'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastShort") > "0");
				$this->MaintainVariable('D1QPF', $this->Translate('Day 1 Precipitation Liquid'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastShort") > "0");
				$this->MaintainVariable('D1QPFSNOW', $this->Translate('Day 1 Precipitation Snow'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastShort") > "0");
				$this->MaintainVariable('D1TemperatureMax', $this->Translate('Day 1 Temperature Max'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastShort") > "0");
				$this->MaintainVariable('D1TemperatureMin', $this->Translate('Day 1 Temperature Min'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastShort") > "0");

				$vpos = 1050;
				$this->MaintainVariable('D2Forecast', $this->Translate('Day 2 Weather Forecast'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastShort") > "1");
				$this->MaintainVariable('D2QPF', $this->Translate('Day 2 Precipitation Liquid'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastShort") > "1");
				$this->MaintainVariable('D2QPFSNOW', $this->Translate('Day 2 Precipitation Snow'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastShort") > "1");
				$this->MaintainVariable('D2TemperatureMax', $this->Translate('Day 2 Temperature Max'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastShort") > "1");
				$this->MaintainVariable('D2TemperatureMin', $this->Translate('Day 2 Temperature Min'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastShort") > "1");
				$this->MaintainVariable('D2RainAmount', $this->Translate('Day 2 Amount of Rain'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastShort") > "1" AND $this->ReadPropertyBoolean("CalculateUpcomingRain") == 1);


				$vpos = 1100;
				$this->MaintainVariable('D3Forecast', $this->Translate('Day 3 Weather Forecast'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastShort") > "2");
				$this->MaintainVariable('D3QPF', $this->Translate('Day 3 Precipitation Liquid'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastShort") > "2");
				$this->MaintainVariable('D3QPFSNOW', $this->Translate('Day 3 Precipitation Snow'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastShort") > "2");
				$this->MaintainVariable('D3TemperatureMax', $this->Translate('Day 3 Temperature Max'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastShort") > "2");
				$this->MaintainVariable('D3TemperatureMin', $this->Translate('Day 3 Temperature Min'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastShort") > "2");
				$this->MaintainVariable('D3RainAmount', $this->Translate('Day 3 Amount of Rain'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastShort") > "2" AND $this->ReadPropertyBoolean("CalculateUpcomingRain") == 1);


				$vpos = 1250;
				$this->MaintainVariable('D4Forecast', $this->Translate('Day 4 Weather Forecast'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastShort") > "3");
				$this->MaintainVariable('D4QPF', $this->Translate('Day 4 Precipitation Liquid'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastShort") > "3");
				$this->MaintainVariable('D4QPFSNOW', $this->Translate('Day 4 Precipitation Snow'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastShort") > "3");
				$this->MaintainVariable('D4TemperatureMax', $this->Translate('Day 4 Temperature Max'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastShort") > "3");
				$this->MaintainVariable('D4TemperatureMin', $this->Translate('Day 4 Temperature Min'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastShort") > "3");
				$this->MaintainVariable('D4RainAmount', $this->Translate('Day 4 Amount of Rain'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastShort") > "3" AND $this->ReadPropertyBoolean("CalculateUpcomingRain") == 1);


				$vpos = 1300;
				$this->MaintainVariable('D5Forecast', $this->Translate('Day 5 Weather Forecast'), vtString, "", $vpos++, $this->ReadPropertyInteger("ForecastShort") > "4");
				$this->MaintainVariable('D5QPF', $this->Translate('Day 5 Precipitation Liquid'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastShort") > "4");
				$this->MaintainVariable('D5QPFSNOW', $this->Translate('Day 5 Precipitation Snow'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastShort") > "4");
				$this->MaintainVariable('D5TemperatureMax', $this->Translate('Day 5 Temperature Max'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastShort") > "4");
				$this->MaintainVariable('D5TemperatureMin', $this->Translate('Day 5 Temperature Min'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyInteger("ForecastShort") > "4");
				$this->MaintainVariable('D5RainAmount', $this->Translate('Day 5 Amount of Rain'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyInteger("ForecastShort") > "4" AND $this->ReadPropertyBoolean("CalculateUpcomingRain") == 1);

				$vpos = 2000;
				$this->MaintainVariable('DLVTemperature', $this->Translate('Download Temperature'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyBoolean("DLTemperature") == "1");
				$this->MaintainVariable('DLVSolarRadiation', $this->Translate('Download Solar Radiation'), vtFloat, "", $vpos++, $this->ReadPropertyBoolean("DLSolarRadiation") == "1");
				$this->MaintainVariable('DLVUV', $this->Translate('Download UV Index'), vtInteger, "~UVIndex", $vpos++, $this->ReadPropertyBoolean("DLUV") == "1");
				$this->MaintainVariable('DLVWindDirection', $this->Translate('Download Wind Direction'), vtFloat, "~WindDirection.F", $vpos++, $this->ReadPropertyBoolean("DLWindDirection") == "1");
				$this->MaintainVariable('DLVHumidity', $this->Translate('Download Humidity'), vtInteger, "~Humidity", $vpos++, $this->ReadPropertyBoolean("DLHumidity") == "1");
                $this->MaintainVariable('DLDewPoint', $this->Translate('Download Dew Point'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyBoolean("DLDewPT") == "1");
				$this->MaintainVariable('DLVWindchill', $this->Translate('Download Windchill'), vtFloat, "~Temperature", $vpos++, $this->ReadPropertyBoolean("DLWindchill") == "1");
				$this->MaintainVariable('DLVWindSpeed', $this->Translate('Download Wind Speed'), vtFloat, "~WindSpeed.kmh", $vpos++, $this->ReadPropertyBoolean("DLWindSpeed") == "1");
				$this->MaintainVariable('DLVWindGust', $this->Translate('Download Wind Gust'), vtFloat, "~WindSpeed.kmh", $vpos++, $this->ReadPropertyBoolean("DLWindGust") == "1");
				$this->MaintainVariable('DLVPressure', $this->Translate('Download Pressure'), vtFloat, "~AirPressure.F", $vpos++, $this->ReadPropertyBoolean("DLPressure") == "1");
				$this->MaintainVariable('DLVRainRate', $this->Translate('Download Rain Rate'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyBoolean("DLRainRate") == "1");
				$this->MaintainVariable('DLVRainTotal', $this->Translate('Download Rain Total'), vtFloat, "~Rainfall", $vpos++, $this->ReadPropertyBoolean("DLRainTotal") == "1");

				$vpos = 3000;
				$this->MaintainVariable('JSONRawForecastVar', $this->Translate('JSON Raw Data Forecast'), vtString, "", $vpos++, $this->ReadPropertyBoolean("JSONRawForecast") == "1");
				$this->MaintainVariable('JSONRawStationVar', $this->Translate('JSON Raw Station Data'), vtString, "", $vpos++, $this->ReadPropertyBoolean("JSONRawStation") == "1");


		}


		public function Forecast() {

			$WU_ID = $this->ReadPropertyString("WU_ID");
			$Language = $this->ReadPropertyString("Language");
			$WU_API = $this->ReadPropertyString("WU_API");
			$locationObject = json_decode($this->ReadPropertyString('Location'), true);
			$Latitude = str_replace(",",".",$locationObject['latitude']);
			$Longitude = str_replace(",",".",$locationObject['longitude']);
			//$Longitude = str_replace(",",".",$this->ReadPropertyString("longitude"));
			//$Latitude = str_replace(",",".",$this->ReadPropertyString("latitude"));

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://api.weather.com/v3/wx/forecast/daily/5day?geocode='.$Latitude.','.$Longitude.'&format=json&units=m&language='.$Language.'&apiKey='.$WU_API);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			$RawData = curl_exec($ch);
			curl_close($ch);

			$this->SendDebug('Raw Data: ', $RawData,0);
			$RawJSON = json_decode($RawData);

			// New For
			$ForecastShort = $this->ReadPropertyInteger("ForecastShort");
			if ($ForecastShort > "0") {
			$i = 0;
			$count = 1;

				while ($i < $ForecastShort) {

					if (isset($RawJSON->narrative[$i]))	{
						$Narrative[$count] = $RawJSON->narrative[$i];
						SetValue($this->GetIDForIdent("D".$count."Forecast"), (string)$Narrative[$count]);
					}
					

					if (isset($RawJSON->qpf[$i])) {
						$QPF[$count] = $RawJSON->qpf[$i];
						SetValue($this->GetIDForIdent("D".$count."QPF"), (float)$QPF[$count]);
					}

					if (isset($RawJSON->qpfSnow[$i])) {
						$QPFSNOW[$count] = $RawJSON->qpfSnow[$i];
						SetValue($this->GetIDForIdent("D".$count."QPFSNOW"), (float)$QPFSNOW[$count]);
					}

					if (isset($RawJSON->temperatureMax[$i])) {
						$TemperatureMax[$count] = $RawJSON->temperatureMax[$i];
						SetValue($this->GetIDForIdent("D".$count."TemperatureMax"), (float)$TemperatureMax[$count]);
					}

					if (isset($RawJSON->temperatureMin[$i])) {
						$TemperatureMin[$count] = $RawJSON->temperatureMin[$i];
						SetValue($this->GetIDForIdent("D".$count."TemperatureMin"), (float)$TemperatureMin[$count]);
					}
					
					$i++;
					$count++;
				}
			}

			// Detail Forecast Segments
			$ForecastDP = $this->ReadPropertyInteger("ForecastDP");
			$this->SendDebug('DP: ', $ForecastDP, 0);

			if ($ForecastDP > "0") { //1
				$i = 0;
				$day = 0;
				$part = 0;

				while ($i < $ForecastDP) { //1
				$this->SendDebug('DP i: ', $i, 0);

					$DPDN[$i] = $RawJSON->daypart[$day]->dayOrNight[$part];
					SetValue($this->GetIDForIdent("DP".$i."DN"), (string)$DPDN[$i]);
					$DPName[$i] = $RawJSON->daypart[$day]->daypartName[$part];
					SetValue($this->GetIDForIdent("DP".$i."Name"), (string)$DPName[$i]);
					$DPNarrative[$i] = $RawJSON->daypart[$day]->narrative[$part];
					SetValue($this->GetIDForIdent("DP".$i."Narrative"), (string)$DPNarrative[$i]);
					$DPPrecipChance[$i] = $this->Translate($RawJSON->daypart[$day]->precipChance[$part]);
					SetValue($this->GetIDForIdent("DP".$i."PrecipChance"), (int)$DPPrecipChance[$i]);
					$DPPrecipType[$i] = $this->Translate($RawJSON->daypart[$day]->precipType[$part]);
					SetValue($this->GetIDForIdent("DP".$i."PrecipType"), (string)$DPPrecipType[$i]);


					if ($this->ReadPropertyBoolean("ForecastDPRain") == "1") {

						if (isset($RawJSON->daypart[$day]->qpf[$part])) {
						$DPQPF[$i] = $RawJSON->daypart[$day]->qpf[$part];
							SetValue($this->GetIDForIdent("DP".$i."QPF"), (float)$DPQPF[$i]);
						}

						if (isset($RawJSON->daypart[$day]->qpfSnow[$part])) {
						$DPQPFSNOW[$i] = $RawJSON->daypart[$day]->qpfSnow[$part];

							SetValue($this->GetIDForIdent("DP".$i."QPFSNOW"), (float)$DPQPFSNOW[$i]);
						}
					}

					if ($this->ReadPropertyBoolean("ForecastDPTemperature") == "1") {

						if (isset($RawJSON->daypart[$day]->temperature[$part])) {
							$DPTemperature[$i] = $RawJSON->daypart[$day]->temperature[$part];
							SetValue($this->GetIDForIdent("DP".$i."Temperature"), (float)$DPTemperature[$i]);
						}

						if (isset($RawJSON->daypart[$day]->temperatureWindChill[$part])) {
							$DPWindChill[$i] = $RawJSON->daypart[$day]->temperatureWindChill[$part];
							SetValue($this->GetIDForIdent("DP".$i."WindChill"), (float)$DPWindChill[$i]);
						}
					}

					if ($this->ReadPropertyBoolean("ForecastDPThunder") == "1") {
						if (isset($RawJSON->daypart[$day]->thunderCategory[$part])) {
							$DPThunder[$i] = $RawJSON->daypart[$day]->thunderCategory[$part];
							SetValue($this->GetIDForIdent("DP".$i."Thunder"), (string)$DPThunder[$i]);
						}
					}

					if ($this->ReadPropertyBoolean("ForecastDPUV") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1") {

						if (isset($RawJSON->daypart[$day]->uvDescription[$part])) {
							$DPUVDescription[$i] = $RawJSON->daypart[$day]->uvDescription[$part];
							SetValue($this->GetIDForIdent("DP".$i."UVDescription"), (string)$DPUVDescription[$i]);
						}
					}

					if ($this->ReadPropertyBoolean("ForecastDPUV") == "1") {
						if (isset($RawJSON->daypart[$day]->uvIndex[$part])) {
							$DPUVIndex[$i] = $RawJSON->daypart[$day]->uvIndex[$part];
							SetValue($this->GetIDForIdent("DP".$i."UVIndex"), (int)$DPUVIndex[$i]);
						}
					}

					if ($this->ReadPropertyBoolean("ForecastDPWind") == "1") {
						if (isset($RawJSON->daypart[$day]->windDirection[$part])) {
							$DPWINDDIR[$i] = $RawJSON->daypart[$day]->windDirection[$part];
							SetValue($this->GetIDForIdent("DP".$i."WINDDIR"), (float)$DPWINDDIR[$i]);
						}

						if (isset($RawJSON->daypart[$day]->windSpeed[$part])) {
							$DPWINDSpeed[$i] = $RawJSON->daypart[$day]->windSpeed[$part];
							SetValue($this->GetIDForIdent("DP".$i."WINDSpeed"), (float)$DPWINDSpeed[$i]);
						}
					}

					if ($this->ReadPropertyBoolean("ForecastDPWind") == "1" and $this->ReadPropertyBoolean("ForecastDPNarrative") == "1") {
						if (isset($RawJSON->daypart[$day]->windDirectionCardinal[$part])) {
							$DPWINDDIRText[$i] = $RawJSON->daypart[$day]->windDirectionCardinal[$part];
							SetValue($this->GetIDForIdent("DP".$i."WINDDIRText"), (string)$DPWINDDIRText[$i]);
						}

						if (isset($RawJSON->daypart[$day]->windPhrase[$part])) {
							$DPWINDDIRPhrase[$i] = $RawJSON->daypart[$day]->windPhrase[$part];
							SetValue($this->GetIDForIdent("DP".$i."WINDDIRPhrase"), (string)$DPWINDDIRPhrase[$i]);
						}
					}

					if ($this->ReadPropertyBoolean("ForecastDPIcon") == "1") {
						if (isset($RawJSON->daypart[$day]->iconCode[$part])) {
							$DPIcon[$i] = $RawJSON->daypart[$day]->iconCode[$part];
							SetValue($this->GetIDForIdent("DP".$i."Icon"), (int)$DPIcon[$i]);
						}
					}

					if ($this->ReadPropertyBoolean("ForecastDPCloudCover") == "1") {
						if (isset($RawJSON->daypart[$day]->cloudCover[$part])) {
							$DPCloudCover[$i] = $RawJSON->daypart[$day]->cloudCover[$part];
							SetValue($this->GetIDForIdent("DP".$i."CloudCover"), (int)$DPCloudCover[$i]);
						}
					}

					if ($i == ($ForecastDP - 1) ) { 	
						$day++;
					}

					$part++;
					$i++;
					
				}
			}	

			If ($this->ReadPropertyBoolean("JSONRawForecast") == "1") {
				//$JSONRawForecast = $RawJSON;
				//$RawJSONForecast = json_encode($RawData);
				SetValue($this->GetIDForIdent("JSONRawForecastVar"), (string)$RawData);

			}

		}


		public function UploadToWunderground() {

		// Prepare Temperature for upload

			$Debug = $this->ReadPropertyBoolean("Debug");

			// setting standard values like time and login

			$WU_ID = $this->ReadPropertyString("WU_ID");
			$WU_Password = $this->ReadPropertyString("WU_StationKey");
			$time = "now";

			$responseUrl = "https://weatherstation.wunderground.com/weatherstation/updateweatherstation.php?ID=".$WU_ID."&PASSWORD=".$WU_Password."&dateutc=".$time;

			If ($this->ReadPropertyInteger("OutsideTemperature") != "")	{
				$Temperature = GetValue($this->ReadPropertyInteger("OutsideTemperature"));
				$TemperatureF = str_replace(",",".",(($Temperature * 9) /5 + 32));
				$this->SendDebug("Wunderground PWS Update","Wunderground Upload Temperature F: ".$TemperatureF, 0);

				$responseUrl .= "&tempf=".$TemperatureF;
			}


			// Prepare Dewpoint for upload

			If ($this->ReadPropertyInteger("DewPoint") != "") {
				$DewPoint = GetValue($this->ReadPropertyInteger("DewPoint"));
				$DewPointF = str_replace(",",".",(($DewPoint * 9) /5 + 32));
				$this->SendDebug("Wunderground PWS Update","Wunderground Upload Taupunkt F: ".$DewPointF, 0);

				$responseUrl .= "&dewptf=".$DewPointF;
			}


			// Prepare Humidity for upload

			If ($this->ReadPropertyInteger("Humidity") != "") {
				$Humidity = GetValue($this->ReadPropertyInteger("Humidity"));
				$this->SendDebug("Wunderground PWS Update","Wunderground Upload Humidity: ".$Humidity, 0);

				$responseUrl .= "&humidity=".$Humidity;
			}


			// Prepare Windirection for upload

			If ($this->ReadPropertyInteger("WindDirection") != "")	{
				$WindDirection = GetValue($this->ReadPropertyInteger("WindDirection"));
				$WindDirectionU = str_replace(",",".",$WindDirection);
				$this->SendDebug("Wunderground PWS Update","Wunderground Upload Wind Direction: ".$WindDirectionU, 0);

				$responseUrl .= "&winddir=".$WindDirectionU;
			}


			// Prepare Windspeed for upload

			If ($this->ReadPropertyInteger("WindSpeed") != "") {
				$WindSpeed = GetValue($this->ReadPropertyInteger("WindSpeed"));

				If ($this->ReadPropertyString("WindConversion") == "kmh") {
						$WindSpeed = str_replace(",",".",Round(($WindSpeed / 3.6),2));
						$this->SendDebug("Wunderground PWS Update","Converting from KMH to M/S", 0);

					}

				$WindSpeedU = str_replace(",",".",Round(($WindSpeed * 2.2369),2));
				$this->SendDebug("Wunderground PWS Update","Wunderground Upload Windspeed: ".$WindSpeedU." mph (".$WindSpeed." m/s)", 0);

				$responseUrl .= "&windspeedmph=".$WindSpeedU;
			}


			// Prepare Windgust for upload

			If ($this->ReadPropertyInteger("WindGust") != "") {
				$WindGust = GetValue($this->ReadPropertyInteger("WindGust"));

				If ($this->ReadPropertyString("WindConversion") == "kmh") {
					$WindGust = str_replace(",",".",Round(($WindGust / 3.6),2));
					$this->SendDebug("Wunderground PWS Update","Converting from KMH to M/S", 0);

				}

				$WindGustU = str_replace(",",".",Round(($WindGust * 2.2369),2));
				$this->SendDebug("Wunderground PWS Update","Wunderground Upload Wind Gust: ".$WindGustU, 0);

				$responseUrl .= "&windgustmph=".$WindGustU;
			}


			// Prepare Rain last hour for upload

			If ($this->ReadPropertyInteger("Rain_last_Hour") != "")	{
				$Rain_last_Hour = GetValue($this->ReadPropertyInteger("Rain_last_Hour"));
				$Rain_last_Hour = str_replace(",",".",Round(($Rain_last_Hour / 25.4),2));
				$this->SendDebug("Wunderground PWS Update","Wunderground Upload Rain Last Hour: ".$Rain_last_Hour, 0);

				$responseUrl .= "&rainin=".$Rain_last_Hour;
			}


			// Prepare Rain 24h for upload

			If ($this->ReadPropertyInteger("Rain24h") != "") {
				$Rain24h = GetValue($this->ReadPropertyInteger("Rain24h"));
				$Rain24h = str_replace(",",".",Round(($Rain24h / 25.4),2));
				$this->SendDebug("Wunderground PWS Update","Wunderground Upload Rain in 24h: ".$Rain24h, 0);

				$responseUrl .= "&dailyrainin=".$Rain24h;
			}


			// Prepare Airpressure for upload

			If ($this->ReadPropertyInteger("AirPressure") != "") {
				$AirPressure = GetValue($this->ReadPropertyInteger("AirPressure"));
				$BPI = str_replace(",",".",Round(($AirPressure * 0.0295299830714),4));
				$this->SendDebug("Wunderground PWS Update","Wunderground Upload Airpressure in BPI: ".$AirPressure, 0);

				$responseUrl .= "&baromin=".$BPI;
			}


			// Prepare UV Index for upload

			If ($this->ReadPropertyInteger("UVIndex") != "") {
				$UVIndex = GetValue($this->ReadPropertyInteger("UVIndex"));
				$this->SendDebug("Wunderground PWS Update","Wunderground Upload UV Index: ".$UVIndex, 0);

				$responseUrl .= "&UV=".$UVIndex;
			}

			$Response =file_get_contents($responseUrl);
			$this->SendDebug("Wunderground PWS Update","Wunderground Upload Service: ".$Response, 0);

		}



    	public function CurrentPWSData() {

            $DLT_WU_ID = $this->ReadPropertyString("DLT_WU_ID");
            $WU_API = $this->ReadPropertyString("WU_API");

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.weather.com/v2/pws/observations/current?stationId='.$DLT_WU_ID.'&format=json&units=m&apiKey='.$WU_API);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $RawData = curl_exec($ch);
            curl_close($ch);

            $RawJSON = json_decode($RawData);

            // new section for download

            If ($this->ReadPropertyBoolean("DLTemperature") == "1")	{
				if (isset($RawJSON->observations[0]->metric->temp))	{
					$DLJSONTemp = $RawJSON->observations[0]->metric->temp;
					SetValue($this->GetIDForIdent("DLVTemperature"), (float)$DLJSONTemp);
				}
            }

            If ($this->ReadPropertyBoolean("DLDewPT") == "1") {
				if (isset($RawJSON->observations[0]->metric->dewpt)) {
					$DLDewPT = $RawJSON->observations[0]->metric->dewpt;
					SetValue($this->GetIDForIdent("DLDewPoint"), (float)$DLDewPT);
				}
            }

            If ($this->ReadPropertyBoolean("DLSolarRadiation") == "1") {
				if (isset($RawJSON->observations[0]->solarRadiation)) {
					$DLJSONSolarRadiation = $RawJSON->observations[0]->solarRadiation;
					SetValue($this->GetIDForIdent("DLVSolarRadiation"), (float)$DLJSONSolarRadiation);
				}
            }


            If ($this->ReadPropertyBoolean("DLWindDirection") == "1") {
				if (isset($RawJSON->observations[0]->winddir)) {
					$DLJSONWindDirection = $RawJSON->observations[0]->winddir;
					SetValue($this->GetIDForIdent("DLVWindDirection"), (float)$DLJSONWindDirection);
				}
            }


            If ($this->ReadPropertyBoolean("DLHumidity") == "1") {
				if (isset($RawJSON->observations[0]->humidity))	{
					$DLJSONDLHumidity = $RawJSON->observations[0]->humidity;
					SetValue($this->GetIDForIdent("DLVHumidity"), (integer)$DLJSONDLHumidity);
				}
            }


            If ($this->ReadPropertyBoolean("DLWindchill") == "1") {
				if (isset($RawJSON->observations[0]->metric->windChill)) {
					$DLJSONWindchill = $RawJSON->observations[0]->metric->windChill;
					SetValue($this->GetIDForIdent("DLVWindchill"), (float)$DLJSONWindchill);
				}
            }


            If ($this->ReadPropertyBoolean("DLWindSpeed") == "1") {
				if (isset($RawJSON->observations[0]->metric->windSpeed)) {
					$DLJSONWindSpeed = $RawJSON->observations[0]->metric->windSpeed;
					SetValue($this->GetIDForIdent("DLVWindSpeed"), (float)$DLJSONWindSpeed);
				}
            }


            If ($this->ReadPropertyBoolean("DLWindGust") == "1") {
				if (isset($RawJSON->observations[0]->metric->windGust))	{
					$DLJSONWindGust = $RawJSON->observations[0]->metric->windGust;
					SetValue($this->GetIDForIdent("DLVWindGust"), (float)$DLJSONWindGust);
				}
            }


            If ($this->ReadPropertyBoolean("DLPressure") == "1") {
				if (isset($RawJSON->observations[0]->metric->pressure))	{
					$DLJSONPressure = $RawJSON->observations[0]->metric->pressure;
					SetValue($this->GetIDForIdent("DLVPressure"), (float)$DLJSONPressure);
				}
            }


            If ($this->ReadPropertyBoolean("DLRainRate") == "1") {
				if (isset($RawJSON->observations[0]->metric->precipRate)) {
					$DLJSONRainRate = $RawJSON->observations[0]->metric->precipRate;
					SetValue($this->GetIDForIdent("DLVRainRate"), (float)$DLJSONRainRate);
				}
            }


            If ($this->ReadPropertyBoolean("DLRainTotal") == "1") {
				if (isset($RawJSON->observations[0]->metric->precipTotal)) {
					$DLJSONRainTotal = $RawJSON->observations[0]->metric->precipTotal;
					SetValue($this->GetIDForIdent("DLVRainTotal"), (float)$DLJSONRainTotal);
				}
            }

			If ($this->ReadPropertyBoolean("JSONRawStation") == "1") {
				//$JSONRawStation = $RawJSON;
				$RawJSONStation = json_encode($RawData);
				//$this->SendDebug('Raw Data: ', $RawData,0);
				SetValue($this->GetIDForIdent("JSONRawStationVar"), (string)$RawData);
        		$this->SetBuffer('RawJSONStation', $RawJSONStation);
				$Bufferdata = $this->GetBuffer("RawJSONStation");
        		$this->SendDebug('Raw Data Current Weather: ', $Bufferdata ,0);
				//Encode
				/*
				$this->SetBuffer('RawJSONStation', $RawJSONStation);
				$Bufferdata = $this->GetBuffer("RawJSONStation");
				$this->SendDebug('Raw Data: ', $Bufferdata ,0);
				*/
			}

		}

	}
