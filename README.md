Baïkal
======

[![continuous-integration](https://github.com/sabre-io/Baikal/actions/workflows/ci.yml/badge.svg)](https://github.com/sabre-io/Baikal/actions/workflows/ci.yml)

This is the source repository for the Baïkal CalDAV and CardDAV server.

Head to [sabre.io/baikal][2] for information about installation, upgrading and troubleshooting.

A German tutorial about installation of Baïkal, using it together with Thunderbird, Android and DavX5, sharing calendars, database maintenance and security can be found [here][6]. Its focus is for people with not so much IT experience. Therefore it is very detailed, step by step with a RaspberryPI used as server.

With the same objective, a French guide, about installation on Debian, database maintenance, security and dealing with iOS clients can be found [here][7]. Its focus is also for people with not so much IT experience.

# This fork
Significant changes in this fork include:

## FEATURES
- Support for a WebDAV per-user home directory
- Add VJOURNAL support to default calendar for new user
- Use salted hash for stored passwords (table and column names, auth backend are different)
- Add Docker files
- Add color picker input for editing calendar color
- Add UI for navigating Contact, Event, Task, Alarm and Journal data; allow user to convert VEVENT to VJOURNAL; allow user to mark VTODOs complete/incomplete
- Add apps for list management, project time tracking
- Optional server-side recurrence management to prevent various CalDAV clients from erasing each others' recurrence rules on VTODOs.

## DEPENDENCIES
- Dependence on [Hydrogen](https://github.com/buckaroo-labs/Hydrogen) library v2 or higher. If you install via Docker, this is managed for you.
- Some (new) features depend on having MySQL as the RDBMS; some steps have been taken to continue supporting SQLite and PGSQL as well, but this effort is incomplete and untested.



Upgrading
---------

Please follow [the upgrade instructions][5].

Credits
-------

Baikal was created by [Jérôme Schneider][3] from Net Gusto and [fruux][4] and is now developed by volunteers.
Many thanks to Daniel Aleksandersen (@zcode) for greatly improving the quality of the project page.

[2]: https://sabre.io/baikal/
[3]: https://github.com/jeromeschneider
[4]: https://fruux.com/
[5]: https://sabre.io/baikal/upgrade/
[6]: https://github.com/JsBergbau/BaikalAnleitung
[7]: https://github.com/criticalsool/Baikal-Guide-FR

