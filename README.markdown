## Stud.IP GlobalSearchPlugin

Bietet endlich eine globale Suche f�r Stud.IP. Der Nutzer tippt und erh�lt alles, wonach er gesucht hat. Gefunden werden 
* Veranstaltungen 
* Dokumente 
* Nutzer 
* R�ume 
* Forenpostings 
gefunden.

Weitere Features:
* Suchergebnisse als unendliche Liste (l�dt sich selbst nach, wenn der Nutzer nach unten scrollt)
* Einschr�nkung (Filter) nach Studienbereichen
* Einschr�nkung (Filter) nach Typ - auch kombinierbar mit Studienbereichen
* Alle Ergebnisse mit korrekten Sichtbarkeitseinstellungen. Man findet nur Dokumente in �ffentlichen und den eigenen Veranstaltungen.
* Quicklink-Funktionen in der rechten-oberen Ecke der Suchergebnisse, damit Admins zum Beispiel schnell Termine oder Zeiten/R�ume der Veranstaltungen bearbeiten k�nnen.
* Dieses Plugin ist selbst durch Plugins erweiterbar durch Verwendung des NotificationCenters.
* CLI-Skript zum n�chtlichen Berechnen des Suchindexes.

### CLI-Skript zum Indexberechnen

Das Skript `build_global_search_index.php` ist ein CLI-Skript, das in den cli-Ordner von Stud.IP verschoben oder kopiert werden kann. Dort ausgef�hrt �ber `php build_global_search_index.php` wird der Index in Stud.IP aktualisiert. Da dies immer ein besonders langer Prozess ist, lohnt es sich, das in der Nacht zu machen, damit die Datenbank nicht tags�ber von wichtigerem gehindert wird.
