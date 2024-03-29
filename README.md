# Symcon - Wunderground PWS Sync & Vorhersage Modul (new WU API)

Dieses Modul erlaubt basierend auf der neuen Wunderground/Weather.com API

* den Upload von Wetterdaten einer eigenen Wetterstation
* den Download von Daten ein Wetterstation anhand der Station ID
* Download der Vorhersage grob (Übersicht maximal 5 Tage), anhand von Geodaten
* Download der Vorhersage detailiert (in 12 Stunden Segmenten - maximal 5 Tage), anhand von Geodaten

## Benötigte Dinge
- Account bei Wunderground inkl einer dort angelegten Wetterstation!
- Einen neuen API Key https://www.wunderground.com/member/api-keys
- Für den Upload mit Station ID und Key anmelden (kann bei Station im WU Portal nachgesehen werden)

WICHTIG: Die Seite für die Erstellung des API Keys ist erst noch dem Upload von Daten (die aktuelle Aussentemperatur reicht) verfügbar. Stand März 2019 kommt man auf den API Link NUR durch den Link - es gibt noch keinen Aufruf auf der Website.

## Voraussetzungen
IP-Symcon ab Version 5.1

## Software-Installation
Über das Modul-Control folgende URL hinzufügen.
https://github.com/elueckel/SymconWUPWSS

## Einrichten der Instanzen in IP-Symcon
Unter "Instanz hinzufügen" ist das 'WundergroundPWSSync'-Modul unter dem Hersteller '(Sonstige)' aufgeführt.

### Version 1.0 12/02/2018
* Upload von Wetterdaten an Wunderground
* Anmeldung an Wunderground via Station ID und API Key
* Auswahl von diversen Wetterdaten
* Upload Konfigurierbar in Schritten von Sekunden

### Version 2.0 17/03/2019
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

### Version 2.1 31/03/2019
* Neu Bewölkung wird für die Detail Vorhersage bereitgestellt
* Neu der Download von Wetterdaten kann jetzt unabhänig vom Upload definiert werden. Hierfür eine andere WU ID festlegen (somit können Daten z.B. durch eine andere nahe Station angereichert werden)
* Neu bei dem Upload von Wetterdaten kann für den Wind jetzt m/s oder km/h gwählt werden (z.B. wenn man eine Homematic OC3 nutzt)
* UI aufgeräumt
* Bugfixes

### Version 2.1.1 07-04-2019
* Bugfix beim Download von einer anderen Station

### Version 2.1.2 12-04-2019
* Change Timer für Forecast nun intern - läuft alle x-Stunden (Grund - damit das Modul in den Module Store kann). Theoretisch sollte ein existierender  externer Timer gelöscht werden / falls nicht bitte manuell tun.
* Bugfix Profil für aktuellen Wetter Download jetzt auf kmh gesetzt

### Version 2.1.3 12-04-2019
* Change Timer für CURL Abfragen auf 10 Sekunden gesetzt

### Version 2.1.4 04-06-2019
* [B] Letzte Version für 4.x aufwärts - solltest Du 4.x einsetzen und NICHT über den Module Store updaten dann bitte den Prod 4.x Branch verwenden, wobei dieser eigentlich keine Update mehr erhalten wird und für 5.x weiter entwickelt wird - V2.2. [/B]
* New Symcon 4.x Branch https://github.com/elueckel/SymconWUPWSS/tree/Production-4.x
* Fix upload von Regenmenge (Kommafehler)
* Change - Upload verbessert (Credit Brovning)

### Version 2.2 06-06-2019
* Der Ort für die Wetterstation kann jetzt in der WebConsole über das Location Modul gewählt werden. In der Classic Konsole ist die Eingabe nicht so komfortabel, aber es stehen jeweils ein Feld für Längen und Breitengrad zur Verfügung. [B] WICHTIG - für bestehende Installationen des Moduls muss der Längen und Breitengrad nochmals eingegeben werden, da die alten Werte nicht übernommen werden!!! [/b]
* Der Download von Wetterdaten einer anderen Station wurde überarbeitet und sollte jetzt keine Probleme mehr machen wenn keine Werte kommen.

### Version 2.2.1 08-09-2019
* Diverse Bugfixes in Bezug auf den Upload von Daten und auch Anpassungen der Beschriftung

### Version 2.3 29-04-2020
* Es gibt nun die Möglichkeit die Regenmenge aus der 5 Tages-Vorhersage zu kumulieren, also Tag 1 + Tag 2 usw. um z.B. die zukünftige Wassermenge zur Bewässerung des Rasens durch eine Bewässerungssteuerung basierend auf der Wettervorhersage zu bestimmen.

### Version 2.31 23-05-2020
* Fix - Taupunkt wird jetzt korrekt für ein gewählte Wetterstation geladen

### Version 3.0 19-12-2020
* Neu - Komplett neue Öberfläche für die Konfiguration des Moduls
* Code Cleanup 

### Version 3.1 07-04-2021
* Neu - Darstellung der Vorhersage Icons als Medienelementen z.B. zur Verwendung mit IPSView

### Version 3.1.1 25-05-2021
* Fix - Regenmenge aufaddieren hat nicht funktioniert

### Version 3.1.2 06-06-2022
* Fix - UV Wert wurde nicht geladen

### Version 3.2 08-02-2023
* Neu - Es ist möglich die Geolokation im Objektbaum zu setzen

### Version 3.21 21-03-2023
* Fix - Übersetzung Wetter Download Variablen

### Version 3.22 27-08-2023
* Neu - Icons werden als Medienobjekte angezeigt und können somit einfach ins Webfront übernommen werden
* Neu - String Variablen werden mit dem ~TextBox Profil angelegt
* Fix - Upload von Wetterdaten
* Fix - Wenn kumulieren von Regenmengen nicht aktivert war, gab es einen Fehler beim Download der Wetterdaten

## WICHTIG:
* Beim Download werden teilweise seitens der API nicht alle Werte gefüllt (sind NULL) - in diesem Fall behält das Modul die alten Daten bei bei neue kommen.
* Das kostenlose Limit für den Download von Daten liegt bei 1500 calls pro Tag oder 30 pro minute für den Download von Daten

Komplette Doku für Weather.com API: https://docs.google.com/document/d/1eKCnKXI9xnoMGRRzOL1xPCBihNV2rOet08qpE_gArAY/edit

Komplette Doku bei Wunderground: http://wiki.wunderground.com/index.php/PWS_-_Upload_Protocol
