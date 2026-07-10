# Panel-Dokumentation

Diese Dokumentation richtet sich an Nutzer:innen des Kirby Panels. Sie erklärt, wie die Inhalte dieser Website im Panel gepflegt werden.

## Login

- Das Panel wird über `https://kvheilbronn.jholfeld.uber.space/panel` aufgerufen.
- Dort mit den persönlichen Zugangsdaten einloggen.
- Nach dem Login erscheint die Startansicht mit den vier Bereichen `Inhalt`, `Homepage Elements`, `Menü` und `Startseite Overlay`.

## Änderungen speichern

- Änderungen sind erst gespeichert, wenn oben rechts auf `Speichern` geklickt wurde.
- Das gilt auch für Texte, Bilder, Farben, Toggles, Block-Inhalte und Verknüpfungen.
- Wenn vor dem Speichern die Sprache gewechselt oder die Seite verlassen wird, gehen ungespeicherte Änderungen nicht verloren, sie sind dann über "Changes" im linken Menü auffindbar.
- Ungespeicherte Änderungen werden durch orange Hinweis-Felder angezeigt.

## Die vier Tabs auf der Panel-Startseite

## Inhalt

Hier werden die Hauptbereiche der Website geöffnet:

- Startseite
- Ausstellungen
- Reisen
- Termine
- Shop
- Kunstverein
- Impressum
- Besuch

Von hier aus gelangt man in die einzelnen Seiten und Unterseiten.

## Homepage Elements

Hier werden die zusätzlichen Inhaltselemente der Startseite gepflegt:

- `Mitgliedschaft`
    - Titel der Box
    - Text der Box
    - Farbe der Box
- `Reisen`
    - Titel der Reise-Box
    - Text der Reise-Box
- `Edition`
    - Auswahl eines Shop-Eintrags, der auf der Startseite gezeigt wird
- `Katalog`
    - Auswahl eines Shop-Eintrags, der auf der Startseite gezeigt wird

Kurz gesagt:

- Hier wird gesteuert, welche Infoboxen und welche Shop-Vorschauen auf der Startseite erscheinen.

## Menü

Hier wird das große Website-Menü gepflegt.

Das Menü ist in Gruppen aufgebaut:

- jede Gruppe hat eine Überschrift
- jede Gruppe kann mehrere Einträge enthalten
- Einträge können auf interne Seiten, auf Anker innerhalb der Website oder auf externe Links verweisen

Wichtig:

- Wenn eine Gruppe nur einen Eintrag hat, wirkt die Gruppenüberschrift im Frontend direkt wie ein Link.
- Über freie URLs können auch Anker wie `/kunstverein#team` gesetzt werden.

## Startseite Overlay

Hier wird das Informations-Overlay auf der Startseite gepflegt.

Bearbeitet werden können:

- Adresse
- Text für regulär geöffnete Tage
- Text für regulär geschlossene Tage
- Öffnungszeiten für Montag bis Sonntag

Dieser Bereich ist vor allem für die allgemeinen Hinweise und die Tageslogik des Overlays wichtig.

## Seiten und Unterseiten

## Grundprinzip

Einige Bereiche sind Sammelseiten. Darunter liegen die eigentlichen Einträge:

- unter `Ausstellungen` liegen einzelne Ausstellungen
- unter `Reisen` liegen einzelne Reisen
- unter `Termine` liegen einzelne Termine
- unter `Shop` liegen Editionen und Kataloge

## Entwurf und veröffentlicht

- Neue Einträge können zunächst als `Entwurf / Draft` angelegt werden.
- Entwürfe sind noch nicht live sichtbar.
- Erst veröffentlichte Seiten erscheinen auf der Website.

## Neue Unterseiten anlegen

- Zuerst die passende Sammelseite öffnen, zum Beispiel `Ausstellungen`.
- Dort über `+` einen neuen Eintrag anlegen.
- Danach die Inhalte ausfüllen, speichern und bei Bedarf veröffentlichen (Button oben rechts: Public / Draft / Listed).

## Sprachumschaltung im Panel

Die Website ist zweisprachig aufgebaut.

- Im Panel kann oben zwischen `Deutsch` und `English` gewechselt werden.
- Texte müssen in jeder Sprache einzeln gepflegt werden.
- Darum nach einer Änderung in Deutsch immer prüfen, ob die englische Version ebenfalls ergänzt oder aktualisiert werden muss.

## Nicht übersetzte Felder

Einige Felder gelten für beide Sprachen gleichzeitig. Dazu gehören vor allem:

- Farben
- Bilder und Dateiauswahl
- Datumsangaben
- Schalter wie Toggles
- Verknüpfungen zu anderen Seiten

Das bedeutet:

