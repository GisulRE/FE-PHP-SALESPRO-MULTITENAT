# SalesPro & Laravel Project Guidelines

## 1. Deployment & Route Caching
- NEVER use `php artisan route:cache` if `routes/web.php` contains Closure routes (`function()`). Use `php artisan route:clear` in CI/CD deployment scripts (`deploy.yml`).

## 2. Navigation & Blade Layout Architecture
- All module views MUST extend the complete layout master `@extends('layout.main')`.
- Do NOT use jQuery AJAX `.stopReload` or `setPage()` pseudo-SPA content swapping. Use standard native Laravel full-page navigation.
- Do NOT place `if (!localStorage.getItem('clicked'))` redirect scripts at the top of Blade views.

## 3. Database & Model Integrity
- Ensure Eloquent models for pivot tables (`Product_Warehouse`) define default fallback values in `static::creating` boot listeners (e.g. `$model->qty = $model->qty ?? 0`) to prevent PostgreSQL `NOT NULL` constraint violations.

## 4. SIAT Service & External API Integration
- SIAT invoice generation HTTP requests MUST use a timeout of at least 90 seconds (`Http::timeout(90)`) to accommodate SIN SOAP validation delays.
- Always check and auto-fetch SIAT session tokens before executing SIAT API endpoints.
