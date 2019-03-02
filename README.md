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

# Version 1.0 12/02/2018
* Upload von Wetterdaten an Wunderground
* Anmeldung an Wunderground via Station ID und API Key
* Auswahl von diversen Wetterdaten
* Upload Konfigurierbar in Schritten von Sekunden

# Version 2.0 02/03/2019 (aktuell noch beta)
* Einbindung der neuen Weather.com API seitens Wunderground für Uploader von Wetterdaten
* Anmeldung an der neuen API durch neuen Key (muss bei auf Wunderground erstellt werden https://www.wunderground.com/member/api-keys)
* Download von Übersichtswetterdaten für bis zu 5 Tage
* Download von Detailwetterdaten in 12h Segmenten (7am - 7pm Tag / 7pm - 7am Nacht) - Timer läuft immer um 7 und 19 Uhr um Daten abzurufen
* Detailwetterdaten können konfiguriert werden z.B. nur Wind, Niederschlag usw.
* Vorsicht ... wenn alles ausgewählt ist werden über 200 Variablen erstellt! 
* Testfunktion innerhalb der Moduls um Download und Upload zu testen

WICHTIG Beim Download werden teilweise seitens der API nicht alle Werte gefüllt (sind NULL) - dieser Fehler wird bewusst aktuell nicht abgefangen - es kommen also evtl. ein paar Fehler ins Log. 


## Wo finde ich Informationen ob das Modul funktioniert
Das Modul postet Informationen in die Debugübersicht des Moduls und nicht in Log (Stand V1.0). Dort sieht man wie die Werte aktualisiert werden und ob der Upload funktioniert. In Wunderground werden die Werte übrigens nicht ständig aktualisiert, somit nicht wundern wenn nicht ständig neue Werte in der Tabelle der Wetterstation auftauchen.

Komplette Doku für Weather.com API: https://docs.google.com/document/d/1eKCnKXI9xnoMGRRzOL1xPCBihNV2rOet08qpE_gArAY/edit

Komplette Doku bei Wunderground: http://wiki.wunderground.com/index.php/PWS_-_Upload_Protocol