- Diese Angaben müssen nur einmal gepflegt werden und können nur in Deutsch bearbeitet werden.
- Eine Änderung gilt dann automatisch für Deutsch und Englisch.

## Wichtige Sonderfunktionen

## Weiße Schrift

Dieses Feld gibt es bei Ausstellungen.

- Wenn `Weiße Schrift` aktiviert ist, wird die Ausstellung mit heller Schrift dargestellt.
- Das ist vor allem bei dunklen oder kräftigen Hintergrundfarben sinnvoll.

## Farben

Farbfelder sind in dieser Website besonders wichtig.

- Sie steuern das Erscheinungsbild einzelner Seiten oder Bereiche.
- Farben werden unter anderem bei Ausstellungen, Reisen, Terminen, Besuch, Impressum, Kunstverein und in den Startseiten-Boxen verwendet.

## Logos

Bei Ausstellungen gibt es zwei verschiedene Logo-Bereiche:

- Logos mit der festen Bedeutung `Gefördert von`
- Logos mit frei definierbarem Begleittext, zum Beispiel `In Kooperation mit`

Zusätzlich kann bei den Logos ein eigener Text über dem Logo-Bereich eingetragen werden.

## Bilder und Bildangaben

Bei hochgeladenen Bildern und Dateien können zusätzliche Angaben gepflegt werden:

- Alt-Text
- Titel
- Bildunterschrift
- Credits
- Link-URL

Diese Angaben sind wichtig, weil sie im Frontend an verschiedenen Stellen sichtbar sein können.

Bei Ausstellungen gibt es außerdem die Funktion:

- `Kann als Bild-Paar dargestellt werden`

Damit kann gesteuert werden, ob Bilder gemeinsam als Paar angezeigt werden dürfen, wenn zwei aufeinander folgende Hochformate dieses Feld auf "on" gestellt haben.

## Block-Editoren

Einige Inhalte werden nicht in einem einfachen Textfeld, sondern in einem Block-Editor gepflegt.

Das betrifft vor allem:

- die Beschreibung bei Ausstellungen
- die Textblöcke auf der Seite `Kunstverein`
- das Menü auf der Panel-Startseite

Hier können Inhalte flexibel in Blöcken aufgebaut und sortiert werden.

### So werden neue Blocks hinzugefügt

- Im jeweiligen Block-Feld auf `Hinzufügen` beziehungsweise auf das `+` klicken.
- Danach den gewünschten Blocktyp auswählen.
- Je nach Bereich stehen unterschiedliche Blocktypen zur Verfügung.
- In Ausstellungen wird die Beschreibung über Text-Blöcke aufgebaut.
- Auf der Seite `Kunstverein` können Textblöcke und weitere Inhaltsblöcke angelegt werden.
- Im Menü werden Menü-Gruppen und darin wiederum Menü-Einträge als Blöcke gepflegt.

### So werden Blocks angeordnet

- Blocks können im Panel per Drag and Drop verschoben werden.
- Dazu den gewünschten Block anklicken und dann an der Greiffläche (kleiner Button mit 6 Punkten) anfassen und an die gewünschte Position ziehen.
- Die neue Reihenfolge sollte anschließend gespeichert werden.

### So werden bestehende Blocks bearbeitet

- Jeder Block kann direkt im Block-Editor geöffnet und inhaltlich bearbeitet werden.
- Einzelne Blocks können bei Bedarf auch gelöscht oder dupliziert werden.
- Nach jeder Änderung muss gespeichert werden.

## Medien-Tabs

Bei mehreren Seitentypen gibt es eigene Tabs für Medien.

Dort werden zum Beispiel gepflegt:

- Galerien
- Logos
- weitere Bildauswahlen

Gerade bei Ausstellungen, Reisen, Terminen, Editionen und Katalogen sollte dieser Bereich immer mitgeprüft werden.

## Unterseiten

## Startseite

Die Startseite selbst wird inhaltlich vor allem über die Panel-Startseite gepflegt:

- über `Homepage Elements`
- über `Menü`
- über `Startseite Overlay`

Zusätzlich fließen Inhalte aus:

- Ausstellungen
- Reisen
- Termine
- Shop

## Ausstellungen

Dies ist die Sammelseite für alle Ausstellungen.

Hier gibt es:

- einen Bereich für Entwürfe
- einen Bereich für bestehende Ausstellungen

## Ausstellung

Diese Seite wird für eine einzelne Ausstellung verwendet.

### Wofür wird sie verwendet?

- für die Ausstellungsdetailseite
- für die Ausstellungsübersicht
- für Ausstellungs-Vorschauen auf der Startseite

### Wo erscheinen die Änderungen im Frontend?

