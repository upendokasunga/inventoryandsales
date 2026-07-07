# ERP Functional Specification — Blueprint Document

> **System:** Multi-Branch Enterprise Resource Planning (ERP)  
> **Technology Stack:** Laravel 12, PHP 8.2, MySQL, Tailwind CSS, Alpine.js, Livewire  
> **Business Verticals:** Retail/Wholesale, Hospitality (Hotel/Restaurant/Bar), Gym, Printing, Light Manufacturing, General Commerce  
> **Base Currency:** TZS (Tanzanian Shilling)  
> **Document Version:** 1.0  
> **Date:** July 2026  

---

# Table of Contents

1. [System Overview](#1-system-overview)
2. [Complete Menu Hierarchy](#2-complete-menu-hierarchy)
3. [Business Workflows](#3-business-workflows)
   - 3.1 [Procurement to Payment](#31-procurement-to-payment)
   - 3.2 [Sales to Cash](#32-sales-to-cash)
   - 3.3 [Inventory Management](#33-inventory-management)
   - 3.4 [Finance & Accounting](#34-finance--accounting)
   - 3.5 [HR & Payroll](#35-hr--payroll)
   - 3.6 [CRM & Lead Management](#36-crm--lead-management)
   - 3.7 [Hospitality Operations](#37-hospitality-operations)
   - 3.8 [Fixed Assets](#38-fixed-assets)
   - 3.9 [Customer Care](#39-customer-care)
   - 3.10 [Marketing](#310-marketing)
   - 3.11 [Projects & Programs](#311-projects--programs)
   - 3.12 [Loyalty Program](#312-loyalty-program)
   - 3.13 [Gym Management](#313-gym-management)
   - 3.14 [Production / Stock Conversion](#314-production--stock-conversion)
   - 3.15 [Imprest (Petty Cash)](#315-imprest-petty-cash)
4. [Transaction Lifecycles](#4-transaction-lifecycles)
5. [Approval Workflows](#5-approval-workflows)
6. [User Roles & Permission Matrix](#6-user-roles--permission-matrix)
7. [Menu Access Matrix](#7-menu-access-matrix)
8. [Reports Catalogue](#8-reports-catalogue)
9. [Dashboard Components](#9-dashboard-components)
10. [Notifications](#10-notifications)
11. [Business Rules](#11-business-rules)
12. [Module Dependencies](#12-module-dependencies)
13. [Recommended Improvements](#13-recommended-improvements)

---

# 1. System Overview

## 1.1 Platform Architecture

| Component | Technology |
|-----------|-----------|
| Backend Framework | Laravel 12 |
| PHP Version | 8.2 |
| Database | MySQL |
| Frontend | Blade Templates, Tailwind CSS, Alpine.js |
| Real-time Components | Livewire v3 |
| PDF Generation | DomPDF (barryvdh/laravel-dompdf) |
| Excel Export | Maatwebsite/Laravel-Excel |
| RBAC | Spatie Laravel-Permission + Custom User Group system |
| Multi-Tenancy | Custom (separate databases per tenant) |
| Asset Bundling | Vite |

## 1.2 Multi-Branch Architecture

- Each branch operates as a separate entity within the same database
- Branch ID is stored in session and applied globally via `BelongsToBranch` trait
- Users can be assigned to multiple branches
- Data is scoped by branch for most transactions
- Inter-branch transfers are supported for inventory and cash

## 1.3 Supported Business Types

| Vertical | Features |
|----------|----------|
| Retail/Wholesale | Product sales, POS, invoicing, stock management |
| Hotel/Lodging | Room reservations, check-in/out, combined billing |
| Restaurant/Bar | Menu management, kitchen orders, bar orders, table management |
| Gym | Member management, subscriptions, attendance, trainer assignments |
| Print Shop | Job orders, department assignments, production workflow |
| Light Manufacturing | BOM/Formulas, stock conversion, batch processing |
| General Services | Service orders, project billing, contracts |

## 1.4 Document Numbering Convention

Documents use configurable prefixes with auto-incrementing sequences:

| Document | Default Prefix | Format |
|----------|---------------|--------|
| Invoice | `INV` | `INV-YEAR-SEQ` |
| Proforma | `PRO` | `PRO-YEAR-SEQ` |
| Purchase Order | `PO` | `PO-YEAR-SEQ` |
| GRN (Goods Receipt) | `GR` | `GR-LIB-ID` |
| Credit Note | `CRN` | `CRN-INVOICE-TIMESTAMP` |
| Journal Entry | Auto | `EXP-PAY-N`, `RCPT-INV`, `MT-ID`, `REV-REF` |
| Draft Invoice | `DRAFT` | `INV-DRAFT-YEAR-SEQ` |

---

# 2. Complete Menu Hierarchy

## 2.1 Dashboard

```
Dashboard
└── /dashboard
```

## 2.2 CRM

```
CRM
├── Dashboard              /crm
├── Prospects & Followup   /crm/prospect-followup
├── Sales Funnel           /crm/sales-funnel
├── Sales Funnel Report    /crm/sales-funnel-report
├── Lead Assignment        /crm/lead-assignment
├── Lead Conversion        /crm/lead-conversion
├── Followup Reason        /crm/followup-reason
├── Cold Calls
│   ├── Call Log           /crm/cold-call
│   ├── Bulk Initiation    /crm/cold-call/bulk-call-initiation
│   └── Call Log History   /crm/call-log
└── Lead Log               /crm/lead-log
```

## 2.3 Products / Master Data

```
Products
├── Products               /products
├── Sub-Products           /products/sub
├── Product Features       /products/features
├── Product Price          /products/price
├── Price History          /products/price-history
├── Product Types          /products/types
├── Categories             /products/category
├── Classifications        /products/classifications
├── Membership             /products/membership
├── Membership Log         /products/membership-log
├── Stores (Warehouses)    /products/stores
├── Reservation Rooms      /products/reservation-rooms
├── Payment Terms          /products/payment_terms
├── Course Management
│   ├── Course Main        /products/course-management/course-main
│   ├── Batch Main         /products/course-management/batch-main
│   ├── Class Routine      /products/course-management/class-routine
│   ├── Class Schedule     /products/course-management/class-schedule
│   ├── Online Class       /products/course-management/online-class
│   ├── Assignment         /products/course-management/assignment
│   ├── Attendance         /products/course-management/attendance
│   ├── Grading System     /products/course-management/grading-system
│   ├── Exam Management    /products/course-management/exam-management
│   ├── Exam Config        /products/course-management/exam-config
│   ├── Exam Result Config /products/course-management/exam-result-config
│   └── Exam Grade Config  /products/course-management/exam-grade-config
└── Attendance Config      /products/attendance-config
```

## 2.4 Procurement

```
Procurement
├── Dashboard              /procurement
├── Quotation Request      /procurement/quotation-request
├── Quotation Register     /procurement/quotation-register
├── Comparative Statement  /procurement/comparative-statement
├── Purchase Orders
│   ├── Pending List       /purchase-orders
│   ├── Drafts             /purchase-orders/drafts
│   ├── Approved           /purchase-orders/approved
│   ├── Rejected           /purchase-orders/rejected
│   └── Reversed           /purchase-orders/reversed
├── Goods Received Note    /stock/receive
├── Received Goods List    /stock/received
├── Service Orders
│   ├── Pending List       /service-orders
│   ├── Approved           /service-orders/approved
│   ├── Confirm Delivery   /service-orders/{id}/confirm-delivery
│   └── Deliveries         /service-orders/deliveries/confirmed
├── Vendor Management
│   ├── Suppliers          /suppliers
│   ├── Vendor Categories  /procurement/vendor-category
│   └── Item Category      /procurement/item-category
├── Item Setup             /procurement/item-setup
├── Item Stock             /procurement/item-stock
├── Item Price             /procurement/item-price
├── Item Price History     /procurement/item-price-history
├── Purchase Requisition   /procurement/purchase-requisition
├── Sales Order            /procurement/sales-order
├── Service Invoice        /procurement/service-invoice
├── Service Invoice Cash   /procurement/service-invoice-cash
└── E-Archive              /procurement/e-archive
```

## 2.5 Inventory / Stock

```
Inventory
├── Dashboard              /inventory
├── Warehouses             /inventory/warehouse
├── Available Stock        /stock/available
├── Stock Movements        /stock/movement
├── Low Stock Alerts       /stock/low
├── Store Requests
│   ├── All Requests       /stock/requests
│   ├── Pending Approval   /stock/requests/approve
│   ├── Approved           /stock/requests/approved
│   ├── To Issue           /stock/requests/to-issue
│   ├── Issued             /stock/requests/issued
│   ├── Rejected           /stock/requests/rejected-list
│   └── Transfers          /stock/requests/transfers
├── Stock Transfers        /inventory/stock-transfer
├── Stock Summary          /inventory/stock-summary
├── Stock Adjustment       /inventory/stock-adjustment
├── Stock Verification     /inventory/stock-verification
├── Stock Conversion       /stock/conversions
└── Price Management
    ├── Edit Prices        /stock/prices/edit
    └── Price History      /products/price-history
```

## 2.6 Sales & Invoicing

```
Sales
├── New Sale               /sales/new
├── New Proforma           /sales/proforma/new
├── Invoices
│   ├── All Invoices       /invoices
│   ├── Drafts             /invoices/drafts
│   ├── Proformas          /invoices/proformas
│   ├── Reversed           /invoices/reversed
│   └── Payments           /invoices/payments
├── Credit Notes           /invoices/credit-notes
├── Sales Dashboard        /sales/dashboard
├── Sales Orders           /sales/sales-order
├── Sales Order Approval   /sales/sales-order-approval
├── Vouchers               /sales/voucher
├── Voucher Approval       /sales/voucher-approval
├── Money Receipt          /sales/money-receipt
├── Customer Advances
│   ├── Receive Advance    /sales/advances/receive
│   ├── Return Advance     /sales/advances/return
│   ├── Advance Accounts   /sales/advances/accounts
│   └── Advance Balances   /sales/advances/balances
├── Delivery Challan       /sales/delivery-challan
├── Collection             /sales/collection
├── Coupons                /sales/coupon
├── Coupon Config          /sales/coupon-config
├── Package Config         /sales/package-config
├── Due Date Config        /sales/due-date-config
├── Refund Request         /sales/refund-request
├── Refund Approval        /sales/refund-request-approval
├── Credit Note Approval   /sales/credit-note-approval
└── Sales Targets          /sales/target
    ├── Achievement        /sales/achievement
    └── Commission         /sales/commission
```

## 2.7 Finance & Accounting

```
Finance
├── Dashboard              /finance
├── Chart of Accounts      /finance/chart-of-accounts
├── Accounts List          /finance/accounts-list
├── Voucher Entry          /finance/voucher-entry
├── Voucher Approval       /finance/approval
├── Contra Voucher         /finance/contra-voucher
├── Journal Voucher        /finance/journal-voucher
├── Payment Voucher        /finance/payment-voucher
├── Receive Voucher        /finance/receive-voucher
├── Purchase Voucher       /finance/purchase-voucher
├── Sales Voucher          /finance/sales-voucher
├── Fixed Asset Voucher    /finance/fixed-asset-voucher
├── Accounts Report        /finance/accounts-report
├── General Ledger         /finance/general-ledger
├── Trial Balance          /finance/trial-balance
├── Cash Book              /finance/cash-book
├── Bank Book              /finance/bank-book
├── Income Statement       /finance/income-statement
├── Balance Sheet          /finance/balance-sheet
├── Cash Flow              /finance/cash-flow
├── Fund Flow              /finance/fund-flow
├── Fixed Asset Schedule   /finance/fixed-asset-schedule
├── Receivables/Payables   /finance/receivables-payables
├── Receivable Aging       /finance/receivable-aging
├── Payable Aging          /finance/payable-aging
├── Bank Reconciliation    /finance/bank-reconciliation
├── Bank Transaction       /finance/bank-transaction
├── Budgeting              /finance/budgeting
├── Budget Report          /finance/budget-report
├── Manager Salary Setup   /finance/manager-salary
├── Salary Process         /finance/salary-process
└── Employee Salary Setup  /finance/employee-salary-setup
```

## 2.8 Accounting (Alternate Module)

```
Accounting
├── Dashboard              /accounting/dashboard
├── Chart of Accounts      /accounting/coa
├── Opening Balance        /accounting/opening-balance
├── Debit Voucher          /accounting/debit-voucher
├── Credit Voucher         /accounting/credit-voucher
├── Journal Voucher        /accounting/journal-voucher
├── Payment Voucher        /accounting/payment-voucher
├── Receive Voucher        /accounting/receive-voucher
├── Contra Voucher         /accounting/contra-voucher
├── Purchase Voucher       /accounting/purchase-voucher
├── Sales Voucher          /accounting/sales-voucher
├── Fixed Asset Voucher    /accounting/fixed-asset-voucher
├── Voucher Approval       /accounting/voucher-approval
├── Voucher List           /accounting/voucher-list
├── Voucher Report         /accounting/voucher-report
├── General Ledger         /accounting/general-ledger
├── Trial Balance          /accounting/trial-balance
├── Cash Book              /accounting/cash-book
├── Bank Book              /accounting/bank-book
├── Income Statement       /accounting/income-statement
├── Balance Sheet          /accounting/balance-sheet
├── Cash Flow Statement    /accounting/cash-flow-statement
├── Fund Flow Statement    /accounting/fund-flow-statement
├── Receivables/Payables   /accounting/receivable-payable
├── Receivable Aging       /accounting/receivable-aging
├── Payable Aging          /accounting/payable-aging
├── Bank Reconciliation    /accounting/bank-reconciliation
├── Bank Transaction       /accounting/bank-transaction
├── Budgeting              /accounting/budgeting
├── Budget Report          /accounting/budget-report
├── Budget Approval        /accounting/budget-approval
├── Cash Budget            /accounting/cash-budget
├── Cost Center            /accounting/cost-center
├── Tax Management         /accounting/tax
├── Salary
│   ├── Salary Setup       /accounting/salary-setup
│   ├── Salary Process     /accounting/salary-process
│   └── Salary Report      /accounting/salary-report
└── Loans
    ├── Loan Application   /accounting/loan-application
    ├── Loan Approval      /accounting/loan-approval
    ├── Loan Payment       /accounting/loan-payment
    └── Loan Report        /accounting/loan-report
```

## 2.9 Expense Management

```
Expense
├── Expense Entry          /expense/entry
├── Expense Category       /expense/category
├── Expense Approval       /expense/approval
└── Expense Report         /expense/report
```

## 2.10 HR & Employee Management

```
Human Resources
├── Dashboard              /hrm
├── Organization Chart     /hrm/org-chart
├── Employees
│   ├── Employee List      /hrm/employee
│   ├── Create Employee    /hrm/employee/create
│   └── Deleted Employees  /hrm/employee/trash
├── Employee Types         /hrm/employee-type
├── Employee Categories    /hrm/employee-category
├── Designations           /hrm/designation
├── Departments            /hrm/department
├── Sections               /hrm/section
├── ID Card Management     /hrm/id-card
├── Employee ID Card       /hrm/employee-id-card
├── Attendance
│   ├── Attendance Entry   /hrm/attendance
│   ├── Manual Attendance  /hrm/manual-attendance
│   └── Attendance Report  /hrm/attendance-report
├── Leave Management
│   ├── Leave Types        /hrm/leave-type
│   ├── Apply Leave        /hrm/leave-application
│   ├── Leave Approval     /hrm/leave-approval
│   └── Leave Register     /hrm/leave-register
├── Movement/Visit         /hrm/movement-visit
├── Payroll
│   ├── Payroll Runs       /hrm/payroll
│   ├── Payroll Processed  /hrm/payroll/processed
│   ├── Payroll Approved   /hrm/payroll/approved
│   ├── Payroll Reversed   /hrm/payroll/reversed
│   └── Payroll Report     /hrm/payroll/report
├── Salary Advances
│   ├── Request Advance    /hrm/salary-advances
│   ├── Pending Approvals  /hrm/salary-advances/pending
│   ├── Approved           /hrm/salary-advances/approved
│   └── Rejected           /hrm/salary-advances/rejected
├── Loans
│   ├── Loan List          /hrm/loan
│   ├── Loan Approval      /hrm/loan-approval
│   ├── Loan Payment       /hrm/loan-payment
│   └── Loan Report        /hrm/loan-report
├── Pay Items              /hrm/pay-items
└── Attendance Config      /hrm/attendance-config
```

## 2.11 Loan Management

```
Loan
├── Dashboard              /loan/dashboard
├── Loan Products          /loan/product
├── Loan Application       /loan/application
├── Loan Approval          /loan/approval
├── Loan Disbursement      /loan/disbursement
├── Loan Collection        /loan/collection
├── Loan Recovery          /loan/recovery
└── Loan Reports           /loan/report
```

## 2.12 Asset Management

```
Assets
├── Asset Category         /assets/asset-category
├── Asset List             /assets/asset-list
├── Asset Schedule         /assets/asset-schedule
├── Asset Disposal         /assets/asset-disposal
├── Asset Transfer         /assets/asset-transfer
└── Asset Report           /assets/asset-report
```

## 2.13 Customer Care

```
Customer Care
├── Data Record            /customer-care/data-record
├── Inquiry                /customer-care/inquiry
├── Complaint              /customer-care/complaint
├── Service Request        /customer-care/service-request
├── Follow Up              /customer-care/follow-up
├── Feedback               /customer-care/feedback
└── Report                 /customer-care/report
```

## 2.14 Marketing

```
Marketing
├── Campaigns              /marketing/campaigns
├── Channels               /marketing/channels
├── Events                 /marketing/events
├── Plans                  /marketing/plans
├── KPIs                   /marketing/kpis
├── Config                 /marketing/config
├── Reports                /marketing/reports
└── Review                 /marketing/review
```

## 2.15 Hospitality

```
Reservations
├── Calendar               /reservations/calendar
├── Bookings               /reservations/bookings
├── Check In               /reservations/check-in
├── Check Out              /reservations/check-out
├── Payments               /reservations/payments
├── Combined Billing       /reservations/combined-billing
├── Cancellations          /reservations/cancellations
└── Reports                /reservations/reports

Restaurant
├── Menu Categories        /restaurant/menu-categories
├── Menu Items             /restaurant/menu-items
├── Recipes                /restaurant/recipes
├── Food Costing           /restaurant/food-costing
├── Kitchen Orders
│   ├── Pending            /restaurant/kitchen/pending
│   ├── In Progress        /restaurant/kitchen/in-progress
│   └── Completed          /restaurant/kitchen/completed
├── Bar Orders
│   ├── Pending            /restaurant/bar/pending
│   └── Completed          /restaurant/bar/completed
└── Reports
    ├── Kitchen Report     /restaurant/kitchen-report
    ├── Bar Report         /restaurant/bar-report
    └── Analytics Report   /restaurant/analytics-report

Tables
├── Table Management       /tables
├── Reservations           /tables/reservations
├── Bill Management        /tables/bills
└── Combined Print         /tables/combined-print
```

## 2.16 Production

```
Production
├── Formulas (BOM)         /production/formulas
├── Cost Assignment        /production/cost-assignment
├── Batch Processing       /production/batch
├── Variation Management   /production/variation
├── Reports                /production/reports
└── Stock Conversion       /stock/conversions
```

## 2.17 Printing

```
Printing
├── Job Orders             /printing/job-orders
├── Departments/Machines   /printing/departments
├── Workflow Assignments   /printing/assignments
└── Close Confirmations    /printing/close
```

## 2.18 Gym Management

```
Gym
├── Members                /gym/members
├── Member Subscriptions   /gym/subscriptions
├── Member Attendance      /gym/attendance
├── Trainers               /gym/trainers
├── Trainer Assignments    /gym/assignments
└── Reports                /gym/reports
```

## 2.19 Loyalty Program

```
Loyalty
├── Loyalty Cards          /loyalty
├── Loyalty Tiers          /loyalty/setup
├── Tier Products          /loyalty/products
├── Assignments            /loyalty/assign
├── Redemptions            /loyalty/{card}/redeem
├── Point Lookup           /loyalty/lookup
└── Closed Cards           /loyalty/closed
```

## 2.20 SMS

```
SMS
├── Bulk SMS               /sms/bulk
├── SMS Config             /sms/config
├── Sent SMS               /sms/sent
├── SMS Templates          /sms/templates
└── SMS Groups             /sms/groups
```

## 2.21 Projects & Programs

```
Projects
├── Programs               /programs
├── Projects               /projects
├── Budget Management      /projects/budget
├── Budget Upload          /projects/budget/upload
├── Budget Approval        /projects/budget/review/approve
├── Budget Reports         /projects/budget/reports
└── Reports                /projects/reports
```

## 2.22 Customer Management

```
Customers
├── Customers              /customers
├── Customer Groups        /customers/groups
└── Quick Create           /customers/quick-create
```

## 2.23 Money Transfers

```
Money Transfers
├── Cash Transfers         /money-transfer
├── Branch Transfers       /money-transfer/branch
├── Approvals              /money-transfer/approve
├── Approved               /money-transfer/approved
└── Rejected               /money-transfer/rejected
```

## 2.24 Supplier Payments

```
Supplier Payments
├── Payment List           /supplier-payments
├── Advances               /supplier-payments/advance
├── Approvals              /supplier-payments/{id}/approve
└── Vouchers               /supplier-payments/voucher/{voucher}/print
```

## 2.25 Imprest (Petty Cash)

```
Imprest
├── Request                /imprest/request
├── Open Imprests          /imprest/open
├── Retire                 /imprest/retire
├── Retired List           /imprest/retired
├── Approved Retirements   /imprest/approved
├── Rejected               /imprest/rejected
└── Reverse                /imprest/{id}/reverse
```

## 2.26 Journals

```
Journals
├── Journal Entries        /journals
├── Adjustment Journal     /journals/adjustments
├── Balance Sheet Items    /journals/balance-items
└── Reports                /journals/reports
```

## 2.27 User & System Settings

```
System Settings
├── General Settings       /settings
├── Company Info           /settings/company-info
├── Branches               /settings/branches
├── Users                  /settings/users
├── User Groups            /settings/user-groups
├── Dashboard Cards        /settings/dashboard-cards
├── Approval Configuration /settings/approvals
├── Document Prefixes      /settings/document-prefixes
├── Payroll Settings       /settings/payroll
├── POS Settings           /settings/pos
├── Inventory Settings     /settings/inventory
├── Currencies             /settings/currencies
├── Email Config           /email-config
├── SMS Config             /sms/config
├── Payment Gateway        /payment-gateway
├── Modules                /modules
├── Language               /language
├── Theme Settings         /theme-settings
├── Barcode Generate       /config/barcode-generate
├── Barcode Settings       /config/barcode-settings
└── User Activity Log      /config/user-activity-log
```

## 2.28 Reports

```
Reports
├── Dashboard              /reports/dashboard
├── Sales Reports
│   ├── Sales Revenue      /reports/sales
│   ├── My Sales           /reports/my-sales
│   ├── Sales Person       /reports/sales-person-rep
│   ├── Sales Commissions  /reports/sales-commissions
│   ├── Sales Forecast     /reports/sales-forecast
│   ├── Sales Target       /reports/sales-target
│   ├── Price Changes      /reports/sales-price-changes
│   └── Posting Person     /reports/posting-person
├── Purchase Reports       /reports/purchase
├── Inventory Reports      /reports/inventory
├── Financial Reports
│   ├── Trial Balance      /financials/trial-balance
│   ├── Income Statement   /financials/income-statement
│   ├── Balance Sheet      /financials/balance-sheet
│   ├── Cash Flow          /financials/cash-flow
│   ├── General Ledger     /reports/gl
│   ├── Revenue vs Exp     /reports/revenue-vs-expenses
│   └── Financial Entries  /financials/entries
├── VAT Reports
│   ├── VAT Sales          /reports/vat/sales
│   ├── VAT Purchases      /reports/vat/purchases
│   ├── VAT VFD            /reports/vat/vfd
│   └── VAT Expenses       /reports/vat/expenses
├── Accounts Reports
│   ├── Expenses           /reports/expenses
│   ├── Debtors            /reports/debtors
│   ├── Creditors          /reports/creditors
│   └── Customer Statement /reports/customer-statement
├── HR Reports
│   ├── Attendance         /reports/attendance
│   ├── Employee List      /reports/employee-list
│   └── Employee Report    /reports/employee
├── Graph Reports
│   ├── Product Graph      /reports/graphs/product
│   ├── Customer Graph     /reports/graphs/customer
│   ├── Expenses Graph     /reports/graphs/expenses
│   ├── Revenue Graph      /reports/graphs/revenue
│   └── Product Perf.      /reports/graphs/product-performance
└── Student Reports        /reports/student
    ├── Student List       /reports/student-list
    ├── Student History    /reports/student-history
    └── Student Status     /reports/student-status
```

## 2.29 Additional Modules

```
Registration
├── Lead Entry             /registration/lead/entry
├── Lead Import            /registration/lead/import
├── Lead List              /registration/lead/list
├── Student Entry          /registration/student/entry
├── Student List           /registration/student/list
├── Transfer Student       /registration/student/transfer-entry
├── Enrollment             /registration/enrollment
├── Enrollment Acceptance  /registration/enrollment/accept
└── Inquiries              /registration/inquiries

Communication
├── Email                  /communication/email
├── Email Config           /communication/email-config
├── Notice                 /communication/notice
└── Notice Category        /communication/notice-category

Task Management
├── Task List              /task-management/task-list
├── My Task                /task-management/my-task
├── All Task               /task-management/all-task
├── Task Project           /task-management/task-project
└── Queue                  /task-management/queue

Config
├── Institute Config       /config/institute
├── Fiscal Year            /config/fiscal-year
├── Branch                 /config/branch
├── Branch Config          /config/branch-config
├── Session                /config/session
├── Class Room             /config/class-room
├── Building               /config/building
├── Floor                  /config/floor
├── Laboratory             /config/laboratory
├── Subject                /config/subject
├── Grade                  /config/grade
├── Holiday Setup          /config/holiday-setup
├── Event Setup            /config/event-setup
├── Certificate Type       /config/certificate-type
├── Certificate Template   /config/certificate-template
└── Print Config           /config/print-config

Exam
├── Exam List              /exam/list
├── Exam Schedule          /exam/schedule
├── Exam Attendance        /exam/attendance
├── Exam Results           /exam/results
├── Exam Grade             /exam/grade
├── Marks Entry            /exam/marks-entry
├── Marksheet              /exam/marksheet
└── Tabulation Sheet       /exam/tabulation

Support
├── Support Ticket         /support/ticket
├── Ticket Category        /support/ticket-category
├── Ticket Priority        /support/ticket-priority
├── FAQ                    /support/faq
├── FAQ Category           /support/faq-category
├── Knowledge Base         /support/knowledge-base
└── KB Category            /support/knowledge-base-category

Audit
├── Audit Trail            /audit/trail
├── Audit Log              /audit/log
├── Audit Config           /audit/config
└── Audit Report           /audit/report

Backup
├── Database Backup        /backup/database
├── File Backup            /backup/file
├── Backup Schedule        /backup/schedule
└── Backup Restore         /backup/restore

API / Integration
├── API Key Management     /api-integration/keys
├── Webhook Config         /api-integration/webhook
└── Integration Log        /api-integration/log

Notification
├── Notification List      /notification/list
├── Notification Template  /notification/template
├── Notification Config    /notification/config
└── Push Notification      /notification/push

Website / CMS
├── Pages                  /cms/pages
├── Page Builder           /cms/page-builder
├── Menu Builder           /cms/menu
├── Slider                 /cms/slider
├── Gallery                /cms/gallery
├── Blog                   /cms/blog
├── Blog Category          /cms/blog-category
├── Testimonial            /cms/testimonial
├── FAQ                    /cms/faq
├── Contact                /cms/contact
├── Subscriber             /cms/subscriber
├── Widget                 /cms/widget
└── Theme                  /cms/theme

E-Commerce
├── Shop Config            /ecommerce/config
├── Products               /ecommerce/products
├── Categories             /ecommerce/category
├── Orders                 /ecommerce/orders
├── Order Approval         /ecommerce/order-approval
├── Payments               /ecommerce/payments
├── Shipping               /ecommerce/shipping
├── Coupons                /ecommerce/coupons
└── Reviews                /ecommerce/reviews

Hostel
├── Hostel List            /hostel/list
├── Rooms                  /hostel/room
├── Beds                   /hostel/bed
├── Allocation             /hostel/allocation
├── Fees                   /hostel/fee
└── Reports                /hostel/report

Transport
├── Transport List         /transport/list
├── Routes                 /transport/route
├── Stops                  /transport/stop
├── Fees                   /transport/fee
├── Assign                 /transport/assign
└── Reports                /transport/report

Canteen
├── Food List              /canteen/food
├── Food Category          /canteen/food-category
├── Food Menu              /canteen/menu
├── Food Order             /canteen/order
└── Reports                /canteen/report

Library
├── Book List              /library/book
├── Book Category          /library/book-category
├── Book Issue             /library/book-issue
├── Book Return            /library/book-return
├── Member                 /library/member
└── Reports                /library/report

Visitor
├── Visitor Entry          /visitor/entry
├── Visitor Log            /visitor/log
├── Visitor Pass           /visitor/pass
└── Visitor Report         /visitor/report

Help
├── Documentation          /help/documentation
├── Changelog              /help/changelog
├── Contact Support        /help/contact
└── About                  /help/about
```

---

# 3. Business Workflows

## 3.1 Procurement to Payment

### Overview
The procurement process spans from identifying a need for goods/services through to supplier payment.

### Actors
| Role | Responsibilities |
|------|-----------------|
| Requester | Creates store request or purchase requisition |
| Store Keeper | Manages stock levels, issues goods |
| Procurement Officer | Creates purchase orders, manages suppliers |
| Approver (1-3 levels) | Approves store requests, purchase orders |
| Finance Officer | Processes supplier payments |
| Goods Receiver | Receives and inspects goods |

### Step-by-Step Workflow

```
Step 1: STORE REQUISITION
─────────────────────────
Trigger: Stock reaches minimum level OR department needs items not in stock

Who: Any authorized staff member

Action:
1. User navigates to Stock → Store Requests → Create
2. Selects source store (supplying warehouse)
3. Selects destination store (requesting warehouse/department)
4. Adds items with quantities
5. Submits request

Status: Pending Approval

─────────────────────────

Step 2: REQUISITION APPROVAL
────────────────────────────
Who: Store Manager / Department Head

Action:
1. User navigates to Stock → Store Requests → Pending Approval
2. Reviews requested items
3. Approves, Rejects, or Unapproves

If Approved: Status → Approved
If Rejected: Status → Rejected (terminal)

For configured multi-level approval:
  Level 1: Department Head approves → Status: Still Pending
  Level 2: Store Manager approves → Status: Approved
  Level 3: Director approves → Status: Approved

─────────────────────────

Step 3: GOODS ISSUANCE
──────────────────────
Who: Store Keeper

Action:
1. User navigates to Stock → Store Requests → To Issue
2. Opens approved request
3. Issues goods physically
4. Records quantities actually delivered
5. Generates Delivery Note (PDF)

Status: Issued
Effect: Stock deducted from source store

─────────────────────────

Step 4: GOODS RECEIPT (at destination)
─────────────────────────────────────
Who: Receiving Store Keeper

Action:
1. User navigates to Stock → Store Requests → Transfers → Received
2. Confirms receipt of transferred goods

Status: Received
Effect: Stock added to destination store

─────────────────────────

ALTERNATIVE PATH: PURCHASE ORDER (External Procurement)
───────────────────────────────────────────────────────

Step 1: PURCHASE ORDER CREATION
────────────────────────────────
Who: Procurement Officer

Action:
1. User navigates to Purchase Orders → Create
2. Selects supplier (or creates new)
3. Adds line items with quantities, unit prices
4. Configures VAT, discount, currency
5. Links to budget line (if required)
6. Selects payment account
7. Saves as Draft or Submits

If Save as Draft: Status → Draft
If Submit: Status → Pending

─────────────────────────

Step 2: PO APPROVAL
───────────────────
Who: Configurable Approver(s)

Action:
1. User navigates to Purchase Orders → Pending
2. Reviews PO details
3. Approves or Rejects

If Approved (Level 0 - No Approval): Status → Approved (auto)
If Approved (Level 1): Status → Approved
If Approved (Level 2+): Status → still pending until final step
If Rejected: Status → Rejected (terminal)

On Final Approval:
  - Validates budget sufficiency
  - Creates Supplier Payment record (status: paid)
  - Creates Payment Voucher
  - Creates Journal Entry (DR AP, CR Cash/Bank)
  - Commits budget

─────────────────────────

Step 3: GOODS RECEIPT (GRN)
───────────────────────────
Who: Goods Receiver / Store Keeper

Action:
1. User navigates to Stock → Receive
2. Selects approved Purchase Order
3. Enters quantities received per line item (partial receipt allowed)
4. Selects destination store
5. Adds remarks/reference
6. Submits receipt

Effect:
  - Creates GoodsReceipt record
  - Creates StockMovement (IN) - stock increases
  - Creates Supplier Payment (status: pending)
  - Creates Journal Entry (DR Inventory, CR AP)

Status: Goods Received (PO is updated; no separate status)

Reverse Path:
  - User can reverse GRN
  - Reverses accounting entries
  - Deletes stock IN movements
  - Soft-deletes receipt

─────────────────────────

Step 4: SERVICE CONFIRMATION (for service items)
─────────────────────────────────────────────────
Who: Service Requester

Action:
1. User navigates to Purchase Orders → Confirm Service
2. Confirms service delivery

Effect:
  - Creates Supplier Payment (pending)
  - Posts Journal Entry (DR Expense, CR AP)

─────────────────────────

Step 5: SUPPLIER PAYMENT
────────────────────────
Who: Finance Officer

Action:
1. User navigates to Supplier Payments
2. Reviews pending payments
3. Approves payment
4. Processes payment (marks as paid)

Status: Pending → Paid

Effect:
  - Creates Payment Voucher
  - Accounting: DR AP, CR Cash/Bank
```

### PO Reversal Rules
1. Only approved POs can be reversed
2. BLOCKED if any active (non-reversed) Goods Receipts exist
3. BLOCKED if any supplier payments beyond 'pending' status exist
4. Reversal triggers:
   - Reverses all related journal entries
   - Deletes pending supplier payments
   - Reverts budget commitment
   - Sets status = 'reversed'

## 3.2 Sales to Cash

### Overview
The sales process from customer inquiry through to cash collection.

### Actors
| Role | Responsibilities |
|------|-----------------|
| Sales Person | Creates sales, manages customers |
| Customer | Purchaser of goods/services |
| Approver | Approves sales (if configured) |
| Store Keeper | Issues goods for sales |
| Finance | Manages payments, credit notes |
| Kitchen/Bar Staff | Fulfills food/beverage orders |

### Step-by-Step Workflow

```
Step 1: QUOTATION / PROFORMA
─────────────────────────────
Who: Sales Person

Action:
1. User navigates to Sales → New Proforma
2. Selects customer (or CASH CUSTOMER)
3. Adds products/services
4. Sets pricing, discounts, VAT
5. Saves as proforma

Status: Proforma (no stock impact)

Proforma may be:
  - Converted to Invoice → creates actual sale
  - Closed (void) → archived
  - Left open → customer may return

─────────────────────────

Step 2: CREATE SALE / INVOICE
─────────────────────────────
Who: Sales Person / Cashier

Action:
1. User navigates to Sales → New Sale
2. Selects customer (or defaults to CASH CUSTOMER)
3. Selects store (from assigned stores)
4. Selects payment account (cash/bank)
5. Adds products/services:
   - Search and select products
   - Set quantities, unit prices
   - Apply discounts (per-line or global)
   - Set VAT rate
6. Selects currency (TZS default) + exchange rate
7. Selects cost center, project (optional)
8. Assigns salesperson
9. Sets payment terms
10. Applies customer advance (if available)
11. Redeems loyalty points (if applicable)
12. Records payment

On submission:
  - If Approval Level 0: Status → Posted (immediate)
  - If Approval Level 1: Status → Pending Issue
  - If Approval Level 2+: Status → Awaiting Approval

─────────────────────────

Step 3: SALES APPROVAL (if configured)
───────────────────────────────────────
Who: Authorized Approver

Action:
1. User navigates to Sales → Approval
2. Reviews sale details
3. Approves or Rejects

If Approved: Status proceeds toward Posted
If Rejected: Status → Rejected

─────────────────────────

Step 4: GOODS ISSUANCE (if applicable)
───────────────────────────────────────
Who: Store Keeper

For goods that need physical issue:
1. Store Request is automatically created
2. Store Keeper issues goods
3. Stock is deducted

─────────────────────────

Step 5: FULFILLMENT
───────────────────
System Actions:
  - Creates Kitchen Orders (for restaurant menu items)
  - Creates Bar Orders (for bar store items)
  - Creates Printing Job Orders (for printing products)
  - Accrues loyalty points
  - Processes customer advance usage
  - Creates receipt journals (if paid)

─────────────────────────

Step 6: PAYMENT COLLECTION
──────────────────────────
Who: Sales Person / Cashier

At time of sale:
  - Payment recorded directly
  - Status: Paid / Partial / Unpaid

After sale (for credit sales):
1. User navigates to Invoice → Payments
2. Records payment against invoice
3. Creates receipt journal (DR Cash/Bank, CR AR)
4. Invoice payment_status updates:
   - unpaid → partial (if partial payment)
   - unpaid → paid (if full payment)
   - partial → paid (if balance settled)

─────────────────────────

Step 7: CREDIT NOTE / RETURN
────────────────────────────
Who: Authorized Staff

Path A - Goods Return:
1. User navigates to Invoice → Returns
2. Selects invoice
3. Selects returned items + quantities
4. System validates return qty ≤ invoiced qty
5. Stock IN movement created
6. Credit Note journal created (CRN reference)

Path B - Discount / Financial Adjustment:
1. User navigates to Invoice → Discount
2. Applies discount (percent or amount)
3. Credit Note journal created
4. Optional: Write-off remaining balance (Bad Debt)

─────────────────────────

Step 8: INVOICE REVERSAL
────────────────────────
Who: Authorized Staff

Requirements:
  - No issued Store Requests
  - No active payments (must reverse payments first)

Actions:
  - Reverses AR (CR AR, DR Income)
  - Reverses discount and VAT
  - Reverses stock movements
  - Reverses loyalty accruals
  - Status → Reversed
```

### Invoice Statuses

```
                         ┌─→ Draft ──→ Submit ──┐
                         │                       │
New Sale ────────────────┤                       ├─→ Pending Issue (Level 1)
                         │                       │
                         └─→ Direct Post ────────┤
                                                 ├─→ Awaiting Approval (Level 2+)
                                                 │
                           Approve ──────────────┘
                               │
                               ├─→ Posted ──→ Payment ──→ Paid/Partial
                               │
                               ├─→ Reversed (after payment reversed)
                               │
                               └─→ Edit/Update
```

## 3.3 Inventory Management

### Overview
Inventory management encompasses stock control across multiple stores/warehouses.

### Store Types
| Type | Code | Description |
|------|------|-------------|
| Goods | `goods` | General merchandise |
| Bar | `bar` | Beverages and bar supplies |
| Kitchen Materials | `kitchen_materials` | Food and kitchen supplies |
| Fixed Asset | `fixed_asset` | Fixed assets (not sellable) |

### Stock Movement Types
| Reference Type | Direction | Description |
|---------------|-----------|-------------|
| `goods_receipt` | IN | Stock received from purchase order |
| `store_request` | OUT | Goods issued to another store |
| `store_request` | IN | Goods received from another store |
| `invoice` | OUT | Goods sold to customer |
| `bar_order` | OUT | Bar consumption |
| `kitchen_order` | OUT | Kitchen consumption |
| `stock_adjustment` | IN/OUT | Inventory count correction |
| `stock_conversion` | OUT | Raw materials consumed in production |
| `stock_conversion` | IN | Finished goods from production |

### Key Workflows

```
STORE REQUEST (Internal Requisition)
────────────────────────────────────
Create Request → Pending Approval → Approved → Issued → Received
                    ↘ Rejected

STOCK TRANSFER (Inter-branch)
─────────────────────────────
Create Transfer (cross-branch) → Pending → Approved → Issued → Received (at destination)

STOCK ADJUSTMENT (Count Correction)
───────────────────────────────────
Select Store → System shows current quantities → Enter counted quantities
  → System calculates difference → Add remarks/reason
  → Creates Stock Movement (IN if increase, OUT if decrease)
  → Creates Journal Entry (DR COGS/Loss or CR Inventory Gain)

STOCK CONVERSION (Production)
────────────────────────────
Select Formula (BOM) → System shows required raw materials
  → Expected finished output quantities
  → Confirm conversion
  → Raw materials deducted (OUT)
  → Finished goods added (IN)

LOW STOCK ALERTS
────────────────
System checks products with stock below configured minimum levels
→ Displayed on dashboard and /stock/low page
```

### Stock Valuation
- Average costing method
- Calculated from `goods_receipt_items`: total cost / total qty received per product+store
- Used for COGS calculation

## 3.4 Finance & Accounting

### Overview
Double-entry accounting system supporting full financial management.

### Chart of Accounts Structure
```
Assets (Base Type: asset)
├── Current Assets
│   ├── Cash
│   ├── Bank Accounts
│   ├── Accounts Receivable
│   ├── Inventory
│   └── Prepaid Expenses
└── Non-Current Assets
    ├── Fixed Assets
    └── Intangible Assets

Liabilities (Base Type: liability)
├── Current Liabilities
│   ├── Accounts Payable
│   ├── VAT Payable
│   ├── WHT Payable
│   └── Customer Advances
└── Non-Current Liabilities
    └── Loans Payable

Equity (Base Type: equity)
├── Owner's Capital
├── Retained Earnings
└── Current Year Earnings

Income (Base Type: income)
├── Revenue
├── Discount Allowed
└── Other Income

Expenses (Base Type: expense)
├── Cost of Goods Sold
├── Operating Expenses
├── Administrative Expenses
└── Depreciation
```

### Voucher Types
| Voucher | Type Code | Description |
|---------|-----------|-------------|
| Debit Voucher | DR | For cash payments |
| Credit Voucher | CR | For cash receipts |
| Journal Voucher | GENJ/ADJ | For non-cash adjustments |
| Payment Voucher | PMT | For supplier payments |
| Receive Voucher | RCPT | For customer receipts |
| Contra Voucher | CTR | For transfers between cash/bank |
| Purchase Voucher | PUR | For credit purchases |
| Sales Voucher | SAL | For credit sales |
| Fixed Asset Voucher | FA | For asset acquisitions |

### Voucher Lifecycle
```
General Journal (GENJ/BNKJ):
  Create → Posted (immediate) → Reversible

Adjustment Journal (ADJ):
  Create → Submitted → Approved (by Finance Manager) → Posted → Reversible

Expense:
  Create → Pending → Approved → Paid → Reversible
                 ↘ Rejected

Money Transfer:
  Create → Pending → Approved → Posted (GL entry)
                 ↘ Rejected
```

### Banking

**Bank Reconciliation:**
1. Create reconciliation → status: draft
2. Add reconciliation items:
   - Deposits in transit
   - Outstanding cheques
   - Bank charges
   - Interest earned
   - Other adjustments
3. Submit → status: pending_approval (requires difference = 0)
4. Approve → status: approved

## 3.5 HR & Payroll

### Employee Lifecycle
```
Create Employee → Active → Leave/Inactive → Terminated
                     ↘ Soft-Delete → Restorable
```

### Payroll Processing Lifecycle
```
Step 1: CREATE PAYROLL RUN
──────────────────────────
Who: HR / Payroll Officer

Action:
1. Select run date
2. Select project/budget allocation
3. Select employees to include
4. System validates no duplicate month run
5. Save as draft

Status: Draft

──────────────────────────

Step 2: SUBMIT FOR APPROVAL
───────────────────────────
Who: Payroll Officer

Action: Submits payroll run

Status: Pending Approval

──────────────────────────

Step 3: APPROVAL (1-3 levels)
─────────────────────────────
Who: Configurable Approvers

Action:
1. Review payroll calculations
2. Approve or Reject

If Approved: Status → Approved
If Rejected: Status → Rejected

──────────────────────────

Step 4: PAYMENT PROCESSING
──────────────────────────
Who: Finance Officer

Action:
1. Review payroll payments
2. Process payments (NSSF, SDL, PAYE, WCF, NHIF, NET)
3. Issue payments

Status: Processed

──────────────────────────

Step 5: PAYSLIP GENERATION
──────────────────────────
System generates individual payslips
Employee can view via Staff Self-Service portal
```

### Payroll Calculation
```
Gross Salary
+ Other Allowances
+ Overtime Pay
- NSSF Employee Contribution
- PAYE (Pay As You Earn)
- NHIF Employee Contribution
- Salary Advance Deductions
- Absence Deductions
- Other Deductions
= Net Pay
```

### Employer Contributions
- NSSF Employer Contribution
- NHIF Employer Contribution
- WCF (Workers Compensation Fund)
- SDL (Skills Development Levy)

### Leave Management Lifecycle
```
Step 1: APPLY FOR LEAVE
───────────────────────
Who: Employee

Action:
1. Select leave type
2. Select start/end dates (auto-calculates days)
3. Add reason
4. Submit

Status: Pending

─────────────────────────

Step 2: LEAVE APPROVAL
──────────────────────
Who: Manager / Supervisor

Action:
1. Review application
2. Approve or Reject

If Approved: Status → Approved
  - Employee leave balance reduced
If Rejected: Status → Rejected
  - Reason required

─────────────────────────

Step 3: LEAVE ADJUSTMENT (if needed)
───────────────────────────────────
Who: Admin

Action:
1. Select approved leave application
2. Adjust days
3. Submit for approval

Status: Adjustment Pending → Adjustment Approved
```

### Salary Advance Lifecycle
```
Step 1: REQUEST
───────────────
Who: Employee

Action: Request advance (amount, reason)

Status: Pending

─────────────────────────

Step 2: APPROVAL
────────────────
Who: Manager

Action:
1. Set deduction plan (amount/month, months, start month)
2. Approve or Reject

If Approved: Status → Approved
If Rejected: Status → Rejected

─────────────────────────

Step 3: ADJUSTMENT (if needed)
─────────────────────────────
Who: Admin

Action: Modify deduction plan
→ Creates adjustment record
→ Requires approval
```

### Attendance Capture Methods
1. **Manual Entry:** HR enters check-in/check-out times
2. **QR Code Check-in:** Employee scans QR code at kiosk
3. **Export/Import:** Bulk upload via CSV/Excel

## 3.6 CRM & Lead Management

### Lead Lifecycle
```
Lead Creation
     │
     ▼
Lead Stages (Configurable Pipeline)
  ├── Stage 1: New Lead
  ├── Stage 2: Contacted
  ├── Stage 3: Qualified
  ├── Stage 4: Proposal
  ├── Stage 5: Negotiation
  └── Stage 6: Won/Lost
     │
     ▼
Conversion → Customer
  OR
Lost → Archive
```

### Key CRM Workflows
1. **Cold Calling:** Bulk call initiation → call log → lead assignment
2. **Prospect Follow-up:** Schedule follow-ups → log interactions
3. **Sales Funnel:** Visual pipeline management
4. **Lead Assignment:** Assign leads to sales reps
5. **Lead Conversion:** Convert leads to customers (creates customer record)

## 3.7 Hospitality Operations

### Hotel Reservations
```
Guest Inquiry → Create Booking → Check In → Stay → Check Out → Payment → Invoice
                    ↘ Cancel

Combined Billing:
  - Room charges
  - Restaurant charges
  - Bar charges
  - Other services
  → Single consolidated invoice
```

### Restaurant Operations
```
Customer Places Order
  → Kitchen Order Created (for food items)
  → Bar Order Created (for beverage items)
  → Kitchen Prepares → Ready → Served
  → Bar Prepares → Ready → Served
  → Bill Generated → Payment
```

### Table Management
```
Table Statuses:
  Available → Reserved → Occupied → Ordering → Billing → Available
```

## 3.8 Fixed Assets

### Asset Lifecycle
```
Define Depreciation Methods
  ↓
Receive Asset from Purchase Order
  ↓
Register Asset (tag, name, cost, useful life)
  ↓
Calculate Depreciation (straight-line, declining balance)
  ↓
Transfer Asset (between departments/locations)
  ↓
Dispose Asset
```

## 3.9 Customer Care

### Workflow Types
1. **Inquiry:** Customer question → Log → Respond → Close
2. **Complaint:** Customer issue → Log → Assign → Investigate → Resolve → Close
3. **Service Request:** Customer request → Log → Assign → Fulfill → Close
4. **Feedback:** Customer feedback → Log → Review → Action
5. **Follow-up:** Scheduled follow-up → Contact → Log outcome

## 3.10 Marketing

### Campaign Lifecycle
```
Create Campaign → Define Channels → Set KPIs → Execute → Track → Report
```

### Marketing Calendar
Visual calendar showing:
- Campaigns
- Events
- Plans
- Reviews

## 3.11 Projects & Programs

### Program Lifecycle
```
Create Program → Add Projects → Manage Budgets → Track Progress → Close
```

### Project Budget Lifecycle
```
Create Budget → Add Budget Lines → Submit for Review
  → Approved → Track Spending (committed vs actual)
  → Revised (if changes needed)
  → Rejected (returned for revision)
  → Roll Budget (carry forward to next year)
```

### Budget Line Fields
- Account ID
- Frequency
- Duration
- Committed Amount
- Spent Amount

## 3.12 Loyalty Program

### Loyalty Card Lifecycle
```
Issue Card → Assign to Customer → Accrue Points → Redeem Points → Close
```

### Tier Structure
```
Tier 1 (Bronze) → Tier 2 (Silver) → Tier 3 (Gold) → Tier 4 (Platinum)
```

### Points Accrual
- Points earned based on tier's `points_per_unit_spend`
- Eligible product subtotal only
- Points have expiry dates
- Deferred revenue journal posted for accrued points

### Points Redemption
- Redeem points during checkout
- Reduces invoice total
- Lookup by card number

## 3.13 Gym Management

### Member Lifecycle
```
Register Member → Create Subscription → Track Attendance → Renew/Expire
```

### Trainer Management
```
Register Trainer (auto-code) → Assign Members → Track Sessions
```

### Gym Reports
- Member list
- Attendance tracking
- Subscription status
- Trainer performance

## 3.14 Production / Stock Conversion

### Formula (BOM) Lifecycle
```
Create Formula → Define Raw Materials → Define Finished Output
  → Cost Assignment → Run Conversion → Track Batch
```

### Batch Processing
```
Create Batch → Select Formula → Input Quantities → Process
  → Raw Materials Consumed (stock OUT)
  → Finished Goods Produced (stock IN)
  → Batch Record Created
```

## 3.15 Imprest (Petty Cash)

### Imprest Lifecycle
```
Step 1: REQUEST IMPREST
───────────────────────
Who: Employee

Action: Request cash advance

Status: Pending

─────────────────────────

Step 2: APPROVE
───────────────
Who: Manager

Action: Approve or Reject

If Approved: Cash issued to employee
Status: Open

─────────────────────────

Step 3: RETIRE IMPREST
──────────────────────
Who: Employee

Action: Submit receipts/expenses against imprest

Status: Retired (pending approval)

─────────────────────────

Step 4: APPROVE RETIREMENT
──────────────────────────
Who: Manager / Finance

Action:
1. Review expenses
2. Approve or reject retirement

If Approved:
  - Expenses booked
  - If surplus: employee refunds
  - If deficit: additional claim paid

─────────────────────────

Step 5: REFUND / ADDITIONAL CLAIM
─────────────────────────────────
If employee owes money:
  → Employee refunds unused cash

If additional amount owed to employee:
  → Additional claim payment processed
```

---

# 4. Transaction Lifecycles

## 4.1 Purchase Order (LPO)

```
                         ┌─→ Edit ──→ ──┐
                         │               │
Create ──→ Draft ────────┴── Submit ────┴──→ Pending
                         │                    │
                         └── Submit ──────────┤
                                              │
                         Approval Step 1 ─────┤
                         Approval Step 2 ─────┤
                         Approval Step 3 ─────┤
                                              │
                 ┌────────────────────────────┤
                 │                            │
                 ▼                            ▼
            Approved                      Rejected
                 │                            │
                 │                       [Terminal]
          ┌──────┴──────┐
          │              │
     Receive Goods   Confirm Service
          │              │
          ▼              ▼
    Goods Receipt   Service Delivery
    (GRN created)   (Confirmed)
          │              │
          ▼              ▼
    Supplier Payment (Pending)
          │
          ▼
    Supplier Payment (Paid)
          │
     ┌────┴────┐
     │         │
   Close    Reverse
               │
               ▼
          Reversed
        [Terminal]
```

## 4.2 Goods Receipt (GRN)

```
Approved PO Exists
      │
      ▼
Select PO → Enter Received Quantities → Select Store
      │
      ▼
GRN Created
  → Stock Movement (IN)
  → Supplier Payment (Pending)
  → Journal Entry (DR Inventory, CR AP)
      │
      ├──→ Reverse
      │       → Reverse Journal Entry
      │       → Delete Stock Movement
      │       → Soft-Delete GRN
      │
      └──→ [Terminal]
```

## 4.3 Sales Invoice

```
                         ┌─→ Draft ──── Submit ──┐
                         │                        │
New Sale ────────────────┤                        ├──→ Awaiting Approval (Level 2+)
                         │                        │
                         └─→ Direct Post ─────────┤
                                                  │
                         Approve (if needed) ─────┤
                                                  │
                    ┌─────────────────────────────┘
                    │
                    ▼
                Posted
              (or Pending Issue for Level 1)
                    │
              ┌─────┴──────────┬──────────┐
              │                 │          │
         Payment          Goods Issue  Fulfillment
         Received         (if goods)   (Kitchen/Bar)
              │                 │          │
              ▼                 ▼          ▼
       Status Updated    Stock OUT    Orders Created
       (Paid/Partial)
              │
              ├──→ Credit Note / Return
              │        → Stock IN (for goods return)
              │        → CRN Journal (for discount)
              │
              ├──→ Edit
              │        → Adjust quantities/prices
              │        → Rebuild journal entries
              │        → Adjust stock movements (delta)
              │
              └──→ Reverse
                       → Must reverse payments first
                       → Must reverse Store Requests first
                       → Reverse AR, Income, VAT
                       → Reverse stock movements
                       → Reverse loyalty
                       → Status: Reversed
```

## 4.4 Store Request (Internal Requisition)

```
Create Request
      │
      ▼
   Pending
      │
      ├──→ Approval Step 1
      │        │
      │        ├──→ Approval Step 2
      │        │        │
      │        │        ├──→ Approval Step 3
      │        │        │        │
      │        │        │        ▼
      │        │        │    Approved
      │        │        ▼
      │        │    Approved
      │        ▼
      │    Approved
      │
      ├──→ Unapprove → back to Pending
      │
      └──→ Reject → Rejected [Terminal]
                      │
                      ▼
                   Issue Goods
                      │
                      ▼
                   Issued
                 (Stock OUT)
                      │
                      ▼
              Receive at Destination
                      │
                      ▼
                  Received
                (Stock IN)
                      │
                      ├──→ Reverse (returns goods to source)
                      │
                      └──→ [Terminal]
```

## 4.5 Expense

```
Create Expense
      │
      ▼
   Pending
      │
      ├──→ Approve (Level 0 = Auto)
      │        │
      │        ▼
      │    Approved
      │        │
      │        ▼
      │     Paid
      │     (Journal: DR Expense, CR Bank/Cash)
      │        │
      │        ├──→ Reverse
      │        │        → Reverse Journal
      │        │        → Status: Reversed
      │        │
      │        └──→ [Terminal]
      │
      ├──→ Reject → Rejected [Terminal]
      │
      └──→ [Terminal]
```

## 4.6 Journal Entry

```
General / Bank Journal:
  Create → Posted (auto) → Reversible

Adjustment Journal:
  Create → Submitted → Approved (by Finance Manager) → Posted → Reversible
              ↘ Rejected
```

## 4.7 Payroll

```
Create Payroll Run (Draft)
      │
      ▼
   Submit
      │
      ▼
Pending Approval
      │
      ├──→ Approve Step 1
      │        │
      │        ├──→ Approve Step 2
      │        │        │
      │        │        ├──→ Approve Step 3
      │        │        │        │
      │        │        │        ▼
      │        │        │    Approved
      │        │        ▼
      │        │    Approved
      │        ▼
      │    Approved
      │        │
      │        ▼
      │   Processed (Payments issued)
      │        │
      │        ├──→ Reverse (after reversing payments)
      │        │
      │        └──→ [Terminal]
      │
      └──→ Reject → Rejected [Terminal]
```

## 4.8 Leave Application

```
Apply Leave
      │
      ▼
   Pending
      │
      ├──→ Approve
      │        │
      │        ├──→ Adjust Days
      │        │        │
      │        │        ├──→ Approve Adjustment
      │        │        └──→ Reject Adjustment
      │        │
      │        ▼
      │    Approved
      │    (Leave balance reduced)
      │
      └──→ Reject → Rejected [Terminal]
```

## 4.9 Salary Advance

```
Request Advance
      │
      ▼
   Pending
      │
      ├──→ Approve
      │        │
      │        ▼
      │    Approved
      │    (Deduction plan set)
      │        │
      │        ├──→ Adjust (modify deduction plan)
      │        │        │
      │        │        ├──→ Approve Adjustment
      │        │        └──→ Reject Adjustment
      │        │
      │        └──→ [Active - deducted via payroll]
      │
      └──→ Reject → Rejected [Terminal]
```

## 4.10 Imprest

```
Request Imprest
      │
      ▼
   Pending Approval
      │
      ├──→ Approve → Open (Cash issued)
      │        │
      │        ▼
      │    Retire (submit receipts)
      │        │
      │        ▼
      │   Retirement Pending Approval
      │        │
      │        ├──→ Approve Retirement
      │        │        │
      │        │        ├──→ Refund (if surplus)
      │        │        └──→ Additional Claim (if deficit)
      │        │
      │        └──→ Reject → Back to Open
      │
      ├──→ Reverse
      │
      └──→ Reject → Rejected [Terminal]
```

## 4.11 Bank Reconciliation

```
Create (Draft)
      │
      ▼
   Add Reconciliation Items
      │
      ▼
   Submit (requires difference = 0)
      │
      ▼
Pending Approval
      │
      ├──→ Approve → Approved [Terminal]
      │
      └──→ Reject → Back to Draft
```

## 4.12 Loyalty Card

```
Issue Card → Assign to Customer
      │
      ├──→ Accrue Points (via sales)
      │        │
      │        └──→ Redeem Points
      │
      └──→ Close Card [Terminal]
```

## 4.13 Customer Advance

```
Receive Advance Payment
      │
      ▼
Advance Account Credited
      │
      ├──→ Applied to Invoice (during sale)
      │        │
      │        ▼
      │   Advance Balance Reduced
      │
      ├──→ Returned to Customer
      │        │
      │        ▼
      │   Advance Balance Reduced to Zero
      │
      └──→ [Remains as Credit Balance]
```

## 4.14 Fixed Asset

```
Receive from Purchase Order
      │
      ▼
   Register Asset
      │
      ├──→ Calculate Depreciation (periodic)
      │        │
      │        └──→ Journal Entry (DR Depreciation, CR Accum. Depr.)
      │
      ├──→ Transfer (between departments/stores)
      │
      └──→ Dispose
               │
               ├──→ Sold (Journal: DR Cash, CR Asset, Gain/Loss)
               └──→ Scrapped (Journal: DR Loss, CR Asset)
```

---

# 5. Approval Workflows

## 5.1 Configurable Approval Modules

The following modules support configurable approval levels (0-3):

| Module Key | Default Level | Description |
|-----------|--------------|-------------|
| `sales` | 0 | Sales invoice posting |
| `pos` | 0 | Point of Sale transactions |
| `store` | 0 | Store requests (internal requisitions) |
| `expenses` | 0 | Expense claims |
| `leave` | 0 | Leave applications |
| `payroll` | 0 | Payroll runs |
| `purchase_orders` | 0 | Purchase orders |
| `service_orders` | 0 | Service orders |
| `salary_advance` | 0 | Salary advance requests |

## 5.2 Approval Level Definitions

```
Level 0: No Approval
  Action → Auto-approved → Immediate Effect
  (Used for low-value, low-risk transactions)

Level 1: Single-Step Approval
  Action → Pending → Approver → Approved/Rejected

Level 2: Two-Step Approval
  Action → Pending → Approver 1 → Approver 2 → Approved/Rejected

Level 3: Three-Step Approval
  Action → Pending → Approver 1 → Approver 2 → Approver 3 → Approved/Rejected
```

## 5.3 Purchase Order Approval Matrix

```
Level 1
┌─────────────────────┐
│ Store Manager       │
│ Department Head     │
└──────────┬──────────┘
           │
           ▼
Level 2
┌─────────────────────┐
│ Procurement Manager │
└──────────┬──────────┘
           │
           ▼
Level 3
┌─────────────────────┐
│ Finance Director    │
└──────────┬──────────┘
           │
           ▼
     Approved / Rejected
```

## 5.4 Expense Approval Matrix

```
Level 1
┌─────────────────────┐
│ Department Head     │
└──────────┬──────────┘
           │
           ▼
Level 2
┌─────────────────────┐
│ Finance Manager     │
└──────────┬──────────┘
           │
           ▼
Level 3
┌─────────────────────┐
│ Director / CFO      │
└──────────┬──────────┘
           │
           ▼
     Approved / Rejected
```

## 5.5 Sales / Invoice Approval Matrix

```
Level 1
┌─────────────────────┐
│ Sales Manager       │
└──────────┬──────────┘
           │
           ▼
Level 2
┌─────────────────────┐
│ Branch Manager      │
└──────────┬──────────┘
           │
           ▼
Level 3
┌─────────────────────┐
│ Finance Director    │
└──────────┬──────────┘
           │
           ▼
     Approved / Rejected
```

## 5.6 Payroll Approval Matrix

```
Level 1
┌─────────────────────┐
│ HR Manager          │
└──────────┬──────────┘
           │
           ▼
Level 2
┌─────────────────────┐
│ Finance Manager     │
└──────────┬──────────┘
           │
           ▼
Level 3
┌─────────────────────┐
│ Managing Director   │
└──────────┬──────────┘
           │
           ▼
     Approved / Rejected
```

## 5.7 Leave Approval Matrix

```
Level 1
┌─────────────────────┐
│ Direct Supervisor   │
└──────────┬──────────┘
           │
           ▼
Level 2
┌─────────────────────┐
│ Department Head     │
└──────────┬──────────┘
           │
           ▼
Level 3
┌─────────────────────┐
│ HR Manager          │
└──────────┬──────────┘
           │
           ▼
     Approved / Rejected
```

## 5.8 Store Request Approval Matrix

```
Level 1
┌─────────────────────┐
│ Store Manager       │
└──────────┬──────────┘
           │
           ▼
Level 2
┌─────────────────────┐
│ Operations Manager  │
└──────────┬──────────┘
           │
           ▼
Level 3
┌─────────────────────┐
│ Branch Manager      │
└──────────┬──────────┘
           │
           ▼
     Approved / Rejected
```

## 5.9 Special Permissions Related to Approvals

| Permission | Effect |
|-----------|--------|
| `allow_approve_all_levels` | User can approve any step of any module |
| `allow_self_approve_expenses` | User can approve own expense requests |
| Super Admin Groups | Bypass all approval checks |

---

# 6. User Roles & Permission Matrix

## 6.1 Standard User Roles

| Role | Description |
|------|-------------|
| Super Admin | Full system access, all modules, all branches |
| Administrator | System configuration, user management |
| Branch Manager | Full access to branch operations |
| Finance Manager | Financial modules, approvals, reporting |
| Accountant | Voucher entry, journal posting, financial reports |
| Procurement Officer | Purchase orders, supplier management, GRN |
| Store Manager | Inventory management, stock adjustments, approvals |
| Store Keeper | Goods issuance, receiving, stock counts |
| Sales Manager | Sales targets, approval, reports |
| Sales Person / Cashier | Create sales, POS, customer management |
| HR Manager | Employee management, payroll, leave |
| HR Officer | Leave processing, attendance, employee records |
| Restaurant Manager | Kitchen orders, menu management |
| Bar Manager | Bar operations, stock management |
| Receptionist | Reservations, check-in/out |
| Marketing Officer | Campaigns, events, marketing reports |
| Customer Care Officer | Complaints, inquiries, feedback |
| CRM Officer | Lead management, sales funnel |
| Gym Manager | Member management, trainer assignment |
| Production Manager | Formulas, batch processing, conversions |
| Print Shop Manager | Job orders, workflow assignment |
| Employee (Self-Service) | View payslips, apply leave, request advances |

## 6.2 Role vs Module Permission Matrix

```
Module                    SuperAdmin  Admin  Br.Mgr  Fin.Mgr  Acct   Proc.  StoreMgr  StoreKpr  SalesMgr  Sales  HRMgr  HROff  Emp
─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────
Dashboard                   ✓         ✓       ✓       ✓        ✓      ✓       ✓         ✓         ✓         ✓      ✓      ✓     ✓
Products                    ✓         ✓       ✓       ✓        ✓      ✓       ✓         ✓         ✓         ✓      ✗      ✗     ✗
Customers                   ✓         ✓       ✓       ✓        ✓      ✓       ✓         ✓         ✓         ✓      ✗      ✗     ✗
Suppliers                   ✓         ✓       ✓       ✓        ✓      ✓       ✓         ✗         ✗         ✗      ✗      ✗     ✗
Purchase Orders             ✓         ✓       ✓       ✓        ✗      ✓       ✓         ✗         ✗         ✗      ✗      ✗     ✗
Goods Receipt               ✓         ✓       ✓       ✗        ✗      ✗       ✓         ✓         ✗         ✗      ✗      ✗     ✗
Store Requests              ✓         ✓       ✓       ✗        ✗      ✗       ✓         ✓         ✗         ✗      ✗      ✗     ✗
Stock Adjustment            ✓         ✓       ✓       ✗        ✗      ✗       ✓         ✗         ✗         ✗      ✗      ✗     ✗
Stock Transfer              ✓         ✓       ✓       ✗        ✗      ✗       ✓         ✓         ✗         ✗      ✗      ✗     ✗
Sales / Invoicing           ✓         ✓       ✓       ✓        ✗      ✗       ✗         ✗         ✓         ✓      ✗      ✗     ✗
Credit Notes                ✓         ✓       ✓       ✓        ✗      ✗       ✗         ✗         ✓         ✗      ✗      ✗     ✗
Payments / Receipts         ✓         ✓       ✓       ✓        ✓      ✗       ✗         ✗         ✗         ✓      ✗      ✗     ✗
Customer Advances           ✓         ✓       ✓       ✓        ✓      ✗       ✗         ✗         ✓         ✓      ✗      ✗     ✗
Loyalty Program             ✓         ✓       ✓       ✗        ✗      ✗       ✗         ✗         ✓         ✓      ✗      ✗     ✗
CRM / Leads                 ✓         ✓       ✓       ✗        ✗      ✗       ✗         ✗         ✓         ✓      ✗      ✗     ✗
Chart of Accounts           ✓         ✓       ✓       ✓        ✓      ✗       ✗         ✗         ✗         ✗      ✗      ✗     ✗
Journal Entries             ✓         ✓       ✓       ✓        ✓      ✗       ✗         ✗         ✗         ✗      ✗      ✗     ✗
Voucher Entry               ✓         ✓       ✓       ✓        ✓      ✗       ✗         ✗         ✗         ✗      ✗      ✗     ✗
Bank Reconciliation         ✓         ✓       ✓       ✓        ✓      ✗       ✗         ✗         ✗         ✗      ✗      ✗     ✗
Financial Reports           ✓         ✓       ✓       ✓        ✓      ✗       ✗         ✗         ✓         ✗      ✗      ✗     ✗
Expense Management          ✓         ✓       ✓       ✓        ✓      ✗       ✓         ✗         ✓         ✗      ✓      ✓     ✗
Fixed Assets                ✓         ✓       ✓       ✓        ✓      ✗       ✓         ✗         ✗         ✗      ✗      ✗     ✗
Budgeting                   ✓         ✓       ✓       ✓        ✓      ✗       ✗         ✗         ✗         ✗      ✗      ✗     ✗
Employees / HR              ✓         ✓       ✓       ✗        ✗      ✗       ✗         ✗         ✗         ✗      ✓      ✓     ✗
Payroll                     ✓         ✓       ✓       ✓        ✗      ✗       ✗         ✗         ✗         ✗      ✓      ✗     ✗
Leave Management            ✓         ✓       ✓       ✗        ✗      ✗       ✗         ✗         ✓         ✗      ✓      ✓     ✓
Attendance                  ✓         ✓       ✓       ✗        ✗      ✗       ✗         ✗         ✗         ✗      ✓      ✓     ✓
Salary Advances             ✓         ✓       ✓       ✓        ✗      ✗       ✗         ✗         ✗         ✗      ✓      ✓     ✓
Imprest                     ✓         ✓       ✓       ✓        ✓      ✗       ✗         ✗         ✗         ✗      ✓      ✓     ✓
Reservations (Hotel)        ✓         ✓       ✓       ✗        ✗      ✗       ✗         ✗         ✗         ✗      ✗      ✗     ✗
Restaurant / Bar            ✓         ✓       ✓       ✗        ✗      ✗       ✓         ✗         ✗         ✗      ✗      ✗     ✗
Production / Conversions    ✓         ✓       ✓       ✗        ✗      ✗       ✓         ✓         ✗         ✗      ✗      ✗     ✗
Gym Management              ✓         ✓       ✓       ✗        ✗      ✗       ✗         ✗         ✗         ✗      ✗      ✗     ✗
Printing                    ✓         ✓       ✓       ✗        ✗      ✗       ✗         ✗         ✗         ✗      ✗      ✗     ✗
Marketing                   ✓         ✓       ✓       ✗        ✗      ✗       ✗         ✗         ✓         ✗      ✗      ✗     ✗
Customer Care               ✓         ✓       ✓       ✗        ✗      ✗       ✗         ✗         ✓         ✓      ✗      ✗     ✗
SMS                         ✓         ✓       ✓       ✗        ✗      ✗       ✗         ✗         ✓         ✓      ✓      ✓     ✗
Projects / Programs         ✓         ✓       ✓       ✓        ✓      ✗       ✗         ✗         ✓         ✗      ✗      ✗     ✗
User Management             ✓         ✓       ✗       ✗        ✗      ✗       ✗         ✗         ✗         ✗      ✗      ✗     ✗
User Groups / Permissions   ✓         ✓       ✗       ✗        ✗      ✗       ✗         ✗         ✗         ✗      ✗      ✗     ✗
Settings / Config           ✓         ✓       ✓       ✗        ✗      ✗       ✗         ✗         ✗         ✗      ✗      ✗     ✗
Approval Configuration      ✓         ✓       ✗       ✗        ✗      ✗       ✗         ✗         ✗         ✗      ✗      ✗     ✗
Reports                     ✓         ✓       ✓       ✓        ✓      ✓       ✓         ✓         ✓         ✓      ✓      ✓     ✓
Audit Logs                  ✓         ✓       ✗       ✗        ✗      ✗       ✗         ✗         ✗         ✗      ✗      ✗     ✗
Backup                      ✓         ✓       ✗       ✗        ✗      ✗       ✗         ✗         ✗         ✗      ✗      ✗     ✗
Dashboard Cards Config      ✓         ✓       ✗       ✗        ✗      ✗       ✗         ✗         ✗         ✗      ✗      ✗     ✗
CMS / Website               ✓         ✓       ✓       ✗        ✗      ✗       ✗         ✗         ✗         ✗      ✗      ✗     ✗
E-Commerce                  ✓         ✓       ✓       ✓        ✗      ✗       ✓         ✗         ✓         ✗      ✗      ✗     ✗
Money Transfers             ✓         ✓       ✓       ✓        ✓      ✗       ✗         ✗         ✗         ✗      ✗      ✗     ✗
Supplier Payments           ✓         ✓       ✓       ✓        ✓      ✓       ✗         ✗         ✗         ✗      ✗      ✗     ✗
```

## 6.3 Special Permissions

| Permission Key | Description | Roles with Access |
|---------------|-------------|------------------|
| `backdate_transactions` | Allow backdating invoices/sales | Super Admin, Admin, Branch Manager |
| `reset_user_passwords` | Allow resetting other users' passwords | Super Admin, Admin |
| `manage_company_info` | Allow editing company information | Super Admin, Admin |
| `allow_self_approve_expenses` | Allow approving own expenses | Branch Manager, Finance Manager |
| `allow_approve_all_levels` | Approve at any approval level | Super Admin, Admin, Branch Manager |
| `allow_sell_without_stock` | Sell/issue without sufficient stock | Branch Manager (configurable) |
| `allow_post_bank` | Post to bank accounts (vs cash only) | Finance Manager, Accountant |
| `invoices.change_store` | Change store on posted invoices | Super Admin, Admin, Store Manager |

---

# 7. Menu Access Matrix

## 7.1 Menu Visibility by Role

```
Menu                        SuperAdmin  Admin  BranchMgr  Finance  Sales  Store  HR  Employee
─────────────────────────────────────────────────────────────────────────────────────────────
Dashboard                       ✓         ✓       ✓          ✓        ✓      ✓     ✓      ✓
CRM                             ✓         ✓       ✓          ✗        ✓      ✗     ✗      ✗
Products                        ✓         ✓       ✓          ✓        ✓      ✓     ✗      ✗
Procurement                     ✓         ✓       ✓          ✓        ✓      ✓     ✗      ✗
Purchase Orders                 ✓         ✓       ✓          ✓        ✓      ✓     ✗      ✗
Inventory                       ✓         ✓       ✓          ✗        ✓      ✓     ✗      ✗
Sales                           ✓         ✓       ✓          ✓        ✓      ✗     ✗      ✗
Invoices                        ✓         ✓       ✓          ✓        ✓      ✗     ✗      ✗
Credit Notes                    ✓         ✓       ✓          ✓        ✓      ✗     ✗      ✗
Finance                         ✓         ✓       ✓          ✓        ✗      ✗     ✗      ✗
Chart of Accounts               ✓         ✓       ✓          ✓        ✗      ✗     ✗      ✗
Journals                        ✓         ✓       ✓          ✓        ✗      ✗     ✗      ✗
Financial Reports               ✓         ✓       ✓          ✓        ✗      ✗     ✗      ✗
Expense                         ✓         ✓       ✓          ✓        ✓      ✓     ✓      ✓
Assets                          ✓         ✓       ✓          ✓        ✗      ✓     ✗      ✗
HR / Employee                   ✓         ✓       ✓          ✗        ✗      ✗     ✓      ✓
Payroll                         ✓         ✓       ✓          ✓        ✗      ✗     ✓      ✗
Leave                           ✓         ✓       ✓          ✗        ✗      ✗     ✓      ✓
Attendance                      ✓         ✓       ✓          ✗        ✗      ✗     ✓      ✓
Reservations                    ✓         ✓       ✓          ✗        ✗      ✗     ✗      ✗
Restaurant / Bar                ✓         ✓       ✓          ✗        ✗      ✓     ✗      ✗
Production                      ✓         ✓       ✓          ✗        ✗      ✓     ✗      ✗
Printing                        ✓         ✓       ✓          ✗        ✗      ✗     ✗      ✗
Gym                             ✓         ✓       ✓          ✗        ✗      ✗     ✗      ✗
Marketing                       ✓         ✓       ✓          ✗        ✓      ✗     ✗      ✗
Customer Care                   ✓         ✓       ✓          ✗        ✓      ✗     ✗      ✗
SMS                             ✓         ✓       ✓          ✗        ✓      ✗     ✓      ✗
Loyalty                         ✓         ✓       ✓          ✗        ✓      ✗     ✗      ✗
Projects / Programs             ✓         ✓       ✓          ✓        ✓      ✗     ✗      ✗
Customers                       ✓         ✓       ✓          ✓        ✓      ✓     ✗      ✗
Suppliers                       ✓         ✓       ✓          ✓        ✓      ✓     ✗      ✗
User Management                 ✓         ✓       ✗          ✗        ✗      ✗     ✗      ✗
Settings                        ✓         ✓       ✓          ✗        ✗      ✗     ✗      ✗
Reports                         ✓         ✓       ✓          ✓        ✓      ✓     ✓      ✗
SMS                             ✓         ✓       ✓          ✗        ✗      ✗     ✗      ✗
Task Management                 ✓         ✓       ✓          ✓        ✓      ✓     ✓      ✓
Support / Tickets               ✓         ✓       ✓          ✗        ✓      ✗     ✗      ✗
```

---

# 8. Reports Catalogue

## 8.1 Sales Reports

| # | Report | URL | Filters | Export | Description |
|---|--------|-----|---------|--------|-------------|
| 1 | Sales Revenue | `/reports/sales` | From, To, Product, Customer Type, Cost Center, Project | PDF, XLSX | Invoice line revenue with totals (revenue, VAT, discounts, outstanding) |
| 2 | My Sales | `/reports/my-sales` | From, To | - | Sales scoped to logged-in user |
| 3 | Sales Person Report | `/reports/sales-person-rep` | From, To, Salesperson | - | Invoices grouped by salesperson |
| 4 | Sales Commissions | `/reports/sales-commissions` | From, To, Salesperson, Commission % | - | Total sales and computed commission |
| 5 | Sales Forecast | `/reports/sales-forecast` | Granularity, From, To, Project, Cost Center, Salesperson, Region | - | Actual vs forecast with top products |
| 6 | Sales Target | `/reports/sales-target` | Mode, From, To, Salesperson | - | Target vs actual comparison |
| 7 | Price Changes | `/reports/sales-price-changes` | From, To, Product, Cost Center | - | Invoices where price differs from default |
| 8 | Posting Person | `/reports/posting-person` | From, To, User | - | Sales by posting user |

## 8.2 Financial Reports

| # | Report | URL | Filters | Description |
|---|--------|-----|---------|-------------|
| 9 | Trial Balance | `/financials/trial-balance` | As Of, From, To, Period, Cost Center, Project | Account balances with movements |
| 10 | Income Statement | `/financials/income-statement` | From, To, View, Cost Center, Project | Revenue, COGS, expenses, net profit |
| 11 | Balance Sheet | `/financials/balance-sheet` | From, To, Cost Center, Project | Assets, liabilities, equity |
| 12 | Cash Flow | `/financials/cash-flow` | From, To, Cost Center, Project | Operating, investing, financing |
| 13 | General Ledger | `/reports/gl` | Account ID, As Of | Account transaction history |
| 14 | Revenue vs Expenses | `/reports/revenue-vs-expenses` | From, To, Project | Comparison chart |
| 15 | Financial Entries | `/financials/entries` | - | All journal entries |

## 8.3 Accounts Reports

| # | Report | URL | Filters | Export | Description |
|---|--------|-----|---------|--------|-------------|
| 16 | Expenses Report | `/reports/expenses` | From, To, Vendor, Status, Cost Center, Type, Project | PDF, XLSX | Full expense listing |
| 17 | Debtors | `/reports/debtors` | From, To, Detail/Summary, Cost Center, Project | - | Outstanding customer balances |
| 18 | Creditors | `/reports/creditors` | From, To, Detail/Summary, Cost Center, Project | - | Outstanding supplier balances |
| 19 | Customer Statement | `/reports/customer-statement` | From, To, Customer, Type, Cost Center, Project | PDF, Print | Full customer statement |
| 20 | Receivables Aging | `/accounting/receivable-aging` | - | - | Aging analysis of receivables |
| 21 | Payables Aging | `/accounting/payable-aging` | - | - | Aging analysis of payables |
| 22 | Account Statement | `/accounts/statement` | Account | CSV, Print | Per-account transaction listing |
| 23 | Cash & Bank Balances | `/accounts/balances` | As Of | - | All cash/bank balances |
| 24 | Bank Reconciliation | `/accounts/bank-reconciliation` | - | - | Bank reconciliation report |

## 8.4 VAT Reports

| # | Report | URL | Export | Description |
|---|--------|-----|--------|-------------|
| 25 | VAT on Sales | `/reports/vat/sales` | PDF | VAT collected on sales |
| 26 | VAT on Purchases | `/reports/vat/purchases` | PDF | VAT on purchases |
| 27 | VFD Report | `/reports/vat/vfd` | PDF | Electronic Fiscal Device report |
| 28 | VAT on Expenses | `/reports/vat/expenses` | - | VAT on expenses |

## 8.5 Graph Reports

| # | Report | URL | Filters | Description |
|---|--------|-----|---------|-------------|
| 29 | Product Graph | `/reports/graphs/product` | From, To, Top N | Revenue/quantity per product |
| 30 | Customer Graph | `/reports/graphs/customer` | From, To, Top N | Revenue by customer |
| 31 | Expenses Graph | `/reports/graphs/expenses` | From, To, Group | Expenses by category/month |
| 32 | Revenue Graph | `/reports/graphs/revenue` | From, To, Interval | Revenue trend |
| 33 | Product Performance | `/reports/graphs/product-performance` | From, To, Metric | Top 10 products with margin |

## 8.6 Inventory Reports

| # | Report | URL | Description |
|---|--------|-----|-------------|
| 34 | Available Stock | `/stock/available` | Current stock by store/product |
| 35 | Stock Movements | `/stock/movement` | Audit trail of stock changes |
| 36 | Low Stock | `/stock/low` | Products below minimum threshold |
| 37 | Stock Summary | `/inventory/stock-summary` | Aggregated stock view |
| 38 | Stock Conversions | `/stock/conversions/report` | Production conversion records |

## 8.7 Purchase Reports

| # | Report | URL | Description |
|---|--------|-----|-------------|
| 39 | Approved POs | `/purchase-orders/approved` | Approved purchase orders |
| 40 | Received Goods | `/stock/received` | Goods receipt history |
| 41 | Stock Received | `/stock/received` | GRN listing |

## 8.8 HR Reports

| # | Report | URL | Description |
|---|--------|-----|-------------|
| 42 | Employee List | `/reports/employee-list` | All employees |
| 43 | Employee Report | `/reports/employee` | Employee details |
| 44 | Attendance Report | `/reports/attendance` | Attendance records |
| 45 | Leave Report | `/hrm/leave/reports` | Approved leave with filters |
| 46 | Payroll Report | `/hrm/payroll/report` | Payroll run details |
| 47 | Payroll Summary | `/hrm/payroll/summary` | Aggregated payroll |

## 8.9 Other Reports

| # | Report | URL | Description |
|---|--------|-----|-------------|
| 48 | Budget Reports | `/projects/budget/reports` | Budget vs actual (PDF, CSV) |
| 49 | Project Reports | `/projects/reports` | Project management reports |
| 50 | Marketing Reports | `/marketing/reports` | Marketing performance (CSV, PDF) |
| 51 | Reservations Reports | `/reservations/reports` | Booking/hotel reports |
| 52 | Kitchen Report | `/restaurant/kitchen-report` | Kitchen operations |
| 53 | Bar Report | `/restaurant/bar-report` | Bar operations |
| 54 | Analytics Report | `/restaurant/analytics-report` | Restaurant analytics |
| 55 | Customer Care Report | `/customer-care/report` | Service and complaint reports |
| 56 | Production Reports | `/production/reports` | Manufacturing reports |
| 57 | Gym Reports | `/gym/reports` | Members, attendance, subscriptions, trainers |
| 58 | Journal Adjustment | `/reports/journal-adjustment` | Manual journal entry form |
| 59 | Sales Dashboard | `/sales/dashboard` | Sales KPIs |
| 60 | Reports Dashboard | `/reports/dashboard` | Central reports hub |

---

# 9. Dashboard Components

## 9.1 Main Dashboard

The main dashboard at `/dashboard` displays configurable KPI cards. Cards can be toggled per user group.

### Available Dashboard Cards

| Card | Description | Module |
|------|-------------|--------|
| New Customers | Count of new customers in period | Sales |
| Open Invoices | Count of unpaid/partial invoices | Sales |
| Overdue Invoices | Count of overdue invoices | Sales |
| Daily Revenue | Today's total revenue | Sales |
| Net Profit | Calculated net profit | Finance |
| Pending Kitchen Orders | Count of pending kitchen orders | Restaurant |
| Ready Kitchen Orders | Count of ready kitchen orders | Restaurant |
| Bar Orders | Count of pending bar orders | Bar |
| Stock Receive | Count of pending goods to receive | Procurement |
| Total Customers | Total customer count | CRM |
| Total Products | Total product count | Products |
| Pending LPO | Count of pending purchase orders | Procurement |
| Pending Expenses | Count of pending expenses | Expense |
| Store Requests Pending | Count of pending store requests | Inventory |
| Store Requests To Issue | Count of approved requests to issue | Inventory |
| Imprests To Retire | Count of open imprests | Finance |
| Imprest Retirements Pending | Count of retirements pending approval | Finance |
| Salary Advances Pending | Count of pending salary advances | HR |
| Payroll (pending/stats) | Payroll status cards | HR |

## 9.2 Sales Dashboard

Located at `/sales/dashboard`:
- Sales revenue charts
- Sales by product category
- Top customers
- Top salespersons
- Daily/weekly/monthly trends

## 9.3 HR Dashboard

Located at `/hrm`:
- Pending leave count
- Approved today count
- Currently on leave
- Leave types summary
- AJAX auto-refresh every 30 seconds
- Quick links to all HR modules

## 9.4 Budget Dashboard

Located at `/projects/budget`:
- Budget vs actual overview
- Budget line utilization
- Committed vs spent amounts
- Alerts for over-budget items

## 9.5 Project Dashboard

Located at `/projects`:
- Active projects
- Project status
- Budget utilization
- Program overview

---

# 10. Notifications

## 10.1 Dashboard Notification Badges

The system provides a consolidated notification endpoint (`/notifications/counts`) that returns JSON with pending action counts:

| Notification | Description |
|-------------|-------------|
| Pending Expenses | Expenses awaiting approval |
| Pending Store Requests | Store requests awaiting approval |
| To Issue | Approved store requests ready to issue |
| Pending Purchase Orders | Purchase orders awaiting approval |
| Pending Supplier Payments | Supplier payments awaiting processing |
| Approved Unpaid | Approved expenses/imprests/advances not yet paid |
| Recent Payments | Payments made in last 7 days |
| Pending Budget Approvals | Project budgets awaiting review |
| Pending Checkouts | Hotel reservations needing checkout |

## 10.2 Email Notifications

- Leave approval/rejection emails
- Leave adjustment approval emails
- Attendance alerts

## 10.3 SMS Notifications

- Bulk SMS campaigns
- Transactional alerts (configurable)

## 10.4 System Notifications

- AJAX polling on key pages for real-time updates
- Payroll index: auto-refresh every 20 seconds
- Leave approval: auto-refresh every 30 seconds
- Dashboard cards: auto-refresh counts

---

# 11. Business Rules

## 11.1 General Rules

1. All transactions are branch-scoped based on session branch
2. Users must be authenticated and have menu access permissions
3. Soft-deletes used for critical data (users, suppliers, employees)
4. Documents use configurable auto-numbering with prefixes
5. Base currency is TZS, multi-currency supported

## 11.2 Sales Rules

1. Sales can only be created against `goods`-type stores
2. Insufficient stock can be handled in two ways:
   - Block sale (Stock Control = Yes)
   - Auto-create Store Request (Stock Control = No)
3. Backdating sales requires `backdate_transactions` special permission
4. Fully paid invoices cannot be edited
5. Invoice reversal requires:
   - No issued Store Requests linked
   - No active payments (must reverse first)
6. Credit note creation for goods return does NOT reduce invoice total
7. Discount application only allowed on unpaid/partial invoices
8. COGS is recalculated on invoice edit via delta stock movements
9. Proforma invoices have no stock or accounting impact

## 11.3 Purchase Rules

1. Purchase Orders can be created without supplier (draft only)
2. PO date must be today or earlier
3. Cannot over-receive against PO line
4. Store type compatibility enforced on GRN:
   - Fixed asset items → Fixed Asset store
   - Kitchen materials → Kitchen Materials store
   - Goods → Goods/Bar store
   - No mixed receipt types allowed
5. PO reversal blocked if active GRN or advanced supplier payment exists

## 11.4 Inventory Rules

1. Negative stock is prevented by `StockMovement::creating` hook
2. Average cost calculated from goods receipt history
3. Stock adjustments create journal entries (DR COGS or CR Inventory Gain)
4. Each movement tracks reference type and reference ID for full auditability
5. Store types: goods, bar, kitchen_materials, fixed_asset
6. Fixed asset store excluded from sales and transfers

## 11.5 Financial Rules

1. Double-entry accounting: every journal entry must balance (debits = credits)
2. Bank reconciliation requires difference = 0 for approval
3. Budget commitment checks on expense approval
4. WHT (Withholding Tax) posted to liability account on payment
5. Capital contributions and withdrawals tracked separately
6. Cash/bank accounts can be assigned to individual users

## 11.6 Payroll Rules

1. Monthly payroll runs prevent duplicate month processing
2. Statutory deductions configurable via settings (NSSF, NHIF, WCF, SDL, PAYE)
3. Salary advances deducted from net pay
4. Overtime and absence configurable with multipliers
5. Payroll requires budget allocation

## 11.7 Leave Rules

1. Leave types have configurable entitlements (days)
2. Gender-specific leave types supported
3. Attachment requirement configurable per leave type
4. Leave carry-over support
5. Approvers can reject with required reason

## 11.8 Approval Rules

1. Levels: 0 (auto), 1 (single), 2 (two-step), 3 (three-step)
2. Per-group step rights configurable
3. Users cannot self-approve unless `allow_self_approve_expenses` granted
4. Super Admin / Admin groups bypass all approval checks
5. `allow_approve_all_levels` special permission grants universal approval

## 11.9 Loyalty Rules

1. Points earned based on tier's points per unit spend
2. Points have expiry dates
3. Deferred revenue journal posted for accrued points
4. Redemption reduces invoice total
5. Only eligible product subtotal counts toward points

## 11.10 Customer Advance Rules

1. Advances received before sales
2. Applied during sale via toggle
3. Journal created: DR Advance account, CR AR
4. Returnable to customer
5. Balance tracked per customer in TZS

---

# 12. Module Dependencies

## 12.1 Dependency Diagram

```
                    ┌──────────────────┐
                    │   User/Group     │
                    │   Management     │
                    └────────┬─────────┘
                             │
              ┌──────────────┼──────────────┐
              │              │              │
              ▼              ▼              ▼
     ┌────────────────┐ ┌──────────┐ ┌──────────────┐
     │   Settings /   │ │ Customers│ │   Products   │
     │  Configurations│ │          │ │   (Master)   │
     └────────┬───────┘ └─────┬────┘ └──────┬───────┘
              │               │              │
              ▼               ▼              ▼
     ┌────────────────────────────────────────────┐
     │                CRM / Leads                 │
     └────────────────────┬───────────────────────┘
                          │
              ┌───────────┴───────────┐
              │                       │
              ▼                       ▼
     ┌────────────────┐     ┌────────────────┐
     │   Procurement  │     │     Sales      │
     │   (Suppliers)  │     │  (Invoicing)   │
     └────────┬───────┘     └────────┬───────┘
              │                      │
              ▼                      ▼
     ┌────────────────┐     ┌────────────────┐
     │  Goods Receipt │     │  Payments /    │
     │     (GRN)      │     │  Receipts      │
     └────────┬───────┘     └────────┬───────┘
              │                      │
              └──────────┬───────────┘
                         │
                         ▼
              ┌──────────────────────┐
              │     Inventory /      │
              │     Stock Movement   │
              └──────────┬───────────┘
                         │
                         ▼
              ┌──────────────────────┐
              │   Finance / GL /     │
              │   Journal Entries    │
              └──────────┬───────────┘
                         │
              ┌──────────┴──────────┐
              │                     │
              ▼                     ▼
     ┌────────────────┐   ┌────────────────┐
     │   Financial    │   │  Bank Recon /  │
     │   Reports      │   │  Tax / VAT     │
     └────────────────┘   └────────────────┘

     ┌──────────────────────────────────────┐
     │            HR / Payroll              │
     │  (Employees, Leave, Attendance)      │
     └──────────────┬───────────────────────┘
                    │
                    ▼
     ┌──────────────────────────────────────┐
     │         Finance Integration          │
     │    (Payroll journals, deductions)    │
     └──────────────────────────────────────┘

     ┌──────────────────────────────────────┐
     │        Hospitality Modules           │
     │  (Reservations, Restaurant, Bar)     │
     ├──────────────────────────────────────┤
     │         → Creates Sales Invoices     │
     │         → Consumes Inventory         │
     │         → Creates Journal Entries    │
     └──────────────────────────────────────┘

     ┌──────────────────────────────────────┐
     │         Support Modules              │
     │  (Production, Printing, Gym, etc.)   │
     ├──────────────────────────────────────┤
     │         → Uses Products Master       │
     │         → Consumes/Produces Stock    │
     │         → Creates Service Orders     │
     └──────────────────────────────────────┘

     ┌──────────────────────────────────────┐
     │           Loyalty Program            │
     ├──────────────────────────────────────┤
     │    → Links to Customers              │
     │    → Integrates with Sales           │
     │    → Creates Deferred Revenue        │
     └──────────────────────────────────────┘
```

## 12.2 Key Dependencies

| Module | Depends On | Provides To |
|--------|-----------|-------------|
| Products (Master) | Settings, Branches | Sales, Procurement, Inventory, Production, Printing, Restaurant |
| Customers | Settings, Branches | Sales, CRM, Loyalty, Customer Care |
| Suppliers | Settings, Branches | Procurement, Finance |
| Procurement | Products, Suppliers, Settings | Inventory, Finance |
| Goods Receipt | Procurement, Products, Stores | Inventory, Finance |
| Sales / Invoicing | Products, Customers, Stores, Settings | Inventory, Finance, Loyalty |
| Inventory | Products, Stores, Settings | Sales, Production, Restaurant, Bar |
| Finance / GL | Chart of Accounts, Settings | All financial transactions |
| HR / Payroll | Settings, Budgets | Finance |
| CRM | Customers, Settings | Sales |
| Loyalty | Customers, Products, Sales | Finance |
| Reservations | Products (Rooms), Customers | Sales, Finance |
| Restaurant/Bar | Products, Stores, Inventory | Sales, Finance |
| Production | Products, Stores, Inventory | Inventory |
| Budgets | Projects | Procurement, Expenses, Payroll, Finance |

---

# 13. Recommended Improvements

## 13.1 Architecture Improvements

| # | Improvement | Priority | Impact |
|---|------------|----------|--------|
| 1 | **Modularize routes** - Split monolithic `web.php` (339K, 5258 lines) into per-module route files | High | Maintainability |
| 2 | **Adopt Service classes** - Move business logic from 1500+ line controllers into dedicated service classes | High | Maintainability |
| 3 | **Standardize API** - Build formal REST API with versioning for mobile/third-party integration | High | Extensibility |
| 4 | **Implement event sourcing** - Replace scattered audit tables with centralized event store | Medium | Auditability |
| 5 | **Queue long operations** - Move PDF generation, bulk SMS, payroll processing to queue | High | Performance |
| 6 | **Implement caching strategy** - Cache frequent queries (product list, account balances, menu permissions) | Medium | Performance |

## 13.2 Functional Improvements

| # | Improvement | Priority | Description |
|---|------------|----------|-------------|
| 7 | **Serial/Lot tracking** - Add batch/serial number tracking for inventory | High | For regulated industries |
| 8 | **Bin locations** - Add sub-location management within stores | Medium | Warehouse management |
| 9 | **Landed cost** - Track freight, insurance, duties on purchase orders | Medium | True inventory costing |
| 10 | **Supplier price lists** - Maintain and compare supplier price lists | Medium | Procurement optimization |
| 11 | **Automated reorder** - Auto-generate purchase orders when stock hits minimum | High | Inventory optimization |
| 12 | **Multi-currency enhancements** - Full multi-currency with auto-rate updates | Medium | International trade |
| 13 | **Fixed asset depreciation** - Automated monthly/yearly depreciation runs | Medium | Financial accuracy |
| 14 | **Budget vs actual** - Real-time budget tracking with alerts | High | Cost control |
| 15 | **Cash flow forecasting** - Predictive cash flow based on AR/AP aging | Medium | Treasury management |
| 16 | **Automated bank feeds** - Import bank statements automatically | Medium | Reconciliation efficiency |
| 17 | **Employee self-service portal** - Enhanced portal for leave, payslips, attendance | High | HR efficiency |
| 18 | **Biometric/QR attendance** - Kiosk-mode check-in/out with device integration | Medium | Attendance accuracy |
| 19 | **Mobile POS** - Tablet/phone based point of sale | High | Sales flexibility |
| 20 | **Customer portal** - Customer access to statements, invoices, payments | Medium | Customer experience |

## 13.3 Reporting Improvements

| # | Improvement | Priority | Description |
|---|------------|----------|-------------|
| 21 | **Scheduled reports** - Email reports on schedule (daily/weekly/monthly) | Medium | Management reporting |
| 22 | **Drill-down reports** - Clickable charts that drill to transaction details | Medium | Data exploration |
| 23 | **Dashboard builder** - Drag-and-drop custom dashboard creation | Low | User flexibility |
| 24 | **MIS Dashboard** - Executive dashboard with consolidated KPIs | High | Decision support |
| 25 | **Custom report builder** - User-defined report columns and filters | Low | Flexibility |

## 13.4 User Experience Improvements

| # | Improvement | Priority | Description |
|---|------------|----------|-------------|
| 26 | **Responsive design** - Full mobile responsiveness for all screens | High | Accessibility |
| 27 | **Dark mode** - Theme toggle for user preference | Low | User comfort |
| 28 | **Bulk operations** - Bulk edit/delete/approve across modules | High | Efficiency |
| 29 | **Keyboard shortcuts** - Power user keyboard navigation | Low | Productivity |
| 30 | **Onboarding wizard** - Guided first-time setup for new installations | Medium | Adoption |

## 13.5 Security & Compliance

| # | Improvement | Priority | Description |
|---|------------|----------|-------------|
| 31 | **Two-factor authentication** - TOTP/SMS 2FA for sensitive actions | High | Security |
| 32 | **Role-based field visibility** - Control field-level visibility per role | Medium | Data privacy |
| 33 | **IP whitelisting** - Restrict access by IP address for admin functions | Medium | Security |
| 34 | **Session management** - View and terminate active sessions | Medium | Security |
| 35 | **Data retention policies** - Automated archiving/purging of old data | Low | Compliance |

## 13.6 Integration Improvements

| # | Improvement | Priority | Description |
|---|------------|----------|-------------|
| 36 | **Payment gateway integration** - Direct mobile money/bank payment processing | High | Cashless operations |
| 37 | **E-invoicing (TRA EFD)** - Integration with Tanzania tax authority systems | High | Regulatory compliance |
| 38 | **Accounting software sync** - Xero/QuickBooks integration | Low | Interoperability |
| 39 | **E-commerce sync** - Real-time inventory sync with online store | Medium | Omnichannel |
| 40 | **WhatsApp integration** - Customer notifications via WhatsApp | Medium | Communication |

---

# Appendix A: System Configuration Settings

| Setting | Location | Description |
|---------|----------|-------------|
| Company Info | `/settings/company-info` | Name, address, tax ID, logo, stamp, brand colors |
| Branches | `/settings/branches` | Multi-branch management |
| Currencies | `/settings/currencies` | Multi-currency setup, exchange rates |
| Document Prefixes | `/settings/document-prefixes` | Invoice, proforma, PO number formats |
| Approval Levels | `/settings/approvals` | Per-module approval configuration |
| Dashboard Cards | `/settings/dashboard-cards` | Per-group card visibility |
| Payroll Settings | `/settings/payroll` | Statutory deduction rates, overtime rules |
| POS Settings | `/settings/pos` | Paid amount behavior (manual/auto/credit) |
| Inventory Settings | `/settings/inventory` | Stock control (block vs auto-request) |
| SMS Config | `/sms/config` | SMS gateway credentials |
| Email Config | `/email-config` | SMTP/email settings |
| Payment Gateway | `/payment-gateway` | Payment processor configuration |

# Appendix B: Database Tables (Reference)

| Table | Purpose |
|-------|---------|
| `products` | Product master data |
| `stores` | Warehouse/branch stores |
| `customers` | Customer records |
| `suppliers` | Supplier/vendor records |
| `purchase_orders` | Purchase order headers |
| `purchase_order_items` | Purchase order line items |
| `goods_receipts` | Goods received note headers |
| `goods_receipt_items` | GRN line items |
| `store_requests` | Internal stock request headers |
| `store_request_items` | Internal stock request items |
| `stock_movements` | Audit trail of all stock changes |
| `invoices` | Sales invoice headers |
| `invoice_lines` | Sales invoice line items |
| `invoice_payments` | Payment records against invoices |
| `accounts` | Chart of accounts |
| `journal_entries` | Journal entry headers |
| `journal_lines` | Journal entry debit/credit lines |
| `journal_entry_audits` | Audit trail for journal entries |
| `employees` | Employee records |
| `payroll_runs` | Payroll processing headers |
| `payroll_items` | Per-employee payroll calculations |
| `payroll_payments` | Payroll payment records |
| `leave_applications` | Leave request records |
| `leave_types` | Leave type definitions |
| `salary_advances` | Salary advance requests |
| `attendance_records` | Employee attendance |
| `loyalty_cards` | Loyalty program cards |
| `loyalty_tiers` | Loyalty tier definitions |
| `loyalty_transactions` | Points accrual/redemption |
| `customer_advances` | Customer prepayment accounts |
| `fixed_assets` | Fixed asset register |
| `depreciation_methods` | Depreciation calculation methods |
| `projects` | Project/program management |
| `project_budgets` | Budget headers |
| `project_budget_lines` | Budget line items with commitment tracking |
| `cost_centers` | Cost center definitions |
| `user_groups` | User permission groups |
| `app_settings` | Key-value application settings |

---

*Document generated July 2026 — Confidential*
