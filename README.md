# 🚀 Fluxo Backend

Backend for the **Fluxo** project, built with **Laravel 11**, **Laravel Breeze (API)**, **Docker**, **Vite**, and **Nginx**.

This repository contains the **complete development environment**: PHP-FPM, Nginx, Node (for Vite), and database (MySQL or SQLite).

---

## 📌 Technologies Used

- **Laravel 11**
- **Laravel Breeze (API authentication)**
- **PHP 8.3**
- **Nginx**
- **Docker & Docker Compose**
- **Node 22 (Vite dev server)**
- **MySQL 8 or SQLite**
- **Composer**
- **Vite + TailwindCSS**

---

## Installation & Setup

1. **Clone the repository**

```bash
git clone https://github.com/charlesbmateus/fluxo.git
cd fluxo-backend
```

2. **Copy/Create the environment file**
```bash
    .env
```

3. **Build and start the containers**
```bash
   docker-compose up -d --build
```

4. **Check running containers**
```bash
   docker-compose ps
```

5. **Install PHP dependencies**
```bash
   docker exec -it fluxo-backend-app-container composer install
```

6. **Run migrations and storage link**
```bash
   docker exec -it fluxo-backend-app-container php artisan migrate --force
   docker exec -it fluxo-backend-app-container php artisan storage:link
```

7. **Install Node dependencies**
```bash
   docker exec -it fluxo-node npm install
```

8. **Run the development server**
```bash
   docker exec -it fluxo-node npm run dev
```

9. **Access URLs**

- Laravel app: http://localhost:8000
- Vite HMR server: http://localhost:5173