- auf der Detailseite der Ausstellung
- in der Übersicht `Ausstellungen`
- teilweise auf der Startseite
- bei Verknüpfungen mit Terminen

### Wie funktionieren die wichtigsten Felder?

- `Auf Startseite anzeigen`
    - wichtig für ältere Ausstellungen, die trotzdem auf der Startseite erscheinen dürfen
- `Künstler:in`
    - Name der beteiligten Künstler:in bzw. Künstler:innen
- `Farbe`
    - bestimmt die Darstellungsfarbe der Ausstellung
- `Weiße Schrift`
    - schaltet auf helle Schrift um
- `Jahr`
    - wichtig für Einordnung und Filter
- `Eröffnung`
    - Datum und Uhrzeit der Eröffnung
- `Startdatum` und `Enddatum`
    - Zeitraum der Ausstellung
- `Beschreibung`
    - Hauptinhalt der Ausstellung
- `Katalog` und `Edition`
    - verknüpfen passende Shop-Einträge

### Welche Sonderfunktionen gibt es?

- `Homepage-Bilder`
    - damit kann gezielt ausgewählt werden, welche Bilder in der Startseitenvorschau erscheinen
- `Logos`
    - Bereich für Förderlogos
- `Logos mit freiem Text`
    - Bereich für Kooperationslogos oder andere frei beschriftete Logo-Gruppen
- `Text für Kooperation`
    - Überschrift über der zweiten Logo-Gruppe

## Reisen

Dies ist die Sammelseite für Reisen und Atelierbesuche.

### Wofür wird sie verwendet?

- für die Übersichtsseite aller Reisen

### Wo erscheinen die Änderungen im Frontend?

- auf der Reisen-Übersichtsseite
- indirekt auch bei einzelnen Reisen und Terminen

### Wie funktionieren die wichtigsten Felder?

- `Farbe`
    - Grundfarbe des Bereichs
- `Farbe Kunstreise`
    - Farbe für Einträge der Kategorie Kunstreise
- `Farbe Atelierbesuch`
    - Farbe für Einträge der Kategorie Atelierbesuch

### Welche Sonderfunktionen gibt es?

- Die beiden Kategoriefarben werden von den einzelnen Reisen übernommen.

## Reise

Diese Seite wird für eine einzelne Reise oder einen Atelierbesuch verwendet.

### Wofür wird sie verwendet?

- für die Detailseite einer Reise
- für die Reiseübersicht

### Wo erscheinen die Änderungen im Frontend?

- auf der einzelnen Reise-Seite
- in der Übersicht `Reisen`
- bei Verknüpfungen aus Terminen

### Wie funktionieren die wichtigsten Felder?

- `Kategorie`
    - wählt zwischen `Atelierbesuch` und `Kunstreise`
- `Reise-Datumstext`
    - freier Datumstext
- `Reisebeginn` und `Reiseende`
    - zeitliche Einordnung
- `Beschreibung`
    - Haupttext
- `Reiseplan`
    - eigener Abschnitt für Ablauf oder Programm
- `Anmeldung`
    - eigener Abschnitt für Anmeldeinformationen

### Welche Sonderfunktionen gibt es?

- `Galerie`
    - weitere Bilder
- Wenn keine Bilder vorhanden sind, wird der Inhalt anders auf der Seite angeordnet.

## Termine

Dies ist die Sammelseite für alle Termine.

### Wofür wird sie verwendet?

- für die Terminübersicht
- als Grundlage für Terminmodule auf der Startseite

### Wo erscheinen die Änderungen im Frontend?

- auf der Seite `Termine`
- auf der Startseite
- im Startseiten-Overlay

### Wie funktionieren die wichtigsten Felder?

- `Farbe`
    - Grundfarbe des Terminbereichs

## Termin

Diese Seite wird für einen einzelnen Termin verwendet.

### Wofür wird sie verwendet?

- für Veranstaltungslisten
- für Terminvorschauen auf der Startseite
- für Hinweise im Startseiten-Overlay

### Wo erscheinen die Änderungen im Frontend?

- in der Terminübersicht
- auf der Startseite
- bei Verlinkungen zu Ausstellungen oder Reisen

### Wie funktionieren die wichtigsten Felder?

- `Veranstaltungskategorie`
    - sichtbare Bezeichnung des Termintyps
- `Auf der Startseite anzeigen`
    - legt fest, ob der Termin auf der Startseite erscheint
- `Veranstaltungszeit`
    - Uhrzeit des Termins
- `Zugeordneter Bereich`
    - entscheidet, ob der Termin zu einer Ausstellung oder Reise gehört
- `Beschreibungstext`
    - kurzer Inhaltstext
- `Kalender`
    - zusätzliche Schlagwörter oder Labels
