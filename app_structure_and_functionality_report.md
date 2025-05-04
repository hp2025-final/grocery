# Laravel Accounting & Inventory App: Comprehensive Functional and Structural Report

## 1. Application Overview
This web application is a double-entry accounting and inventory management system tailored for grocery stores in Pakistan. It is built using Laravel 10+, MySQL, and Blade, with features for sales, purchases, inventory, expenses, banking, and reporting. All transactions are tightly linked to the chart of accounts for full traceability and compliance.

---

## 2. Application Structure

- **Controllers:** Located in `app/Http/Controllers`, each module (inventory, sales, purchases, expenses, etc.) has its own controller for business logic and form handling.
- **Models:** Located in `app/Models`, each main entity (Inventory, JournalEntry, Customer, Vendor, etc.) has a corresponding Eloquent model.
- **Views:** Blade templates are organized by module in `resources/views/{module}`. Each form and report has a dedicated Blade file.
- **Migrations:** Database schema is defined in `database/migrations`, with a migration for each table.
- **Seeders:** Populate initial data such as chart of accounts and categories.

---

## 3. Database Structure (Key Tables)

- **users:** Application users (owners, staff).
- **inventory_categories:** Categories for inventory products.
- **inventories:** Products table (code, name, category, unit, buy/sale price, opening qty, notes).
- **chart_of_accounts:** Chart of accounts for double-entry bookkeeping.
- **journal_entries & journal_entry_lines:** Store all accounting entries, with references to the originating module.
- **purchases, purchase_items:** Purchase transactions and their line items.
- **sales, sale_items:** Sales transactions and their line items.
- **customers, vendors:** Business partners.
- **expenses:** Expense records.
- **stock_adjustments, stock_adjustment_items:** Inventory corrections.
- **customer_receipts, vendor_payments:** Cash inflows/outflows.

---

## 4. Forms: Purpose & Database Impact

### Inventory Forms
- **Add Inventory Product:** (`/inventory/create`)
  - Fields: Product Code, Name, Category, Unit, Buy Price, Sale Price, Opening Qty, Notes
  - **Database:** Inserts into `inventories`. If Opening Qty is set, creates a `journal_entry` with lines in `journal_entry_lines` for opening stock.

### Purchase Forms
- **Create Purchase:** (`/purchases/create`)
  - Fields: Vendor, Date, Items (product, qty, price), Notes
  - **Database:** Inserts into `purchases` and `purchase_items`. Creates journal entries for inventory and accounts payable.

### Sales Forms
- **Create Sale:** (`/sales/create`)
  - Fields: Customer, Date, Items (product, qty, price), Notes
  - **Database:** Inserts into `sales` and `sale_items`. Creates journal entries for revenue and inventory reduction.

### Expense Forms
- **Create Expense:** (`/expenses/create`)
  - Fields: Account, Amount, Date, Description
  - **Database:** Inserts into `expenses`. Creates a journal entry for expense and cash/bank.

### Receipts/Payments
- **Customer Receipt:** (`/customer-receipts/create`)
  - Fields: Customer, Amount, Date, Description
  - **Database:** Inserts into `customer_receipts`. Journal entry for cash/bank and accounts receivable.
- **Vendor Payment:** (`/vendor-payments/create`)
  - Fields: Vendor, Amount, Date, Description
  - **Database:** Inserts into `vendor_payments`. Journal entry for cash/bank and accounts payable.

### Stock Adjustment
- **Create Stock Adjustment:** (`/stock-adjustments/create`)
  - Fields: Product, Qty, Reason
  - **Database:** Inserts into `stock_adjustments` and `stock_adjustment_items`. Journal entry for inventory adjustment.

---

## 5. Reports: Purpose & Data Sources

- **Trial Balance:** (`/reports/trial-balance`)
  - Shows all account balances from `chart_of_accounts` and `journal_entry_lines`.
- **General Ledger:** (`/reports/general-ledger`)
  - Shows all transactions for each account.
- **Journal:** (`/reports/journal`)
  - Lists all journal entries with details.
- **Income Statement:** (`/reports/income-statement`)
  - Summarizes revenue and expenses for a period.
- **Balance Sheet:** (`/reports/balance-sheet`)
  - Shows assets, liabilities, and equity.

---

## 6. User Roles & Permissions
- **Owner (Super Admin):** Full access.
- **Staff:** Limited by module permissions (sales, inventory, etc.).
- **Implemented using Laravel Policies/Gates.**

---

