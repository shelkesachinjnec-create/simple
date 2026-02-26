<?php

$router = new Router();

// Auth
$router->get('auth/login', 'AuthController', 'loginPage');
$router->post('auth/login', 'AuthController', 'login');
$router->get('auth/logout', 'AuthController', 'logout');

// Dashboard
$router->get('', 'DashboardController', 'index');
$router->get('dashboard', 'DashboardController', 'index');

// Visitors
$router->get('visitors', 'VisitorController', 'index');
$router->post('visitors/store', 'VisitorController', 'store');
$router->post('visitors/update/{id}', 'VisitorController', 'update');
$router->post('visitors/delete/{id}', 'VisitorController', 'delete');
$router->get('visitors/convert-lead/{id}', 'VisitorController', 'convertToLead');

// Leads
$router->get('leads', 'LeadController', 'index');
$router->get('leads/{id}', 'LeadController', 'show');
$router->post('leads/store', 'LeadController', 'store');
$router->post('leads/update/{id}', 'LeadController', 'update');
$router->post('leads/{id}/followup', 'LeadController', 'addFollowup');
$router->post('followups/complete/{id}', 'LeadController', 'completeFollowup');
$router->get('leads/{id}/convert-customer', 'LeadController', 'convertToCustomer');

// Customers
$router->get('customers', 'CustomerController', 'index');
$router->get('customers/{id}', 'CustomerController', 'show');
$router->post('customers/update/{id}', 'CustomerController', 'update');

// Inventory
$router->get('inventory', 'InventoryController', 'index');
$router->post('inventory/store', 'InventoryController', 'store');
$router->post('inventory/update/{id}', 'InventoryController', 'update');
$router->post('inventory/delete/{id}', 'InventoryController', 'delete');

// Sales
$router->get('sales', 'SalesController', 'index');
$router->get('sales/create', 'SalesController', 'create');
$router->post('sales/store', 'SalesController', 'store');
$router->get('sales/invoice/{id}', 'SalesController', 'invoice');
$router->post('sales/{id}/payment', 'SalesController', 'addPayment');

// Reports
$router->get('reports', 'ReportsController', 'index');
$router->get('reports/daily', 'ReportsController', 'daily');
$router->get('reports/monthly', 'ReportsController', 'monthly');

// Settings
$router->get('settings', 'SettingsController', 'index');
$router->post('settings/general', 'SettingsController', 'updateGeneral');
$router->post('settings/email', 'SettingsController', 'updateEmail');
$router->post('settings/marketing', 'SettingsController', 'updateMarketing');
$router->post('settings/users/create', 'SettingsController', 'createUser');
$router->get('settings/users/toggle/{id}', 'SettingsController', 'toggleUser');

return $router;
