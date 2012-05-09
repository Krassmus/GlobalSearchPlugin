## Stud.IP GlobalSearchPlugin

Bietet endlich eine globale Suche für Stud.IP. Der Nutzer tippt und erhält alles, wonach er gesucht hat. Gefunden werden 
* Veranstaltungen 
* Dokumente 
* Nutzer 
* Räume 
* Forenpostings 
gefunden.

Weitere Features:
* Suchergebnisse als unendliche Liste (lädt sich selbst nach, wenn der Nutzer nach unten scrollt)
* Einschränkung (Filter) nach Studienbereichen
* Einschränkung (Filter) nach Typ - auch kombinierbar mit Studienbereichen
* Alle Ergebnisse mit korrekten Sichtbarkeitseinstellungen. Man findet nur Dokumente in öffentlichen und den eigenen Veranstaltungen.
* Quicklink-Funktionen in der rechten-oberen Ecke der Suchergebnisse, damit Admins zum Beispiel schnell Termine oder Zeiten/Räume der Veranstaltungen bearbeiten können.
* Dieses Plugin ist selbst durch Plugins erweiterbar durch Verwendung des NotificationCenters.
* CLI-Skript zum nächtlichen Berechnen des Suchindexes.

### CLI-Skript zum Indexberechnen

Das Skript `build_global_search_index.php` ist ein CLI-Skript, das in den cli-Ordner von Stud.IP verschoben oder kopiert werden kann. Dort ausgeführt über `php build_global_search_index.php` wird der Index in Stud.IP aktualisiert. Da dies immer ein besonders langer Prozess ist, lohnt es sich, das in der Nacht zu machen, damit die Datenbank nicht tagsüber von wichtigerem gehindert wird.
