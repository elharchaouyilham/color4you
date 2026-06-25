# Artt - Atelier & Library Management System

Artt is a comprehensive web application for managing drawing workshops (Ateliers) and catalog reservations. Built on **Laravel 11**, **React**, **TypeScript**, **Inertia.js**, and **Tailwind CSS / PrimeReact**, it offers roles for Administrators, Trainers, and Clients.

---

## 🏗️ Project Architecture & Design
Please refer to the following documents for deep-dive architectural guidelines and conventions:
* **System Architecture & Testing Standards**: [docs/SYSTEM_DESIGN.md](docs/SYSTEM_DESIGN.md)
* **Frontend Component & Styling Guidelines**: [docs/DESIGN.md](docs/DESIGN.md)

---

## 📋 Prerequisites
Before starting, ensure you have installed:
* [Docker Desktop](https://www.docker.com/products/docker-desktop/) (running on Windows, macOS, or Linux)

No local installation of PHP, Composer, or Node is strictly required as all dependencies run isolated within Docker.

---

## 🚀 Getting Started (Step-by-Step)

### Step 1: Clone the Repo & Run Docker Containers
Run the following command to build and launch the application containers in the background:
```bash
docker compose up -d --build
```
This boots:
* **Web Server (Nginx)**: Port `80`
* **PHP App**: Port `9000` (FPM)
* **Node (Vite)**: Port `5173`
* **MySQL Database**: Port `3306` (host forwarded)
* **MinIO Object Storage**: Ports `9000` (API) and `9001` (Admin Console)

### Step 2: Initialize Database and Default Data (Critical)
Run the migrations and seed default records (users, products, categories, sessions):
```bash
docker compose exec app php artisan migrate --seed
```

---

## 🔑 Default Seeded Accounts
Once the database is seeded, you can log in using any of the following pre-configured credentials (password is `password` for all):

| Role | Email | Password | Features |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@artt.test` | `password` | Sessions CRUD, category setup, product catalog, confirming/processing reservations. |
| **Trainer 1** | `jane.doe@example.com` | `password` | Confirming availability, viewing participants list, marking attendance. |
| **Trainer 2** | `john.smith@example.com` | `password` | Same as Trainer 1 (different specialty). |
| **Client** | `client@example.com` | `password` | Registering for sessions, requesting/cancelling reservations. |

---

## 🌐 Application Services & Admin URLs

* **Application Front-End**: [http://localhost](http://localhost)
* **Vite HMR Dev Server**: [http://localhost:5173](http://localhost:5173)
* **MinIO Storage Dashboard**: [http://localhost:9001](http://localhost:9001)
  * *Username*: `artt`
  * *Password*: `password`
  * *Bucket*: `artt` (preconfigured to support public anonymous downloads of cover images)

---

## 🛠️ CLI Cheat Sheet

### Run the PHPUnit Test Suite
```bash
docker compose exec app php artisan test
```

### Regenerate Media Library Assets (MinIO)
If you ever need to clear or regenerate dummy media files for drawing sessions and products:
```bash
docker compose exec app php artisan media-library:regenerate
```

### Clean System Caches
```bash
docker compose exec app php artisan optimize:clear
```

### Recompile Production Static Assets
```bash
docker compose exec node npm run build
```

---

## 📂 Project Structure

Below is an overview of the key directories in the project:

```
├── app/
│   ├── Enums/               # Enums (DrawingSessionStatus, ProductStatus, ReservationStatus, etc.)
│   ├── Http/
│   │   ├── Controllers/     # Controllers (Admin, Trainer, Client namespaces)
│   │   ├── Middleware/      # Custom middleware (HandleInertiaRequests, Spatie Role middleware)
│   │   └── Requests/        # Form Validation Requests
│   ├── Models/              # Eloquent Models (User, Product, DrawingSession, Category, etc.)
│   └── Services/            # Business Logic (ReservationService for stock adjustment checks)
│
├── database/
│   ├── migrations/          # DB Schema Definitions
│   └── seeders/             # Database Seeders for sandbox setups
│
├── docs/                    # Architecture & Guidelines documentation
│
├── public/                  # Public web directory
│   └── build/               # Compiled production assets (Vite manifest and chunks)
│
├── resources/
│   ├── css/                 # Global styles
│   ├── js/
│   │   ├── Components/      # Common UI components
│   │   ├── Layouts/         # Shell layouts (AdminLayout, ClientLayout, PublicLayout)
│   │   ├── Pages/           # Inertia React page components
│   │   ├── types/           # TypeScript definitions
│   │   └── app.tsx          # Application entry point
│   └── views/
│       └── app.blade.php    # Root Blade template (loads @routes and Vite assets)
│
├── routes/
│   ├── auth.php             # Authentication routes (Laravel Breeze)
│   └── web.php              # App routes (grouped by visitor, client, trainer, and admin scopes)
│
└── tests/                   # Automated PHPUnit tests (Feature and Unit)
```
