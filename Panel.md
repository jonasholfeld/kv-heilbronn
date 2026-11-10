# Panel-Dokumentation

Diese Dokumentation richtet sich an Nutzer:innen des Kirby Panels. Sie erklärt, wie die Inhalte dieser Website im Panel gepflegt werden.

## Login

- Das Panel wird über `https://kvheilbronn.jholfeld.uber.space/panel` aufgerufen.
- Dort mit den persönlichen Zugangsdaten einloggen.
- Nach dem Login erscheint die Startansicht mit den vier Bereichen `Seiten`, `Startseite`, `Menü` und `Öffnungszeiten`.

## Änderungen speichern

- Änderungen sind erst gespeichert, wenn oben rechts auf `Speichern` geklickt wurde.
- Das gilt auch für Texte, Bilder, Farben, Toggles, Block-Inhalte und Verknüpfungen.
- Wenn vor dem Speichern die Sprache gewechselt oder die Seite verlassen wird, gehen ungespeicherte Änderungen nicht verloren, sie sind dann über "Changes" im linken Menü auffindbar.
- Ungespeicherte Änderungen werden durch orange Hinweis-Felder angezeigt.

## Die vier Tabs auf der Panel-Startseite

## Seiten

Hier werden die Hauptbereiche der Website geöffnet:

- Ausstellungen
- Reisen
- Termine
- Shop
- Kunstverein
- Impressum
- Besuch

Von hier aus gelangt man in die einzelnen Seiten und Unterseiten. Die Bereiche selbst sind fest angelegt und können nicht gelöscht werden.

## Startseite

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

- Die Überschrift einer Gruppe führt zum `Link der Überschrift`; ist dieser leer, zum ersten Eintrag der Gruppe.
- Bei `Ziel` → `Adresse` können auch Anker wie `/kunstverein#team` gesetzt werden. Der Anker (`team`) muss in einem Textblock der Zielseite eingetragen sein.

## Öffnungszeiten

Hier wird das Informations-Overlay auf der Startseite gepflegt.

Bearbeitet werden können:

- Adresse
- Text für geöffnete Tage
- Text für geschlossene Tage
- Öffnungszeiten für Montag bis Sonntag (z. B. `11–17 Uhr`; ein leeres Feld bedeutet „geschlossen“)

Gibt es in den nächsten 7 Tagen einen Termin, zeigt das Overlay diesen Tag und den Termin, sonst den heutigen Tag mit seiner Öffnungszeit. Die Öffnungszeiten je Wochentag gelten für beide Sprachen; auf der englischen Seite wird die Uhrzeit automatisch umgeschrieben (11 AM – 5 PM).

Die Öffnungszeiten im Footer und auf der Seite `Besuch` werden getrennt davon auf der Seite `Besuch` gepflegt.

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

- Diese Angaben müssen nur einmal gepflegt werden und können nur in Deutsch bearbeitet werden (in Englisch sind sie ausgegraut).
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

Ein Klick auf ein Bild öffnet seine Angaben:

- `Alt-Text (Bildbeschreibung)`
    - beschreibt in einem kurzen Satz, was auf dem Bild zu sehen ist – für Menschen, die das Bild nicht sehen können (Screenreader) oder wenn es nicht lädt
    - z. B. „Raumansicht mit drei großformatigen Gemälden in Rot- und Blautönen“
    - nicht mit „Bild von …“ beginnen, keine Fotocredits
    - der Alt-Text kann pro Sprache gepflegt werden
- `Credits`
    - Werkangaben und Fotocredits, eine Angabe pro Zeile; erscheinen in der Bildunterschrift
- `Darf neben einem anderen Bild stehen`
    - nur für Ausstellungsgalerien: zwei aufeinanderfolgende Hochformate werden nebeneinander gezeigt, wenn beide dies erlauben

Logos haben eigene Angaben:

- `Alt-Text`: der Name der Institution, z. B. „Stadt Heilbronn“
- `Link`: optional die Webseite der Institution, das Logo wird dann dorthin verlinkt

Ältere Logos, die vor dieser Unterscheidung hochgeladen wurden, zeigen noch die Bild-Angaben; dort gilt dasselbe (Name als Alt-Text, Link im Feld `Link (nur bei Logos)`).

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

