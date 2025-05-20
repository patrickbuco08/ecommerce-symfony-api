# **🛒 Symfony E-Commerce API**

📌 **A RESTful API for managing products, orders, and users, built with Symfony & JWT authentication.**

## **🚀 Features**

✅ **User Authentication** (JWT-based)  
✅ **Product Management** (CRUD operations)  
✅ **Order Management** (Create, update, and view orders)  
✅ **Invoice Generation** (Automatic PDF invoices)  
✅ **Admin Dashboard** (Order statistics & reports)  
✅ **CSV/JSON Export** (Filterable order reports)

---

## **🛠️ Installation**

### **1️⃣ Clone the Repository**

git clone https://github.com/your-username/ecommerce-symfony-api.git  
cd ecommerce-symfony-api

### **2️⃣ Install Dependencies** ⚙️

composer install

### **3️⃣ Configure Environment** 🌍

cp .env.dist .env

### **4️⃣ Generate JWT Keys** 🔑

php bin/console lexik:jwt:generate-keypair

### **5️⃣ Run Database Migrations** 📦

php bin/console doctrine:migrations:migrate

### **6️⃣ Start Symfony Server** 🚀

symfony server:start

---

## **📦 Docker Setup (Optional)** 🐳

You can run the API in a **Dockerized** environment with **MySQL 8**.

### **1️⃣ Start Docker Containers** 🏗️

docker compose up -d

### **2️⃣ Run Migrations Inside the PHP Container** 🔄

docker exec -it symfony_app php bin/console make:migration
docker exec -it symfony_app php bin/console doctrine:migrations:migrate

### **3️⃣ Access the API** 🌐

Import ecommerce_symfony_api.postman_collection.json into Postman.

## **📝 Testing API with Postman** 📬

1️⃣ **Authenticate using `/api/login_check`** to get a JWT token  
2️⃣ **Use the token in protected routes**  
`Authorization: Bearer your-token-here`

### **Populate Tables** 📦

## **Users**

docker exec -it symfony_app php bin/console app:populate-users

## **Categories**

docker exec -it symfony_app php bin/console app:populate-categories

## **Products**

docker exec -it symfony_app php bin/console app:populate-products

## **Reset Demo Tables**

docker exec -it symfony_app php bin/console app:reset-demo-tables

---

## **📄 License** 📜

This project is **open-source** and available under the MIT License.

**🚀 Happy Coding!** 🎉
