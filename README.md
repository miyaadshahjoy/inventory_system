# 📦 Inventory Management System

![PHP](https://img.shields.io/badge/PHP-8%2B-blue)
![MySQL](https://img.shields.io/badge/MySQL-Database-orange)

A production-oriented **Inventory Management System** built with **vanilla PHP + MySQL**, focusing on **clean architecture, transaction-safe inventory operations, and real-world warehouse workflows**.

---

## 🚀 Live Demo

https://inventorysystem.great-site.net

---

## 🔑 Demo Credentials

**Email:** admin@example.com
**Password:** admin12345

---

## 🖼️ Screenshots

### 📊 Inventory Dashboard

![PREVIEW](/screenshots/inventory-page.png)

### 📦 Product Management

![PREVIEW](/screenshots/product-page.png)

### 🔄 Stock Movements

![PREVIEW](/screenshots/stock-movement-page.png)

---

# 🛠 Tech Stack

- PHP 8
- MySQL
- HTML5
- CSS3
- Vanilla JavaScript

---

# 🧠 Key Features

## 🔐 Authentication & Security

- Authentication system
- Role-based access (`ADMIN`, `STAFF`)
- Middleware-based route protection
- Session-based authentication
- Production-safe error handling
- Role-based movement restrictions for sensitive inventory operations

---

## 📦 Inventory Core

- Product CRUD
- Category CRUD
- Warehouse CRUD
- SKU-based product tracking
- Warehouse-aware stock management
- Reorder level system
- Low stock detection

---

## 🔄 Movement Engine

- Stock In / Stock Out
- Damage movements
- Expiry movements
- Return stock movements
- Admin-restricted inventory adjustment movements
- Warehouse transfer system
- Immutable stock movement history

---

## 🏭 Warehouse Transfer System

- Transfer inventory between warehouses
- Transaction-safe stock updates
- Linked transfer movements using `transfer_group_id`
- Prevents invalid or insufficient stock transfers

---

## 📊 Reporting & Dashboard

- Inventory summary overview
- Low stock alerts
- Inventory valuation overview
- Warehouse-based stock visibility
- Movement history reporting
- **CSV export support**

---

## 🔍 Data Exploration

- Search by product name or SKU
- Filter by category, date created, price, status, etc.
- Sort by name, price, date created
- **Paginated** listings

---

# ⚠️ Error Handling

- Centralized exception handling
- Flash messaging system
- Validation vs system exception separation
- Production-safe error pages (`403`, `404`, `500`)
- File-based error logging
- Hidden server-side errors in production

---

# 🧠 Inventory Engine Highlights

- **Transaction-safe** inventory operations
- Warehouse-specific stock tracking
- Snapshot-based inventory optimization
- Immutable movement ledger
- Linked warehouse transfer movements
- Atomic transfer transactions

---

# 🧱 Application Architecture

```txt
Request
  ↓
Router
  ↓
Middleware
  ↓
Controller
  ↓
Service Layer (Business Logic)
  ↓
Database Layer
```

---

# 🧠 Engineering Decisions

- Used stock snapshots to optimize inventory reads
- Separated business logic into dedicated service classes
- Implemented transaction-safe warehouse transfers
- Centralized error handling for production safety
- Used middleware for authentication and authorization
- Built immutable movement-based inventory tracking

---

# 📁 Project Structure

```txt
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

# 🛢 Database Design

The database was designed around movement-based inventory tracking and warehouse-aware stock snapshots.

## 📌 ER Diagram

![PREVIEW](/screenshots/erd.svg)

---

# ⚙️ Installation Guide

## 1️⃣ Clone Repository

```bash
git clone https://github.com/miyaadshahjoy/inventory_system.git
cd inventory_system
```

---

## 2️⃣ Configure Web Server

Point your document root to:

```txt
/public
```

---

## 3️⃣ Create Database

```sql
CREATE DATABASE inventory_system;
```

---

## 4️⃣ Create Admin User

Run the admin creation script:

```bash
php createAdmin.php
```

---

# 👨‍💻 Author

Miyaad Islam
