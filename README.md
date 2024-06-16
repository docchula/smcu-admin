# SMCU Administrative System

A web application for administrative tasks of the Student Union of the Faculty of Medicine, Chulalongkorn University.

Successor of [docchula/smcu-document-number](https://github.com/docchula/smcu-document-number), created by [Sarun Intaralawan](https://github.com/sarunint). Based on [Laravel](https://laravel.com/docs).

## Features

- Document Index (สารบรรณ) : Records of the union's documents since academic year 2551
    - Retrieves DocHub document status updates from Gmail, and notify document's owner of any error via Email
- Project Index : Records of the union's projects since academic year 2565
  - Record project participants, which can be viewed and printed by each participants
- Personnel Index : List of the union committee members, accessible to the public at https://admin.docchula.com/board
- Links to the union manual (hosted at Notion)

## Required maintenance

- Update personnel index (union committee member) yearly.
- Grant administrative privileges to the union's executive committee members (set roles to ADMIN in _users_ database table).
- Update union department list in case of establishment of new club (see _departments_ table).
- Delete or edit document/project information as requested (if any).

## Health Check

You can check the health of the system by visiting [/health/board?fresh](https://admin.docchula.com/health/board?fresh) (board UI)
or [/health](https://admin.docchula.com/health) (for API; public) endpoint. It'll check the database connection, Google API connection, cache
availability, used disk space, etc.

## Dependencies

- PHP
  - set _upload_max_filesize_ to at least 15 MB
- Relational Database e.g. MySQL
- Google API for OAuth 2.0 identity provider and Gmail read access (DocHub status updates)
- Vesta service (MDCU Directory) for student status verification
- SMTP for DocHub document status updates to document owners

## Developers

SMCU developers by generation. Add your name here when you're continuing the legacy!

- MDCU74: Siwat Techavoranant

## License

ALL RIGHTS RESERVED. © 2021-2023 by Siwat Techavoranant
