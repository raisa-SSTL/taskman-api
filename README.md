## Key Features
✅ JWT Authentication: Secure login & token-based authentication  
✅ Protected API Routes: Auth middleware restricts access to authenticated users  
✅ Role & Permission Management: Implemented using Laravel Spatie with permission-based route protection  
✅ Task & Employee APIs: Complete CRUD functionality for managing tasks and employees  
✅ Data Filtering & Sorting: APIs for searching, filtering, and sorting table data  
✅ Analytics APIs: APIs for calculating task progress, employee performance, and workload distribution with support for time-based parameters (month, date, year)  
✅ Optimized for Charts: Provides analytical data formatted for frontend visualizations

# Project Setup Guide

Follow these steps to set up the project on your local machine:

## Prerequisites

Ensure you have the following installed on your machine:
* PHP (version 8.1 or higher)
* Composer
* Node.js (version 16 or higher) and npm
* MySQL or any other database server supported by Laravel
* Git
* A compatible web server (e.g., Apache or Nginx)

## Step 1: Clone the repository
1. Open a terminal and navigate to the directory where you want to clone the project.
2. Run the following command:
   ### `git clone https://github.com/raisa-SSTL/taskman-api.git`
3. Navigate to the project directory:
   ### `cd taskman-api/taskman-api`
## Step 2: Install Dependencies:
Install PHP dependencies::
   ## `composer install`
## Step 3: Set Up Environment Variables
1. Copy the .env.example file to create your .env file:
   ## `cp .env.example .env`
2. Open the .env file in a text editor and update the following values:
  ```
  APP_NAME=YourAppName
  APP_URL=http://localhost
  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=your_database_name
  DB_USERNAME=your_database_username
  DB_PASSWORD=your_database_password
  ```
### Step 4: Set Up the Database
1. Create a new database in MySQL under your_database_name
2. Run the migrations and seed the database:
   ## `php artisan migrate --seed`
This will:
* Create all necessary database tables.
* Seed the database with an admin and employee user.
### Step 5: Generate Application Key
Run the following command to generate a unique application key:
## `php artisan key:generate`
### Step 6: JWT Configuration
Since we're using JWT for authentication, generate a secret key:
## `php artisan jwt:secret`
### Step 7: Start the Development Server
Start the Laravel development server:
## `php artisan serve`
The application will be accessible at http://localhost:8000 by default.  