Gerade bei Ausstellungen, Reisen, Editionen und Katalogen sollte dieser Bereich immer mitgeprüft werden.

## Unterseiten

## Startseite

Die Startseite selbst wird inhaltlich vor allem über die Panel-Startseite gepflegt:

- über `Startseite`
- über `Menü`
- über `Öffnungszeiten`

Zusätzlich fließen Inhalte aus:

- Ausstellungen
- Reisen
- Termine
- Shop

## Ausstellungen

Dies ist die Sammelseite für alle Ausstellungen.

Hier gibt es:

- einen Bereich für Entwürfe
- eine Tabelle der veröffentlichten Ausstellungen mit Künstler:in, Start und Ende; über `Filter` lässt sie sich durchsuchen

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

- `Künstler:in`
    - Name der beteiligten Künstler:in bzw. Künstler:innen
- `Farbe` (rechte Spalte `Darstellung`)
    - Hintergrund der Ausstellungsseite, Hover-Farbe in der Ausstellungsliste und Farbe der Startseite, solange die Ausstellung dort oben steht
- `Weiße Schrift`
    - schaltet auf helle Schrift um, bei dunklen Farben einschalten
- `Als Archiv-Ausstellung auf der Startseite möglich`
    - gibt es keine laufende oder keine kommende Ausstellung, zeigt die Startseite zufällig eine vergangene Ausstellung, bei der dies eingeschaltet ist
- `Eröffnung`
    - Datum und Uhrzeit der Eröffnung
- `Startdatum` und `Enddatum`
    - Zeitraum der Ausstellung
- `Beschreibung`
    - Hauptinhalt der Ausstellung
- `Zugehöriger Katalog` und `Zugehörige Edition`
    - verknüpfen passende Shop-Einträge, die auf der Ausstellungsseite als Buttons erscheinen

### Welche Sonderfunktionen gibt es? (Tab `Medien`)

- `Bilder auf der Startseite`
    - maximal 3 Bilder für die Startseitenvorschau; ohne Auswahl die ersten 3 Bilder der Galerie
- `Gefördert von`
    - Förderlogos
- `Kooperation`
    - zweite Logo-Gruppe mit frei wählbarer `Überschrift`, z. B. „In Kooperation mit“

## Reisen

Dies ist die Sammelseite für Reisen und Atelierbesuche.

### Wofür wird sie verwendet?

- für die Übersichtsseite aller Reisen

### Wo erscheinen die Änderungen im Frontend?

- auf der Reisen-Übersichtsseite
- indirekt auch bei einzelnen Reisen und Terminen

### Wie funktionieren die wichtigsten Felder?

- `Farbe der Seite`
    - Hintergrund der Seite Reisen und der Reisen-Box auf der Startseite
- `Farbe Kunstreisen`
    - Farbe für Reisen der Kategorie Kunstreise (Karten, Reiseseite, Termine)
- `Farbe Atelierbesuche`
    - Farbe für Reisen der Kategorie Atelierbesuch

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
    - wählt zwischen `Atelierbesuch` und `Kunstreise` und bestimmt damit die Farbe
- `Reisebeginn`
    - das Anmeldeformular ist bis zum Vortag geöffnet
- `Reiseende`
    - nur bei mehrtägigen Reisen; erscheint in den Suchergebnissen
- `Beschreibung`
    - Haupttext
- `Reiseplan`
    - eigener Abschnitt für Ablauf oder Programm
- `Anmeldung`
    - z. B. Preis und Anmeldeschluss; erscheint auf der Reiseseite und oben im Anmeldeformular

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
    - Hintergrund der Seite Termine und Farbe der Termine auf der Startseite

Die Tabelle zeigt für jeden Termin den Anfang der Beschreibung, das Datum und die Art der Veranstaltung.

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

- `Datum`, `Uhrzeit` und `Enddatum`
    - beim Anlegen werden nur Datum und Uhrzeit abgefragt; der Titel des Termins ist sein Datum
    - das `Enddatum` nur bei mehrtägigen Terminen, der Termin bleibt bis dahin in der Liste
- `Beschreibung`
    - erscheint auf der Seite Termine, auf der Startseite und im Overlay der Startseite
- `Art der Veranstaltung`
    - z. B. Führung, Vortrag, Eröffnung; steht über dem Termin
