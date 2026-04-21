# Product Photo Tagger

A lightweight PHP web application for uploading, tagging, and organizing product images. Features user authentication, gallery view, and admin management tools.

## Features

- 🔐 User authentication with demo/admin roles
- 📤 Drag & drop image upload
- 🏷️ Manual tagging and description system
- 🖼️ Gallery view with organized display
- 👑 Admin panel for user management
- 💰 Coin-based upload system (demo feature)
- 📱 Responsive design

## Stack

- PHP 8.1+
- MySQL
- Vanilla HTML/CSS/JS

## Stack

- PHP 8.1+
- MySQL
- Vanilla HTML/CSS/JS

## Project structure

- `index.php` upload + inline result rendering
- `gallery.php` read-only gallery page
- `api/analyze.php` upload validation and tagging
- `config/db.php` PDO connection helper
- `config/constants.php` runtime constants and secrets
- `uploads/` local image storage
- `assets/style.css` shared styles
- `schema.sql` DB table creation

## Quick start

1. Create DB/table using `schema.sql`.
2. Copy `config/constants.example.php` to `config/constants.php` and update with your values:
   - DB credentials
   - `APP_BASE_URL` if app is not hosted at domain root
   - Demo and admin passwords
3. Ensure `uploads/` is writable by PHP.
4. Open `index.php` and run an upload.

## Notes

- `config/constants.php` is ignored by git via `.gitignore`. Use `config/constants.example.php` as a template.
- Uploaded files are not listable (`uploads/.htaccess` has `Options -Indexes`).
- API failures auto-delete the just-uploaded file.
- If DB insert fails, the UI still returns AI output, and the insert error is logged server-side.
 
