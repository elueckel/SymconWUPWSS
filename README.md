# Symcon - Wunderground PWS Sync Modul
Diese Modul erlaubt den Upload von Wetterdaten einer eigenen Wetterstation via Symcon an Wunderground.

## Funktionsumfang
Upload von diversen Wetterdaten an Wunderground (setzt das vorherige einrichten einer PWS - Personal Weather Station) innerhalb von Wunderground voraus. Sind Werte nicht gesetzt überspringt das Modul diese - es müssen nicht alle Werte geladen werden.

## Voraussetzungen
IP-Symcon ab Version 4.x

## Software-Installation
Über das Modul-Control folgende URL hinzufügen.
https://github.com/elueckel/SymconWUPWSS

## Einrichten der Instanzen in IP-Symcon
Unter "Instanz hinzufügen" ist das 'WundergroundPWSSync'-Modul unter dem Hersteller '(Sonstige)' aufgeführt.

## Konfigurationsseite:

* WU ID: Name der Wetterstation, z.B. IHESSENB46
* WU Passwort: Passwort welches für den Wunderground Account hinterlegt wurde

Felder in Version 1.0
* Temperatur Aussen in C (wind in Fahrenheit im Modul umgerechnet)
* Luftfeuchtigkeit in %
* Taupunkt in C (wird in Fahrenheit im Modul umgerechnet)
* Windrichtung in Grad
* Wind - Durchschnitt in m/s (wird im Modulumgerechnet in mph)
* Wind - Böen in m/s (wird im Modulumgerechnet in mph)
* Regen letzte Stunde in mm (wird umgerechnet in inch)
* Regen letzte 24 in mm (wird umgerechnet in inc)
* Luftdruck in HPA (wird in BPI im Modul umgerechnet)
* UV Index (1-12)
* Update Timer, in Sekunden (wie oft Daten an WU übermittelt werden)

## Wo finde ich Informationen ob das Modul funktioniert
Das Modul postet Informationen in die Debugübersicht des Moduls und nicht in Log (Stand V1.0). Dort sieht man wie die Werte aktualisiert werden und ob der Upload funktioniert. In Wunderground werden die Werte übrigens nicht ständig aktualisiert, somit nicht wundern wenn nicht ständig neue Werte in der Tabelle der Wetterstation auftauchen.

Komplette Doku bei Wunderground: http://wiki.wunderground.com/index.php/PWS_-_Upload_Protocol