- `Startdatum` und `Enddatum`
    - zeitliche Einordnung
- `Zugehörige Ausstellung`
    - Verknüpfung mit einer Ausstellung
- `Zugehörige Reise`
    - Verknüpfung mit einer Reise
- `Notiz`
    - zusätzlicher interner Text

### Welche Sonderfunktionen gibt es?

- Termine können direkt mit Ausstellungen oder Reisen verknüpft werden.
- Diese Verknüpfung ist wichtig für die Weiterleitung und die Darstellung im Frontend.

## Shop

Dies ist die Sammelseite für Shop-Inhalte.

### Wofür wird sie verwendet?

- für die Übersichtsseite aller Editionen und Kataloge

### Wo erscheinen die Änderungen im Frontend?

- auf der Shop-Übersicht
- teilweise auf der Startseite

## Edition

Diese Seite wird für einen einzelnen Editions-Eintrag verwendet.

### Wofür wird sie verwendet?

- für Shop-Detailseiten
- für Vorschauen im Shop
- optional für die Startseite

### Wo erscheinen die Änderungen im Frontend?

- im Shop
- eventuell auf der Startseite

### Wie funktionieren die wichtigsten Felder?

- `Farbe`
    - Darstellungsfarbe des Eintrags
- `Künstler:in`
    - Name zum Eintrag
- `Beschreibung`
    - Inhalt des Eintrags

### Welche Sonderfunktionen gibt es?

- `Galerie`
    - Bilder für Vorschau und Detailseite

## Katalog

Diese Seite wird für einen einzelnen Katalog-Eintrag verwendet.

### Wofür wird sie verwendet?

- für Shop-Detailseiten
- für Vorschauen im Shop
- optional für die Startseite

### Wo erscheinen die Änderungen im Frontend?

- im Shop
- eventuell auf der Startseite

### Wie funktionieren die wichtigsten Felder?

- `Farbe`
    - Darstellungsfarbe des Eintrags
- `Künstler:in`
    - Name zum Eintrag
- `Beschreibung`
    - Inhalt des Eintrags

### Welche Sonderfunktionen gibt es?

- `Galerie`
    - Bilder für Vorschau und Detailseite

## Besuch

Diese Seite wird für Kontakt- und Besuchsinformationen verwendet.

### Wofür wird sie verwendet?

- für die Seite `Besuch`
- für Kontakt- und Öffnungszeitenbereiche

### Wo erscheinen die Änderungen im Frontend?

- auf der Seite `Besuch`
- in Teilen des Footers
- teilweise im Startseiten-Overlay

### Wie funktionieren die wichtigsten Felder?

- `Farbe`
    - Seitenfarbe
- `Kontakt`
    - Kontaktinformationen
- `Öffnungszeiten`
    - Öffnungszeiten für die Besuchsseite
- `Anfahrt`
    - Anfahrtsinformationen
- `Ansprechpartner`
    - zuständige Kontaktpersonen

## Kunstverein

Diese Seite wird für Informationen über den Kunstverein verwendet.

### Wofür wird sie verwendet?

- für die Seite `Kunstverein`

### Wo erscheinen die Änderungen im Frontend?

- auf der Seite `Kunstverein`

### Wie funktionieren die wichtigsten Felder?

- `Farbe`
    - Seitenfarbe
- `Galerie`
    - Bildbereich der Seite
- `Textblöcke`
    - inhaltliche Blöcke der Seite
- `Satzung PDF`
    - Datei-Link zur Satzung

### Welche Sonderfunktionen gibt es?

- Die Inhalte werden hier blockweise aufgebaut.
- Wenn eine Satzung hinterlegt ist, erscheint ein eigener Link zur PDF.

## Impressum

Diese Seite wird für rechtliche Inhalte verwendet.

### Wofür wird sie verwendet?

- für Impressum und Datenschutz

### Wo erscheinen die Änderungen im Frontend?

- auf der Seite `Impressum`
- über die Links im Menü

### Wie funktionieren die wichtigsten Felder?

- `Farbe`
    - Seitenfarbe
- `Impressum`
    - Inhalt des Impressums
- `Datenschutz`
    - Inhalt der Datenschutzerklärung

## Praktische Hinweise

- Nach Änderungen immer speichern.
- Bei zweisprachigen Inhalten immer beide Sprachen prüfen.
- Bei Ausstellungen, Reisen und Terminen immer auch Datumsangaben kontrollieren.
- Bei Bildern nach Möglichkeit Alt-Text und weitere Bildangaben ergänzen.
- Bei Startseiten-Inhalten immer zusätzlich prüfen, ob die Änderungen auch wirklich im richtigen Bereich erscheinen.
