# Online Personal Wellness Tracker

A PHP and MySQL application for recording daily wellness activities and reviewing personal logs.

## Features

- Water, exercise, sleep and mood tracking
- Wellness log editing and history
- Personal reminders
- Account and profile management

## Technology

PHP, MySQL, HTML, CSS and JavaScript.

## Local setup

Clone the repository:

```bash
git clone https://github.com/nasirabbas827/wellness_tracker-final.git
cd wellness_tracker-final
```

1. Install a local PHP web server and MySQL, such as XAMPP.
2. Place the project in the server's document root, or configure a virtual host pointing to this folder.
3. Import `Database/wellness_db.sql` using phpMyAdmin or your MySQL client.
4. Update the database connection in `config.php` to match your local database name and credentials.
5. Start Apache and MySQL, then open `http://localhost/wellness_tracker-final/`.

## Repository layout

| Path | Purpose |
|---|---|
| `Database/` | SQL database export |
| `config.php` | Database connection configuration |
| `css/` | Project resources |
| `images/` | Project resources |
| `index.php` | Web entry page |

## Development notes

Setup steps describe local development. Configure your own database, mail and API credentials where applicable. Review debug settings and replace development secrets before deploying. The application was not executed as part of this documentation update.

Maintained by [Nasir Abbas](https://github.com/nasirabbas827).
