# 🧾 Grocery Store Accounting Web App (Pakistan)

This project is a Laravel-based accounting web application built specifically for grocery stores in Pakistan. It follows double-entry bookkeeping principles and provides complete traceability for every financial transaction.

## 🧱 Tech Stack

- **Framework:** Laravel 10+
- **Database:** MySQL
- **Hosting:** Hostinger (shared)
- **Frontend:** Blade (or optional Vue/React later)
- **Helper:** ChatGPT 4.1 + Windsurf

---

## 📊 Core Accounting Principles

- Every transaction (sale, purchase, expense, etc.) generates a **journal entry**
- Based on **double-entry accounting**
- All transactions are **traceable** via ledgers
- **Unique reference codes** for each form (e.g., INV-000001)
- Data is localized for **Pakistani currency: Rs.**

---

## 📦 Modules & Phases

### ✅ Phase 1: Core Setup & Chart of Accounts
You are helping build a full accounting web app for grocery stores in Pakistan using Laravel and MySQL. The app must follow double-entry accounting principles with complete traceability of all transactions via journal entries.

We're working on **Phase 1: Core Setup**.

---

## 🧱 Goals for Phase 1:

1. Create database structure for:
   - Chart of Accounts
   - Journal Entries
   - Journal Entry Lines

2. Seed standard account types:
   - Assets, Liabilities, Equity, Income, Expenses
   - Add default accounts like Cash, Inventory, Sales Revenue, Purchases, Capital, etc.

3. Add opening balance capability via journal entries

---

## 📊 Chart of Accounts

Each account will have:
- id
- name
- code (e.g., 1001 for Cash)
- type (enum: Asset, Liability, Equity, Income, Expense)
- parent_id (for account tree/hierarchy)
- is_group (true/false)
- opening_balance (optional)
- created_at / updated_at

---

## 🧾 Journal Entries Table

Fields:
- id
- entry_number (e.g., JRN-000001)
- date
- description
- created_by (user_id)
- created_at / updated_at

---

## 💳 Journal Entry Lines Table

Each journal entry can have multiple lines (debit or credit).

Fields:
- id
- journal_entry_id (FK)
- account_id (FK to chart_of_accounts)
- debit (nullable)
- credit (nullable)
- description (optional)

Sum of debit = sum of credit per journal entry (enforce validation rule)

---

## 💡 Important Rules

- When a new customer/vendor/bank/product is added with an opening balance, a journal entry must be created automatically.
- Every financial form (sales, purchase, payment, etc.) will generate a journal entry — this is the core principle of the app.
- Use Laravel migrations, models, seeders where needed.

---

## 🎯 Output Requirements:

- Migrations for the 3 tables listed above
- Models with relationships (e.g., JournalEntry hasMany JournalLines)
- Seeder for default chart of accounts
- (Optional) Validation rule for journal entry balancing


### ✅ Phase 2: Master Data — Products, Units, Categories
- Units: KG, Grams, Pcs, Pack, etc.
- Product categories: Rice, Bread, etc.
- Products with unit, category, pricing
- Opening inventory entries

### ✅ Phase 3: Master Data — Customers, Vendors, Banks
- Separate tables for:
  - Customers (`CUS-000001`)
  - Vendors (`VEN-000001`)
  - Bank/Cash Accounts
- Opening balances (with journal entries)

### ✅ Phase 4: Sales Module
- Sales invoice (`INV-000001`)
- Product selection, quantity, discount, tax
- Customer payments (`RCV-000001`)
- Customer ledger, sales reports

### ✅ Phase 5: Purchase Module
- Purchase bills (`PUR-000001`)
- Vendor payments (`PMT-000001`)
- Vendor ledger, purchase reports

### ✅ Phase 6: Expense Module
- Expense form (`EXP-000001`)
- Select expense account from chart
- Full expense ledger and tracking

### ✅ Phase 7: Banking & Transfers
- Bank-to-bank and bank-to-cash transfers
- Cash withdrawals/deposits
- Bank/cash ledgers and reconciliation

### ✅ Phase 8: Reports & Financials
- Trial Balance
- Profit & Loss
- Balance Sheet
- Inventory Report
- Customer/Vendor ledgers
- Journal listing

### ✅ Phase 9: User Roles & Permissions
- Roles: Owner (full control), Admin, Cashier, etc.
- Permissions: `create_sale`, `view_report`, `edit_product`, etc.
- Role → permission mapping
- Route & UI protection using Laravel Policies/Gates

---

## 🔐 User System

- Owner has full access (super admin, cannot be deleted)
- Staff access controlled by roles/permissions
- Owner can define custom roles and assign permissions

---

## 📁 Code Conventions

- Prefix IDs with module code:
  - Customers: `CUS-000001`
  - Vendors: `VEN-000001`
  - Invoices: `INV-000001`
  - Purchases: `PUR-000001`
  - Payments: `RCV-000001` / `PMT-000001`
  - Expenses: `EXP-000001`
  - Journal Entries: `JRN-000001`
- All forms log entries to `journal_entries` table
- Transactions must link to proper accounts from COA

---

## 📌 Localization

- Currency: **Rs. (Pakistani Rupees)**
- Tailored for small-to-medium grocery stores
- Measurement units relevant to grocery inventory

---

## 🧠 AI Context

This project is being built using **ChatGPT 4.1 + Windsurf** for live code generation and iteration. The entire system is designed for accuracy, traceability, and simplicity.

---