- `Schlagworte`
    - optional, erscheinen auf der Startseite in Großbuchstaben über dem Termin
- `Auf der Startseite anzeigen`
    - die Startseite zeigt die Termine des kommenden Monats
- `Verknüpfen mit`
    - einer Ausstellung oder einer Reise; danach erscheint die Auswahl der Ausstellung bzw. Reise

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

### Tabs `Bestellformular Editionen` und `Bestellformular Kataloge`

Die Formulare, die sich über `Bestellen` auf den Shop-Detailseiten öffnen: Editionen und Kataloge haben je ein eigenes Formular mit eigenen Einstellungen (Empfänger, Bestätigungs-E-Mail, …). Nur das Formular der Editionen kennt den Baustein `Editionsnummer`. Beschreibung, Bilder, Preise und Hinweis kommen aus dem jeweiligen Shop-Eintrag. Aufbau und Einstellungen: siehe [Formulare](#formulare).

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
    - Hintergrund der Shop-Seite und Farbe der Karte im Shop und auf der Startseite
- `Künstler:in`
    - Name zum Eintrag
- `Beschreibung`
    - Werkangaben wie Titel, Jahr, Technik, Maße und Auflage

### Welche Sonderfunktionen gibt es?

- `Galerie`
    - das erste Bild erscheint im Shop und auf der Startseite
    - erscheinen im Bestellformular als auswählbare Editionsnummern (#1 = erstes Bild)
- Tab `Bestellung`
    - `Preisoptionen`: Auswahl im Bestellformular, z. B. „800 Euro (ohne Rahmen)“
    - `Hinweis im Bestellformular`: z. B. welche Nummern bereits verkauft sind

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
    - Hintergrund der Shop-Seite und Farbe der Karte im Shop und auf der Startseite
- `Künstler:in`
    - Name zum Eintrag
- `Beschreibung`
    - Angaben wie Herausgeber, Umfang, Format und ISBN

### Welche Sonderfunktionen gibt es?

- `Galerie`
    - das erste Bild erscheint im Shop und auf der Startseite
- Tab `Bestellung`
    - `Preisoptionen`: Auswahl im Bestellformular der Kataloge, z. B. „25 Euro“

## Besuch

Diese Seite wird für Kontakt- und Besuchsinformationen verwendet.

### Wofür wird sie verwendet?

- für die Seite `Besuch`
- für Kontakt- und Öffnungszeitenbereiche

### Wo erscheinen die Änderungen im Frontend?

- auf der Seite `Besuch`
- `Kontakt` und `Öffnungszeiten` auch im Footer jeder Seite

### Wie funktionieren die wichtigsten Felder?

- `Farbe`
    - Seitenfarbe
- `Kontakt`
    - Kontaktinformationen
- `Öffnungszeiten`
    - Öffnungszeiten für die Besuchsseite
- `Anfahrt`
    - Anfahrtsinformationen
- `Ansprechpartner:innen`
    - zuständige Kontaktpersonen
- `Vorschautext in der Suche`
    - erscheint im Suchergebnis der Seite

## Kunstverein

Diese Seite wird für Informationen über den Kunstverein verwendet.

### Wofür wird sie verwendet?

- für die Seite `Kunstverein`

### Wo erscheinen die Änderungen im Frontend?

- auf der Seite `Kunstverein`

### Wie funktionieren die wichtigsten Felder?

- `Farbe`
    - Seitenfarbe
- `Textblöcke`
    - inhaltliche Blöcke der Seite; über den `Anker` eines Textblocks kann das Menü direkt auf ihn verlinken (z. B. `/kunstverein#team`). Die Startseite verlinkt auf den Anker `mitgliedschaft`.
- `Bilder`
    - Bildbereich der Seite
- `Satzung (PDF)`
    - Datei-Link zur Satzung, auch im Footer

### Welche Sonderfunktionen gibt es?

- Die Inhalte werden hier blockweise aufgebaut.
- Wenn eine Satzung hinterlegt ist, erscheint ein eigener Link zur PDF.
- `Satzung` und der Button zum Mitgliedsantrag stehen am unteren Rand der Textspalte und bleiben beim Scrollen sichtbar (auf dem Handy: eigene Boxen am Ende der Textblöcke).

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
- `Förderung`
    - `Überschrift der Förderer` und `Logos der Förderer` (Reihenfolge wie auf der Website; Name der Institution als Alt-Text)

## Formulare

Es gibt drei Formulare, die gleich aufgebaut sind und sich als Overlay über der Seite öffnen:

- `Bestellformular Editionen` / `Bestellformular Kataloge`: Shop → gleichnamige Tabs, Button `Bestellen` auf den Detailseiten der Editionen bzw. Kataloge
- `Mitgliedsantrag`: Kunstverein → Tab `Mitgliedsantrag`, Button unten in der Textspalte neben `Satzung`
- `Anmeldeformular`: Reisen → Tab `Anmeldeformular`, Button `Anmeldung` auf jeder Reise, deren Reisebeginn in der Zukunft liegt; über den Feldern stehen Titel, Datum und der Text `Anmeldung` der Reise

### Formularfelder

- Felder hinzufügen, entfernen und per Drag & Drop anordnen
- `Gruppe`: farbiger Kasten mit Überschrift und eigenen Feldern (z. B. `Zahlungsweise`); mit `Mindestens ein Feld ausfüllen` muss eines der Felder darin ausgefüllt sein (z. B. `Personenanzahl`)
- `Textfeld`: die Bezeichnung steht als Platzhalter im Feld und verschwindet beim Tippen
    - `Art`: `Name` füllt den Platzhalter {name}, das erste Feld `E-Mail` erhält die Bestätigungs-E-Mail; `Telefon`, `Datum` (mit optionalem `Mindestalter`), `Anzahl`, `IBAN` und `BIC` werden auf Gültigkeit geprüft
    - `Breite`: ganze oder halbe Breite (zwei halbe Felder stehen nebeneinander), `Schmal` oder `Wie die Bezeichnung`
- `Textbereich`: mehrzeiliges Feld; `Großes Feld` für eine Nachricht
- `Auswahl`: genau eine Option (z. B. Anrede); `Untereinander` stapelt die Optionen (z. B. Mitgliedsart); Optionen werden mit Enter oder `;` getrennt
- `Einwilligung`: Text mit Haken, Links über das Link-Werkzeug; ohne `Pflichtfeld` freiwillig (z. B. Newsletter); die `Bezeichnung` erscheint in der E-Mail
- `Datei-Upload`: PDF, JPG oder PNG bis 5 MB, wird an die E-Mail angehängt
- `Text`: erklärender Text zwischen den Feldern
- `Abstand`: größerer Abstand zwischen zwei Feldern
- nur im Shop: `Editionsauswahl` (Bilder der Galerie als #1, #2, … zum Anklicken, darüber die `Erklärung`) und `Preisauswahl` (Preisoptionen des Shop-Eintrags)
- Pflichtfelder und Gruppen mit `Mindestens ein Feld ausfüllen` erhalten automatisch ein `*`
- Fehler werden direkt beim Ausfüllen unter dem Feld angezeigt und vor dem Versand noch einmal geprüft

### Einstellungen

- `Überschrift des Formulars`, Beschriftung des öffnenden und des Absende-Buttons, `Text über den Feldern`
- `Empfänger`: an diese Adresse geht jedes ausgefüllte Formular; mit `Antworten` schreibt man direkt der Absender:in
- `Meldung nach erfolgreichem Absenden` / `Meldung bei einem Fehler`: erscheinen anstelle des Formulars; bei Fehlern wird darunter aufgelistet, was schiefgelaufen ist
- `Betreff` und `Text der Bestätigungs-E-Mail` an die Absender:in; Platzhalter: `{name}`, `{titel}` (Shop-Eintrag, Reise, …), `{angaben}` (alle Angaben, IBAN gekürzt)
- Für die englische Seite alle Texte und Felder in der englischen Sprachversion pflegen; solange sie fehlen, erscheint dort das deutsche Formular

## Praktische Hinweise

- Nach Änderungen immer speichern.
- Bei zweisprachigen Inhalten immer beide Sprachen prüfen.
- Bei Ausstellungen, Reisen und Terminen immer auch Datumsangaben kontrollieren.
- Bei jedem Bild Alt-Text und Credits ergänzen, bei Logos den Namen der Institution als Alt-Text.
- Bei Startseiten-Inhalten immer zusätzlich prüfen, ob die Änderungen auch wirklich im richtigen Bereich erscheinen.
