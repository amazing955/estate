# Estate Management System

This is a simple real-estate management application built with pure PHP (MVC style), MySQL, and Bootstrap for the frontend. It supports multiple user roles (admin, estate owner, broker, client) with authentication, property listings, media upload, inquiries/notifications, adverts, and dashboards.

## Features

- **User authentication** with `password_hash` and role-based access.
- **Property CRUD**: owners and brokers can add/edit/delete listings including multiple images and optional video.
- **Search & filtering** on home page.
- **Client actions**: view/listings, save properties, make inquiries.
- **Notification system**: owners/admin notified when clients show interest.
- **Advert module**: admin can upload adverts with positions and expiry dates.
- **Dashboards** customized by role.
- Owners receive actionable notifications with a "Track Sale" button to reserve or sell a property.
- Admins, owners, and brokers receive an email from the system whenever they make an update via their dashboard (e.g. property or profile changes).
- Clients can rate properties and book site tours directly from their dashboard, with dedicated forms and database records.
- **Secure file uploads** and PDO prepared statements.
- **Modern responsive UI** using Bootstrap.

## Folder Structure

```
/estate
├─ /assets
│  ├─ /css
│  ├─ /js
│  └─ /images
├─ /config
├─ /controllers
├─ /database
│  └─ schema.sql
├─ /models
├─ /uploads
│  ├─ /images
│  └─ /videos
└─ /views
   ├─ /admin
   ├─ /owner
   ├─ /broker
   ├─ /client
   ├─ header.php
   ├─ footer.php
   └─ home.php
```

## Setup

1. Install XAMPP or similar LAMP/WAMP stack.
2. Place this project in your web root (e.g., `c:\xampp\htdocs\estate`).
3. Create a database using the provided schema:
   ```sql
   SOURCE c:/xampp/htdocs/estate/database/schema.sql;
   ```
4. Update database credentials in `config/database.php` if needed.
5. Ensure the `uploads/images` and `uploads/videos` directories are writable.
6. Navigate to `http://localhost/estate/` to begin.

## Development Notes

- All database interaction uses PDO and prepared statements to prevent SQL injection.
- Authentication and sessions are handled in `controllers/AuthController.php`.
- Views are PHP files separated from business logic; no inline queries.
- Basic role checks are added on each dashboard/view page.
- Media uploads are stored in `/uploads` with unique filenames.
- Adverts are shown on the home page banner area.

## Extending

This skeleton can be extended with additional features like email notifications, pagination, more advanced filtering, and RESTful API endpoints.

---

*Generated on 2026-03-05 by GitHub Copilot (Raptor mini Preview).*