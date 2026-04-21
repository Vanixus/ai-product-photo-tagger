# AI Product Photo Tagger

A lightweight PHP web application for uploading, analyzing, and organizing product images with AI-powered tagging and metadata generation.

## Features

- **Image Upload** — Drag-and-drop interface with validation and processing
- **AI-Powered Tagging** — Automated tag and description generation using Claude API
- **Gallery Management** — Organized view of uploaded images with metadata display
- **User Authentication** — Role-based access (demo and admin accounts)
- **Admin Panel** — User and upload management tools
- **Coin-Based System** — Demo feature for upload tracking
- **Responsive Design** — Mobile-friendly interface

## Tech Stack

- **Backend** — PHP 8.1+
- **Database** — MySQL with PDO
- **Frontend** — Vanilla HTML, CSS, and JavaScript
- **AI Integration** — Anthropic Claude API

## Project Structure

| File | Purpose |
|------|---------|
| `index.php` | Main upload and results page |
| `gallery.php` | Read-only gallery view of all images |
| `login.php` | User authentication entry point |
| `admin.php` | Admin panel for management |
| `api/analyze.php` | Image analysis and tagging endpoint |
| `config/db.php` | Database connection helper |
| `config/constants.php` | Runtime configuration and secrets |
| `includes/auth.php` | Authentication utilities |
| `assets/style.css` | Shared CSS styles |
| `schema.sql` | Database schema and table definitions |
| `uploads/` | Local image storage directory |

## Quick Start

### Prerequisites

- PHP 8.1 or higher
- MySQL 5.7 or higher
- Web server (Apache, Nginx, etc.)
- Anthropic API key (for AI tagging)

### Installation

1. **Clone or download the project** into your web root directory.

2. **Create the database and tables:**
   ```bash
   mysql -u root -p < schema.sql
   ```

3. **Configure the application:**
   - Copy `config/constants.example.php` to `config/constants.php`
   - Update the following values:
     - Database credentials (`DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`)
     - `APP_BASE_URL` if the app is not hosted at the domain root
     - `ANTHROPIC_API_KEY` with your API key from [Anthropic](https://console.anthropic.com)
     - Demo and admin passwords (`DEMO_PASSWORD`, `ADMIN_PASSWORD`)

4. **Set permissions:**
   - Ensure the `uploads/` directory is writable by the PHP process:
     ```bash
     chmod 755 uploads/
     ```

5. **Access the application:**
   - Open your browser and navigate to the application URL
   - Log in with demo or admin credentials
   - Start uploading product images

## Usage

- **Upload Images** — Use the upload form on the main page to add product photos
- **View Gallery** — Browse all uploaded images with their generated metadata
- **Manage Users** — Access the admin panel to manage user accounts and uploads

## Configuration

`config/constants.php` contains all runtime settings. This file is ignored by Git for security. Use `config/constants.example.php` as a template for initial setup.

### Key Configuration Options

- `APP_NAME` — Application display name
- `ANTHROPIC_MODEL` — Claude model version for image analysis
- `MAX_UPLOAD_BYTES` — Maximum file size for uploads (default: 5MB)
- `SYSTEM_PROMPT` — Instructions for AI tagging system

## Security Notes

- Store `config/constants.php` securely and never commit to version control
- Use strong passwords for demo and admin accounts
- Validate and sanitize all user input
- Ensure uploads directory is outside the web root in production environments
- Restrict file types to approved image formats

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

You are free to use, modify, and distribute this project, including for commercial purposes, with proper attribution.

## Contact

For questions or support, please reach out to [idris.jadidi@gmail.com](mailto:idris.jadidi@gmail.com)