## 7. Special Features
- **Automatic Journal Entries:** Every transaction (sales, purchase, opening balance, etc.) creates double-entry records in `journal_entries` and `journal_entry_lines`.
- **Unique Reference Codes:** Each transaction gets a unique code (e.g., PRD-000001 for products).
- **Localization:** All currency in PKR, units tailored for groceries.
- **Validation:** All forms use Laravel validation for data integrity.

---

## 8. Example Data Flow: Add Inventory Product
1. User fills the Add Product form.
2. Data is validated and inserted into `inventories`.
3. If opening qty is set, a journal entry is created for opening stock.
4. Product is immediately available for use in purchases/sales.

---

## 9. Directory Structure (Key Folders)
- `app/Http/Controllers/` – All controllers (business logic)
- `app/Models/` – All Eloquent models
- `resources/views/` – Blade templates (forms, reports)
- `database/migrations/` – Database schema
- `database/seeders/` – Initial data

---

## 10. Additional Notes
- **All forms are tightly coupled with database tables and journal entries.**
- **Reports are generated from accounting and transaction tables.**
- **Every action is traceable via unique codes and journal references.**

---

## Expanded Example Scenarios & Data Flows

### 1. Add New Customer
- **Form:** `/customers/create`
- **Fields:** Name, Contact, Opening Balance, Opening Balance Type (Debit/Credit)
- **Scenario A:** Add customer "Ali" with opening balance 50,000 Debit
  - **Database Entries:**
    - `customers`: New row for Ali
    - `journal_entries`: New entry for opening balance (reference_type: 'customer', reference_id: Ali's ID)
    - `journal_entry_lines`:
      - Debit: `accounts_receivable` (from chart_of_accounts), 50,000
      - Credit: `opening_balance_equity` (or other equity/capital account), 50,000
  - **Accounts Used:**
    - Debit: Accounts Receivable (Asset)
    - Credit: Opening Balance Equity (Equity)
- **Scenario B:** Add customer with opening balance = 0 or not provided
  - **Database Entries:**
    - `customers`: New row for customer
    - **No journal entry is created.** Only the customer record is stored; accounting tables are not affected.


### 2. Add New Vendor
- **Form:** `/vendors/create`
- **Fields:** Name, Contact, Opening Balance, Opening Balance Type (Debit/Credit)
- **Scenario A:** With Opening Balance (e.g., 20,000 Credit)
  - **Database Entries:**
    - `vendors`: New vendor
    - `journal_entries`: New entry for opening balance
    - `journal_entry_lines`:
      - Debit: Opening Balance Equity (Equity), 20,000
      - Credit: Accounts Payable (Liability), 20,000
  - **Accounts Used:**
    - Debit: Opening Balance Equity
    - Credit: Accounts Payable
- **Scenario B:** Opening balance = 0 or not provided
  - **Database Entries:**
    - `vendors`: New vendor
    - **No journal entry is created.** Only the vendor record is stored; accounting tables are not affected.


### 3. Add New Inventory Product
- **Form:** `/inventory/create`
- **Fields:** Code, Name, Category, Unit, Buy/Sale Price, Opening Qty
- **Scenario A:** With Opening Qty (e.g., 100 units @ 50 = 5,000)
  - **Database Entries:**
    - `inventories`: New product
    - `journal_entries`: New entry for opening stock
    - `journal_entry_lines`:
      - Debit: Inventory (Asset), 5,000
      - Credit: Opening Balance Equity (or Inventory Opening), 5,000
  - **Accounts Used:**
    - Debit: Inventory
    - Credit: Opening Balance Equity (or Inventory Opening)
- **Scenario B:** Opening Qty = 0 or not provided
  - **Database Entries:**
    - `inventories`: New product
    - **No journal entry is created.** Only the inventory record is stored; accounting tables are not affected.


### 4. Add New Bank Account
- **Form:** `/banks/create` (or similar)
- **Fields:** Name, Account Number, Opening Balance (optional)
- **Scenario A:** With Opening Balance (e.g., 10,000 Debit)
  - **Database Entries:**
    - `banks`: New bank
    - `journal_entries`: New entry for opening balance
    - `journal_entry_lines`:
      - Debit: Bank (Asset), 10,000
      - Credit: Opening Balance Equity, 10,000
  - **Accounts Used:**
    - Debit: Bank
    - Credit: Opening Balance Equity
- **Scenario B:** Opening balance = 0 or not provided
  - **Database Entries:**
    - `banks`: New bank
    - **No journal entry is created.** Only the bank record is stored; accounting tables are not affected.


### 5. Add New Expense
- **Form:** `/expenses/create`
- **Fields:** Expense Account, Amount, Date, Description
- **Database Entries:**
  - `expenses`: New expense
  - `journal_entries`: New entry
  - `journal_entry_lines`:
    - Debit: Selected Expense Account
    - Credit: Cash/Bank

### 6. Add Purchase (from Vendor)
- **Form:** `/purchases/create`
- **Fields:** Vendor, Items (product, qty, price), Total, Paid Amount
- **Database Entries:**
  - `purchases`, `purchase_items`: New purchase and lines
  - `journal_entries`: New entry
  - `journal_entry_lines`:
    - Debit: Inventory (Asset)
    - Credit: Accounts Payable (Liability)
    - (If paid) Debit: Accounts Payable, Credit: Bank/Cash

### 7. Add Sale (to Customer)
- **Form:** `/sales/create`
- **Fields:** Customer, Items, Total, Received Amount
- **Database Entries:**
  - `sales`, `sale_items`: New sale and lines
  - `journal_entries`: New entry
  - `journal_entry_lines`:
    - Debit: Accounts Receivable (Asset)
    - Credit: Revenue (Income)
    - (If received) Debit: Bank/Cash, Credit: Accounts Receivable
    - Debit: Cost of Goods Sold (Expense), Credit: Inventory (Asset)

### 8. Add Stock Adjustment
- **Form:** `/stock-adjustments/create`
- **Fields:** Product, Qty, Reason
- **Database Entries:**
  - `stock_adjustments`, `stock_adjustment_items`: New adjustment
  - `journal_entries`: New entry
  - `journal_entry_lines`:
    - Debit or Credit: Inventory (Asset) depending on increase/decrease
    - Offset: Inventory Shrinkage/Adjustment (Expense or Income)

### 9. Add Customer Receipt / Vendor Payment
- **Customer Receipt Form:** `/customer-receipts/create`
  - Debit: Bank/Cash
  - Credit: Accounts Receivable
- **Vendor Payment Form:** `/vendor-payments/create`
  - Debit: Accounts Payable
  - Credit: Bank/Cash

---

## Expanded Table: Forms, Actions, and Database Impact

| Form / Action                    | Main Tables Affected          | Journal Entries? | Accounts Used (Typical)                          |
|----------------------------------|-------------------------------|------------------|-------------------------------------------------|
| Add Customer (with balance)      | customers, journal_entries, journal_entry_lines | Yes              | AR, Equity                                      |
| Add Vendor (with balance)        | vendors, journal_entries, journal_entry_lines   | Yes              | AP, Equity                                      |
| Add Inventory (with qty)         | inventories, journal_entries, journal_entry_lines | Yes              | Inventory, Equity                               |
| Add Bank Account (with balance)  | banks, journal_entries, journal_entry_lines     | Yes              | Bank, Equity                                    |
| Add Expense                      | expenses, journal_entries, journal_entry_lines  | Yes              | Expense, Cash/Bank                              |
| Add Purchase                     | purchases, purchase_items, journal_entries, journal_entry_lines | Yes | Inventory, AP, Bank/Cash                        |
| Add Sale                         | sales, sale_items, journal_entries, journal_entry_lines | Yes | AR, Revenue, COGS, Inventory, Bank/Cash         |
| Add Stock Adjustment             | stock_adjustments, stock_adjustment_items, journal_entries, journal_entry_lines | Yes | Inventory, Adjustment/Shrinkage                 |
| Customer Receipt                 | customer_receipts, journal_entries, journal_entry_lines | Yes | Bank/Cash, AR                                   |
| Vendor Payment                   | vendor_payments, journal_entries, journal_entry_lines | Yes | AP, Bank/Cash                                   |
| Add Category/Unit (any module)   | *_categories, units           | No               | –                                               |

---

## Notes on Account Logic
- **Opening balances** always use the relevant Asset/Liability/Bank/Inventory account and an Equity or Opening Balances account for offset.
- **All monetary transactions** generate a journal entry and lines for double-entry compliance.
- **Every form submission is traceable via reference codes and journal linkage.**
- **Reports** (Trial Balance, Ledger, etc.) are generated from journal tables and show the impact of all forms above.

---

*This expanded report covers detailed data flows, example entries, and the impact of every major form. For even more detail (field-by-field, sample SQL, or code walkthroughs), just ask!*
