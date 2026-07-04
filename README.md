# Stock Movement Management System

A robust, backend-driven inventory module built with Laravel. This system safely handles stock movements (sales, purchases, returns) using strict database transactions, pessimistic row locking (`lockForUpdate`), and concurrency protections to ensure inventory consistency.

## Core Features
* **Transaction Safety**: All stock movements and balance updates occur within atomic DB transactions.
* **Concurrency Handling**: Row-level locking prevents race conditions during high-volume API requests.
* **Consistency Rules**: Stock is actively prevented from dropping below zero.
* **Standardized API**: Utilizes Laravel API Resources and custom Exception rendering for consistent JSON responses.

## Setup Instructions

1. Clone the repository and install dependencies:
   ```bash
   composer install
   ```

2. Copy the environment file and generate the app key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Environment Configuration:** 
   Open your `.env` file and configure your database connection. For a local MySQL setup, it should look like this:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=meridian_stock
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. Run migrations and seed the database with initial products and users:
   ```bash
   php artisan migrate:fresh --seed
   ```

5. Start the local development server:
   ```bash
   php artisan serve
   ```

## Test Credentials
The database seeder provisions two users for testing:
* Admin: `admin@gmail.com` / `Admin@123`
* User: `user@gmail.com` / `User@123`

---

## 🚀 API TESTING GUIDE

Below is the complete testing workflow.

⚠️ **IMPORTANT:** 
Run **TEST 1** first, copy the `token` from the response, and replace `YOUR_TOKEN` with it in the headers for all subsequent tests!

### TEST 1 - Login

```text
METHOD : POST
URL    : http://localhost:8000/api/auth/login
HEADERS: Content-Type: application/json
BODY   :
{
  "email": "admin@gmail.com",
  "password": "Admin@123"
}
```
✅ **EXPECTED STATUS:** 200  
✅ **EXPECTED RESPONSE:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@gmail.com"
    },
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxx"
  }
}
```

### TEST 2 - Get Current User

```text
METHOD : GET
URL    : http://localhost:8000/api/auth/me
HEADERS: Authorization: Bearer YOUR_TOKEN
BODY   : none
```
✅ **EXPECTED STATUS:** 200  
✅ **EXPECTED RESPONSE:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@gmail.com",
    "created_at": "2024-01-15T10:00:00.000000Z",
    "updated_at": "2024-01-15T10:00:00.000000Z"
  }
}
```

### TEST 3 - Create a Product

```text
METHOD : POST
URL    : http://localhost:8000/api/products
HEADERS: Authorization: Bearer YOUR_TOKEN
         Content-Type: application/json
BODY   :
{
  "name": "Sony PlayStation 5",
  "sku": "CONSOLE-SONY-PS5",
  "is_active": true
}
```
✅ **EXPECTED STATUS:** 201  
✅ **EXPECTED RESPONSE:**
```json
{
  "success": true,
  "message": "Product created successfully",
  "data": {
    "id": 1,
    "name": "Sony PlayStation 5",
    "sku": "CONSOLE-SONY-PS5",
    "is_active": true,
    "current_stock": 0,
    "created_at": "2024-01-15T10:00:00"
  }
}
```

### TEST 4 - Create a Stock Movement (Purchase)

```text
METHOD : POST
URL    : http://localhost:8000/api/stock-movements
HEADERS: Authorization: Bearer YOUR_TOKEN
         Content-Type: application/json
BODY   :
{
  "product_id": 1,
  "movement_type": "purchase",
  "quantity": 50,
  "reference_number": "PUR-1001",
  "notes": "Initial inventory arrival"
}
```
✅ **EXPECTED STATUS:** 201  
✅ **EXPECTED RESPONSE:**
```json
{
  "success": true,
  "message": "Stock movement created successfully",
  "data": {
    "id": 1,
    "movement_type": "purchase",
    "quantity": 50,
    "reference_number": "PUR-1001",
    "notes": "Initial inventory arrival",
    "product": {
      "id": 1,
      "name": "Sony PlayStation 5",
      "sku": "CONSOLE-SONY-PS5"
    },
    "created_by": "Admin User",
    "created_at": "2024-01-15T10:05:00"
  }
}
```

### TEST 5 - Check Stock Balance for a Product

```text
METHOD : GET
URL    : http://localhost:8000/api/products/1/stock
HEADERS: Authorization: Bearer YOUR_TOKEN
BODY   : none
```
✅ **EXPECTED STATUS:** 200  
✅ **CHECK:** current_stock must be 50  
✅ **EXPECTED RESPONSE:**
```json
{
  "success": true,
  "data": {
    "product_id": 1,
    "product_name": "Sony PlayStation 5",
    "sku": "CONSOLE-SONY-PS5",
    "is_active": true,
    "current_stock": 50,
    "last_movement_at": "2024-01-15T10:05:00.000000Z"
  }
}
```

### TEST 6 - Get All Movements for a Specific Product

```text
METHOD : GET
URL    : http://localhost:8000/api/products/1/stock-movements
HEADERS: Authorization: Bearer YOUR_TOKEN
BODY   : none
```
✅ **EXPECTED STATUS:** 200  
✅ **EXPECTED RESPONSE:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "movement_type": "purchase",
      "quantity": 50,
      "reference_number": "PUR-1001",
      "notes": "Initial inventory arrival",
      "created_by": "Admin User",
      "created_at": "2024-01-15T10:05:00"
    }
  ]
}
```

### TEST 7 - Get All Stock Movements (Global with Filter)

```text
METHOD : GET
URL    : http://localhost:8000/api/stock-movements?movement_type=purchase
HEADERS: Authorization: Bearer YOUR_TOKEN
BODY   : none
```
✅ **EXPECTED STATUS:** 200  
✅ **EXPECTED RESPONSE:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "movement_type": "purchase",
      "quantity": 50,
      "reference_number": "PUR-1001",
      "notes": "Initial inventory arrival",
      "product": {
        "id": 1,
        "name": "Sony PlayStation 5",
        "sku": "CONSOLE-SONY-PS5"
      },
      "created_by": "Admin User",
      "created_at": "2024-01-15T10:05:00"
    }
  ]
}
```
