<?

	class WundergroundPWSSync extends IPSModule
	{
		
		public function Create()
		{
			//Never delete this line!
			parent::Create();
			
			$this->RegisterPropertyString("WU_ID", 0);
			$this->RegisterPropertyString("WU_Password",0);
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
			$this->RegisterPropertyInteger("Timer", 10);
			$this->RegisterPropertyBoolean("Debug", 0);
			
			//Component sets timer, but default is OFF
			$this->RegisterTimer("UpdateTimer",0,"WUPWSS_UploadToWunderground(\$_IPS['TARGET']);");			
		}
	
		public function ApplyChanges()
		{
			
			//Never delete this line!
			parent::ApplyChanges();
			
									
		        //Timer Update - if greater than 0 = On
				
				$TimerMS = $this->ReadPropertyInteger("Timer") * 1000;
				
        		$this->SetTimerInterval("UpdateTimer",$TimerMS);
    				
		}
	
		
		public function UploadToWunderground()
		{
		
		
		// Prepare Temperature for upload
		
		$Debug = $this->ReadPropertyBoolean("Debug");
		
		If ($this->ReadPropertyInteger("OutsideTemperature") != "")
		{
		$Temperature = GetValue($this->ReadPropertyInteger("OutsideTemperature"));
		$TemperatureF = str_replace(",",".",(($Temperature * 9) /5 + 32));
		$this->SendDebug("Wunderground PWS Update","Wunderground Upload Temperature F: ".$TemperatureF, 0);	
		}
		
		ElseIf ($this->ReadPropertyInteger("OutsideTemperature") == "0")
		{
		$TemperatureF = "";
		}
		
		// Prepare Dewpoint for upload
				
		If ($this->ReadPropertyInteger("DewPoint") != "")
		{
		$DewPoint = GetValue($this->ReadPropertyInteger("DewPoint"));
		$DewPointF = str_replace(",",".",(($DewPoint * 9) /5 + 32));
		$this->SendDebug("Wunderground PWS Update","Wunderground Upload Taupunkt F: ".$DewPointF, 0);
		}
		
		ElseIf ($this->ReadPropertyInteger("DewPoint") == "0")
		{
		$DewPointF = "";
		}

		// Prepare Humidity for upload
				
		If ($this->ReadPropertyInteger("Humidity") != "")
		{
		$Humidity = GetValue($this->ReadPropertyInteger("Humidity"));
		$this->SendDebug("Wunderground PWS Update","Wunderground Upload Humidity: ".$Humidity, 0);
		}
		
		ElseIf ($this->ReadPropertyInteger("Humidity") == "0")
		{
		$Humidity = "";
		}
			
		// Prepare Windirection for upload
				
		If ($this->ReadPropertyInteger("WindDirection") != "")
		{
		$WindDirection = GetValue($this->ReadPropertyInteger("WindDirection"));
		$WindDirectionU = str_replace(",",".",$WindDirection);
		$this->SendDebug("Wunderground PWS Update","Wunderground Upload Wind Direction: ".$WindDirectionU, 0);		
		}
		
		ElseIf ($this->ReadPropertyInteger("WindDirection") == "0")
		{
		$WindDirectionU = "";
		}

		// Prepare Windspeed for upload
				
		If ($this->ReadPropertyInteger("WindSpeed") != "")
		{
		$WindSpeed = GetValue($this->ReadPropertyInteger("WindSpeed"));
		$WindSpeedU = str_replace(",",".",Round(($WindSpeed * 2.2369),2));
		$this->SendDebug("Wunderground PWS Update","Wunderground Upload Windspeed: ".$WindSpeedU, 0);
		}
		
		ElseIf ($this->ReadPropertyInteger("WindSpeed") == "0")
		{
		$WindSpeedU = "";
		}
		
		// Prepare Windgust for upload
				
		If ($this->ReadPropertyInteger("WindGust") != "")
		{
		$WindGust = GetValue($this->ReadPropertyInteger("WindGust"));
		$WindGustU = str_replace(",",".",Round(($WindGust * 2.2369),2));
		$this->SendDebug("Wunderground PWS Update","Wunderground Upload Wind Gust: ".$WindGustU, 0);
		}
		
		ElseIf ($this->ReadPropertyInteger("WindGust") == "0")
		{
		$WindGustU = "";
		}

		
		// Prepare Rain last hour for upload
				
		If ($this->ReadPropertyInteger("Rain_last_Hour") != "")
		{
		$Rain_last_Hour = GetValue($this->ReadPropertyInteger("Rain_last_Hour"));
		$Rain_last_Hour = str_replace(",",".",Round(($Rain_last_Hour / 2.54),2));
		$this->SendDebug("Wunderground PWS Update","Wunderground Upload Rain Last Hour: ".$Rain_last_Hour, 0);
		}
		
		ElseIf ($this->ReadPropertyInteger("Rain_last_Hour") == "0")
		{
		$Rain_last_Hour = "";
		}

		// Prepare Rain 24h for upload
				
		If ($this->ReadPropertyInteger("Rain24h") != "")
		{
		$Rain24h = GetValue($this->ReadPropertyInteger("Rain24h"));
		$Rain24h = str_replace(",",".",Round(($Rain24h / 2.54),2));
		$this->SendDebug("Wunderground PWS Update","Wunderground Upload Rain in 24h: ".$Rain24h, 0);
		}
		
		ElseIf ($this->ReadPropertyInteger("Rain24h") == "0")
		{
		$Rain24h = "";
		}

		// Prepare Airpressure for upload
				
		If ($this->ReadPropertyInteger("AirPressure") != "")
		{
		$AirPressure = GetValue($this->ReadPropertyInteger("AirPressure"));
		$BPI = str_replace(",",".",Round(($AirPressure * 0.0295299830714),4));
		$this->SendDebug("Wunderground PWS Update","Wunderground Upload Airpressure in BPI: ".$AirPressure, 0);
		}
		
		ElseIf ($this->ReadPropertyInteger("AirPressure") == "0")
		{
		$BPI = "";
		}

		// Prepare UV Index for upload
				
		If ($this->ReadPropertyInteger("UVIndex") != "")
		{
		$UVIndex = GetValue($this->ReadPropertyInteger("UVIndex"));
		$this->SendDebug("Wunderground PWS Update","Wunderground Upload UV Index: ".$UVIndex, 0);
		}
		
		ElseIf ($this->ReadPropertyInteger("UVIndex") == "0")
		{
		$UVIndex = "";
		}

			
		// setting standard values like time and login
		
		$WU_ID = $this->ReadPropertyString("WU_ID");
		$WU_Password = $this->ReadPropertyString("WU_Password");
		
		$date = date('Y-m-d');
		$hour = date('H');
		$minute = date('i');
		$second = date('s');
		$time = $date.'+'.$hour.'%3A'.$minute.'%3A'.$second;
		
		
		// Upload to Wunderground
		$Response =file_get_contents('https://weatherstation.wunderground.com/weatherstation/updateweatherstation.php?ID='.$WU_ID."&PASSWORD=".$WU_Password."&dateutc=".$time.
		"&tempf=".$TemperatureF.
		"&dewptf=".$DewPointF.
		"&winddir=".$WindDirectionU.
		"&humidity=".$Humidity.
		"&windspeedmph=".$WindSpeedU.
		"&windgustmph=".$WindGustU.
		"&rainin=".$Rain_last_Hour.
		"&dailyrainin=".$Rain24h.
		"&baromin=".$BPI.
		"&UV=".$UVIndex);
		
		$this->SendDebug("Wunderground PWS Update","Wunderground Upload Service: ".$Response, 0);
		
		}
	
	}

?>
