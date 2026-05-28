# Rohigraphy Archive Portfolio

Korean documentation: [README.ko.md](./README.ko.md)

Live site: [http://rohigraphy.co.kr:63478/](http://rohigraphy.co.kr:63478/)

A lightweight PHP-based portfolio CMS for visual creators, photographers, researchers, and concept artists.

Rohigraphy Archive Portfolio is a PHP-based visual portfolio and archive website for photography, research, concept art, storyboarding, and media-cultural studies. It is built for a single individual and personal brand: a snapshot photographer and multidisciplinary creator who needs an actual maintainable portfolio, not just a static design mockup.

This project is not just a static portfolio page. It includes a lightweight admin dashboard that allows content editing, image slot management, project updates, multilingual portfolio presentation, page design controls, SEO settings, and archive management through structured JSON data.

The current version uses PHP templates and JSON content files for simple deployment and easy operation. In the future, the content system is planned to be migrated to a web database-backed structure for stronger scalability, search, filtering, media management, and long-term content operations.

## Overview

Rohigraphy Archive Portfolio is a personal visual archive designed for a multidisciplinary creator. It combines photography, documentary images, natural photography, snapshot photography, concept art, research notes, cultural studies, data fluency, qualitative research, doodles, and animation-related visual development into one portfolio platform.

The goal is to provide a clean, maintainable, and editable portfolio system without relying on a heavy CMS.

## Why This Project Matters

Many portfolio websites are static and difficult to maintain after launch. This project focuses on making a portfolio that can actually be operated over time.

It separates content from presentation using structured JSON data and provides an admin interface for non-technical content updates. This makes it suitable for:

- Designer portfolios
- Photographer portfolios
- Snapshot photography archives
- Visual research archives
- Concept art portfolios
- Personal brand websites
- Small creative studio websites

## Key Features

- Responsive portfolio website
- PHP-based page rendering
- Lightweight admin dashboard
- JSON-based content management
- Image upload and replacement support
- Font upload support
- Multilingual content structure
- Project cards and project detail pages
- Page-specific and global design settings
- Light mode, dark mode, and header color controls
- Home page layout controls
- Pagination settings per archive page
- SEO metadata settings
- `llm.txt` for LLM-readable site context
- Reusable includes and shared layout components
- Simple deployment on a PHP-enabled web server

## Site Sections

- `Home` — introduction, hero section, selected works, categories, and notes
- `About Me` — creator story, education, CV, and background
- `Concept Art` — concept design, storyboard, movie, and animation-oriented visual works
- `Research` — cultural studies, media studies, data science, data fluency, and qualitative research archive
- `Photography` — documentary, natural, snapshot, profile, couple, family, and visual reference images
- `Doodle & Scribble` — rough sketches, visual notes, informal drawings, and early-stage ideas
- `Projects` — portfolio project cards and project detail pages
- `Resume` — experience, skills, profile, and contact information
- `Contact` — inquiry information, social links, brand details, and collaboration references

## Admin Dashboard

The project includes an admin dashboard for managing major website content.

Editable areas include:

- Site common settings
- Navigation menu and dropdowns
- Footer
- Metadata and SEO keywords
- Home, About, Work, Research, Photography, Doodle, Resume, and Contact content
- Project list and detail data
- Images and visual item slots
- Fonts
- Light mode and dark mode colors
- Header colors
- Home layout settings
- Archive pagination rules

Images can be uploaded and connected directly to content slots. Fonts can also be uploaded and applied through the design settings.

## Tech Stack

- PHP
- HTML
- CSS
- JavaScript
- JSON
- Tailwind CDN
- Docker / Nginx deployment support

## Project Structure

```txt
designer-portfolio/
├── admin/          # Admin dashboard and content editors
├── assets/         # CSS, JavaScript, images, uploaded assets
├── data/           # JSON-based content data
├── includes/       # Shared PHP components and layout files
├── index.php       # Main home page
├── about.php       # About page
├── work.php        # Concept art page
├── research.php    # Research archive page
├── photograph.php  # Photography page
├── doodle.php      # Doodle archive page
├── project.php     # Project detail page
├── resume.php      # Resume page
└── contact.php     # Contact page
```

## Deployment

This project can be deployed on any PHP-supported web server.

Current live deployment:

- [http://rohigraphy.co.kr:63478/](http://rohigraphy.co.kr:63478/)

Basic requirements:

- PHP-enabled hosting
- Web server such as Apache or Nginx
- Writable `data/` directory for JSON content updates
- Writable `assets/images/uploads/` directory for image uploads
- Writable `assets/fonts/uploads/` directory for font uploads

## Future Direction

This project is currently JSON-driven for simplicity and portability. A future version is expected to move toward a web database-backed content system.

Planned improvements may include database-backed content storage, advanced media library management, improved filtering and search, better image metadata support, versioned content editing, more flexible page builder features, and production-ready sitemap / SEO workflow.

## Repository Metadata Suggestion

Suggested GitHub description:

```txt
A lightweight PHP-based portfolio CMS for photography, research, concept art, and visual archive management.
```

Suggested topics:

```txt
php, portfolio, cms, designer-portfolio, photography-portfolio, visual-archive, admin-dashboard, json-cms, concept-art, research-portfolio
```

## Status

This project is currently used as a real portfolio and also serves as a practical web development portfolio project.

## Author

Created by Moomoo.