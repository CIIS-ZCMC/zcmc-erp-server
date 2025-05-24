# ZCMC ERP System - Annual Procurement Planning  
**A Laravel-based Enterprise Resource Planning System for Zamboanga City Medical Center**  

## 📌 System Overview  
This ERP System is designed to streamline the annual procurement planning of:  
- Medical equipment  
- Hospital supplies  
- Office items  
- Other essential goods  

Built for Zamboanga City Medical Center, it ensures efficient budget allocation, purchase tracking, and inventory forecasting.

## ✨ Key Features  
✅ **Annual Procurement Planning (AOP)**  
- Create, review, and approve annual purchase plans  
- Budget forecasting and allocation
- Multi-level approval workflows

✅ **Inventory Management**  
- Track stock levels of medical/office supplies  
- Automated reorder alerts  

✅ **Vendor & Supplier Portal**  
- Manage supplier contracts  
- Compare quotations  

✅ **Approval Workflows**  
- Structured three-tier approval process:
  - Department/Section/Unit Head creates and submits AOP
  - Planning Division provides initial review and validation
  - Division Chief conducts secondary review
  - Medical Center Chief gives final approval
- Real-time notifications for both application owners and approvers
- Interactive decision points with feedback loops for rejected applications
- Comprehensive application timeline tracking
- Automated status updates with proper error handling

✅ **Reporting Dashboard**  
- Annual spending analytics  
- Procurement status tracking  

## 🔄 Annual Operation Plan (AOP) Workflow
The AOP system follows a structured approval process with clearly defined roles and review stages:

### Workflow Columns/Responsibilities:
- **Department/Section/Unit Head or OIC**: Initiates and manages AOP creation
- **Planning Division**: Reviews submitted AOPs for initial approval
- **Division Chief**: Secondary review and approval stage
- **Medical Center Chief**: Final review and approval authority

### Workflow Process:
1. **Initial Planning Stage** (Department/Section/Unit Head):
   - Start with mission input
   - Select Type of Function
   - Select Objective from predefined list
   - If objective is not on the list, input new Objective
   - Input Success Indicator when selecting from list or adding new objective

2. **Activity Definition Stage**:
   - Input Activity details
   - Select person in-charge of the activity
   - Select start month and end month of activity
   - Select Yes/No if GAD-related activity
   - Input target by Quarter
   - Input unit of target (training, item and quantity)
   - Select type of resource (procurement, non-procurement)
   - Input cost for activity
   - Select Expense Class of Unit
   - Submit AOP

3. **Review and Approval Process**:
   - **Planning Division Review**:
     - Reviews AOP of Unit
     - Decision point: "passed review?"
     - If No: AOP returned to department for updates
     - If Yes: Forwards to Division Chief

   - **Division Chief Review**:
     - Reviews AOP of Unit
     - Decision point: "passed review?"
     - If No: AOP returned to department for updates
     - If Yes: Forwards to Medical Center Chief

   - **Medical Center Chief Review**:
     - Views AOP of Unit
     - Approves AOP of Unit
     - Process completes with "End"

4. **Update Process**:
   - If AOP is not approved at any stage, it returns to department
   - Department must update AOP based on feedback
   - Updated AOP re-enters the approval workflow
   - All approvals must be sequential (Planning → Division Chief → Medical Center Chief)

Each step in this workflow includes validation points to ensure quality and compliance before the AOP can proceed to the next approval level.

## ⚙️ Technology Stack  
- **Backend**: Laravel 12.0  
- **Frontend**: Blade, Tailwind CSS 4.0, Vite 6
- **Database**: MySQL  
- **Authentication**: Laravel Sanctum
- **Excel Processing**: Maatwebsite/Excel, PHPSpreadsheet
- **Logging**: Opcodesio Log Viewer
- **Integration**: UMIS (Unified Management Information System)

## 🚀 Installation  
1. Clone the repository:  
   ```bash
   git clone https://github.com/CIIS-ZCMC/zcmc-erp-server.git
   cd zcmc-erp-server
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install JavaScript dependencies:
   ```bash
   npm install
   ```

4. Set up environment variables:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. Configure your database in the `.env` file:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=your_database_username
   DB_PASSWORD=your_database_password
   ```

6. Run migrations and seeders:
   ```bash
   php artisan migrate --seed
   ```

7. Start the development server:
   ```bash
   php artisan serve
   ```

8. In a separate terminal, compile assets:
   ```bash
   npm run dev
   ```

## 💻 Development Setup

### UMIS Setup (Unified Management Information System)
1. Set up UMIS Locally
2. Switch to the `erp/controller` branch
3. Open the `.env` file and add or modify the following line:
   ```
   UMIS_API_KEY=LSF7XKPYrW9Qa82JMzVTd6B4NH3CUtEGWZKXQYmJFV
   ```
   (or generate your own API key)
4. Register the ERP system in your local UMIS database by inserting a new row:
   - name: X-ERP-System
   - code: ZCMC-ERP
   - domain: [your domain]
   - api_key: [your UMIS API key]
5. Start the UMIS server on port `8001`

### ERP Setup
1. Go to `development` branch
2. Open the `.env` file and add or modify the following line:
   ```
   UMIS_API_KEY=LSF7XKPYrW9Qa82JMzVTd6B4NH3CUtEGWZKXQYmJFV
   ```
   (or use your own generated API key)
3. Run database migrations:
   ```bash
   php artisan migrate
   ```
   or for a fresh install:
   ```bash
   php artisan migrate:fresh
   ```
4. Import all required data:
   ```bash
   php artisan import:all
   ```
5. Seed the database:
   ```bash
   php artisan db:seed
   ```

### Development Helper Command
For local development, you can use the convenient script:
```bash
composer run dev
```
This will concurrently run:
- Laravel development server
- Queue worker
- Log viewer
- Vite for frontend asset compilation

## 📄 License  
This project is licensed under the MIT License.