# 📦 Inventory Management System

![PHP](https://img.shields.io/badge/PHP-8%2B-blue)
![MySQL](https://img.shields.io/badge/MySQL-Database-orange)

A production-oriented **Inventory Management System** built with **vanilla PHP + MySQL**, focusing on **clean architecture, scalability, and real-world inventory workflows**.

---

## 🚀 Live Demo

[https://inventorysystem.great-site.net](https://inventorysystem.great-site.net)

---

## 🖼️ Screenshots

### 📊 Inventory Overview

screenshots/inventory-page.png

### 📦 Product Management

_(Add real screenshots here)_

### 🔄 Stock Movements

_(Add real screenshots here)_

---

## 🧠 Key Features

### 🔐 Auth & Security

- Authentication system
- Role-based access (`ADMIN`, `STAFF`)
- Middleware-based protection

### 📦 Inventory Core

- Product / Category / Warehouse CRUD
- SKU-based tracking
- Reorder level system

### 🔄 Movement Engine

- Stock In / Out
- Damage & Expiry movements
- Warehouse Transfers
- Returns module
- Adjustment movements (Admin only)

### 📊 Reporting

- Inventory overview dashboard
- Low stock alerts
- Movement history
- CSV export (with filters)

### 🔍 Data Exploration

- Search by name / SKU
- Advanced filters (category, price, date, status)
- Pagination

---

## 🧱 Architecture

```
Controller → Service → Database
             ↓
          Business Logic
```

---

## 📁 Project Structure

```
inventory-system/
│
├── app/
│   ├── controllers/
│   ├── core/
│   ├── exceptions/
│   ├── middlewares/
│   ├── services/
│   └── views/
│
├── config/
├── public/
│   ├── assets/
│   ├── .htaccess
│   └── index.php
│
├── storage/
└── README.md
```

---

## ⚙️ Installation Guide

### 1. Clone

```
git clone https://github.com/your-username/inventory-system.git
cd inventory-system
```

---

### 2. Configure Server

Point root to:

```
/public
```

---

### 3. Setup Database

```
CREATE DATABASE inventory_system;
```

---

### 4. Create Admin User

Use:

```
password_hash('admin12345', PASSWORD_DEFAULT);
```

---

### 5. Run

```
php -S localhost:8000 -t public
```

---

## 🔑 Demo Credentials

Email: admin@example.com  
Password: admin12345

---

## 👨‍💻 Author

Miyaad  
Backend Developer
