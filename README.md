# Symcon - Wunderground PWS Sync & Vorhersage Modul (new WU API)

Diese Modul erlaubt basierend auf der neuen Wunderground/Weather.com API

* den Upload von Wetterdaten einer eigenen Wetterstation 
* den Download von Daten ein Wetterstation anhand der Station ID
* Download der Vorhersage grob (Übersicht maximal 5 Tage), anhand von Geodaten
* Download der Vorhersage detailiert (in 12 Stunden Segmenten - maximal 5 Tage), anhand von Geodaten

## Benötigte Dinge
- Account bei Wunderground inkl einer dort angelegten Wetterstation!
- Einen neuen API Key https://www.wunderground.com/member/api-keys

WICHTIG: Die Seite für die Erstellung des API Keys ist erst noch dem Upload von Daten (die aktuelle Aussentemperatur reicht) verfügbar. Stand März 2019 kommt man auf den API Link NUR durch den Link - es gibt noch keinen Aufruf auf der Website. 

## Voraussetzungen
IP-Symcon ab Version 4.x

## Software-Installation
Über das Modul-Control folgende URL hinzufügen.
https://github.com/elueckel/SymconWUPWSS

## Einrichten der Instanzen in IP-Symcon
Unter "Instanz hinzufügen" ist das 'WundergroundPWSSync'-Modul unter dem Hersteller '(Sonstige)' aufgeführt.

# Version 1.0 12/02/2018
* Upload von Wetterdaten an Wunderground
* Anmeldung an Wunderground via Station ID und API Key
* Auswahl von diversen Wetterdaten
* Upload Konfigurierbar in Schritten von Sekunden

# Version 2.0 17/03/2019
* Einbindung der neuen Weather.com API seitens Wunderground für Uploader von Wetterdaten
* Anmeldung an der neuen API durch neuen Key (siehe oben)
* Download von Übersichtswetterdaten für bis zu 5 Tage (benötigt wird API Key und Geodaten)
* Download von Detailwetterdaten in 12h Segmenten (7am - 7pm Tag / 7pm - 7am Nacht) - Timer läuft immer um 7 und 19 Uhr um Daten abzurufen (benötigt wird API Key und Geodaten)
* Download von Daten einer anderen Wetterstation anhand der Station ID (benötigt werden Station ID und API Key)
* Detailwetterdaten können konfiguriert werden z.B. nur Wind, Niederschlag usw.
* Vorsicht ... wenn alles ausgewählt ist werden über 200 Variablen erstellt! 
* Timer - Upload der Wetterstation: in Sekunden
* Timer - Download Vorhersage: ab 7:05 Uhr (da die API immer von 7 bis 7 rechnet) - Standard alle 12 Stunden (ist anpassbar in Stunden)
* Timer - Download Daten einer Station: In Minuten
* Es ist möglich die Rohdaten in einer eigenen Variable für die eigene Auswertung bereitzustellen
* Testfunktion innerhalb der Moduls um Download und Upload zu testen

# Version 2.1 31/03/2019
* Neu Bewölkung wird für die Detail Vorhersage bereitgestellt
* Neu der Download von Wetterdaten kann jetzt unabhänig vom Upload definiert werden. Hierfür eine andere WU ID festlegen (somit können Daten z.B. durch eine andere nahe Station angereichert werden)
* Neu bei dem Upload von Wetterdaten kann für den Wind jetzt m/s oder km/h gwählt werden (z.B. wenn man eine Homematic OC3 nutzt)

##WICHTIG:
* Beim Download werden teilweise seitens der API nicht alle Werte gefüllt (sind NULL) - in diesem Fall behält das Modul die alten Daten bei bei neue kommen. 
* Das kostenlose Limit für den Download von Daten liegt bei 1500 calls pro Tag oder 30 pro minute für den Download von Daten

Komplette Doku für Weather.com API: https://docs.google.com/document/d/1eKCnKXI9xnoMGRRzOL1xPCBihNV2rOet08qpE_gArAY/edit

Komplette Doku bei Wunderground: http://wiki.wunderground.com/index.php/PWS_-_Upload_Protocol
