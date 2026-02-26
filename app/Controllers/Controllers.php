<?php

// ============================================================
// VISITOR CONTROLLER
// ============================================================
class VisitorController extends BaseController {
    private VisitorModel $model;

    public function __construct() {
        $this->model = new VisitorModel();
    }

    public function index(): void {
        $this->requireAuth();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $filters = [
            'status'    => $this->get('status'),
            'search'    => $this->get('search'),
            'date_from' => $this->get('date_from'),
            'date_to'   => $this->get('date_to'),
        ];
        $result = $this->model->getAllWithDetails($page, $filters);
        $sources   = Database::getInstance()->fetchAll("SELECT * FROM marketing_sources WHERE is_active=1");
        $inventory = (new InventoryModel())->getAllActive();
        $operators = Database::getInstance()->fetchAll("SELECT id, name FROM users WHERE role IN('operator','admin','super_admin') AND is_active=1");
        $this->view('visitors/index', compact('result', 'filters', 'sources', 'inventory', 'operators'));
    }

    public function store(): void {
        $this->requireAuth();
        $this->verifyCsrf();
        $data = [
            'name'                => $this->post('name'),
            'phone'               => $this->post('phone'),
            'email'               => $this->post('email'),
            'address'             => $this->post('address'),
            'source_id'           => $this->post('source_id') ?: null,
            'interested_model_id' => $this->post('interested_model_id') ?: null,
            'visit_date'          => $this->post('visit_date') ?: date('Y-m-d'),
            'assigned_operator_id'=> $this->post('assigned_operator_id') ?: null,
            'status'              => $this->post('status') ?: 'cold',
            'notes'               => $this->post('notes'),
            'created_by'          => Auth::id(),
        ];
        if (empty($data['name']) || empty($data['phone'])) {
            flash('error', 'Name and phone are required.');
            redirect('visitors');
        }
        $id = $this->model->create($data);
        (new ActivityLogModel())->log(Auth::id(), 'CREATE', 'visitors', $id, null, $data);
        flash('success', 'Visitor added successfully.');
        redirect('visitors');
    }

    public function update(int $id): void {
        $this->requireAuth();
        $this->verifyCsrf();
        $old = $this->model->find($id);
        $data = [
            'name'                => $this->post('name'),
            'phone'               => $this->post('phone'),
            'email'               => $this->post('email'),
            'address'             => $this->post('address'),
            'source_id'           => $this->post('source_id') ?: null,
            'interested_model_id' => $this->post('interested_model_id') ?: null,
            'visit_date'          => $this->post('visit_date'),
            'assigned_operator_id'=> $this->post('assigned_operator_id') ?: null,
            'status'              => $this->post('status'),
            'notes'               => $this->post('notes'),
        ];
        $this->model->update($id, $data);
        (new ActivityLogModel())->log(Auth::id(), 'UPDATE', 'visitors', $id, $old, $data);
        if (isAjax()) $this->json(['success' => true, 'message' => 'Visitor updated.']);
        flash('success', 'Visitor updated.');
        redirect('visitors');
    }

    public function delete(int $id): void {
        $this->requireAuth('admin');
        $this->verifyCsrf();
        $this->model->delete($id);
        (new ActivityLogModel())->log(Auth::id(), 'DELETE', 'visitors', $id);
        flash('success', 'Visitor deleted.');
        redirect('visitors');
    }

    public function convertToLead(int $id): void {
        $this->requireAuth();
        $visitor = $this->model->find($id);
        if (!$visitor) redirect('visitors');
        $leadModel = new LeadModel();
        $leadId = $leadModel->create([
            'visitor_id'          => $visitor['id'],
            'name'                => $visitor['name'],
            'phone'               => $visitor['phone'],
            'email'               => $visitor['email'],
            'source_id'           => $visitor['source_id'],
            'interested_model_id' => $visitor['interested_model_id'],
            'status'              => 'new',
            'priority'            => 'medium',
            'assigned_to'         => $visitor['assigned_operator_id'],
            'created_by'          => Auth::id(),
        ]);
        (new ActivityLogModel())->log(Auth::id(), 'CONVERT_TO_LEAD', 'visitors', $id, null, ['lead_id' => $leadId]);
        flash('success', 'Visitor converted to lead.');
        redirect('leads');
    }
}

// ============================================================
// LEAD CONTROLLER
// ============================================================
class LeadController extends BaseController {
    private LeadModel $model;

    public function __construct() {
        $this->model = new LeadModel();
    }

    public function index(): void {
        $this->requireAuth();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $filters = [
            'status'      => $this->get('status'),
            'priority'    => $this->get('priority'),
            'search'      => $this->get('search'),
            'assigned_to' => $this->get('assigned_to'),
        ];
        $result = $this->model->getAllWithDetails($page, $filters);
        $sources   = Database::getInstance()->fetchAll("SELECT * FROM marketing_sources WHERE is_active=1");
        $inventory = (new InventoryModel())->getAllActive();
        $operators = Database::getInstance()->fetchAll("SELECT id, name FROM users WHERE is_active=1");
        $this->view('leads/index', compact('result', 'filters', 'sources', 'inventory', 'operators'));
    }

    public function show(int $id): void {
        $this->requireAuth();
        $lead = $this->model->find($id);
        if (!$lead) redirect('leads');
        $followups = (new FollowupModel())->getByLead($id);
        $sources   = Database::getInstance()->fetchAll("SELECT * FROM marketing_sources WHERE is_active=1");
        $inventory = (new InventoryModel())->getAllActive();
        $operators = Database::getInstance()->fetchAll("SELECT id, name FROM users WHERE is_active=1");
        $this->view('leads/show', compact('lead', 'followups', 'sources', 'inventory', 'operators'));
    }

    public function store(): void {
        $this->requireAuth();
        $this->verifyCsrf();
        $data = [
            'name'                => $this->post('name'),
            'phone'               => $this->post('phone'),
            'email'               => $this->post('email'),
            'source_id'           => $this->post('source_id') ?: null,
            'interested_model_id' => $this->post('interested_model_id') ?: null,
            'budget'              => $this->post('budget') ?: null,
            'status'              => 'new',
            'priority'            => $this->post('priority') ?: 'medium',
            'assigned_to'         => $this->post('assigned_to') ?: Auth::id(),
            'next_followup_date'  => $this->post('next_followup_date') ?: null,
            'notes'               => $this->post('notes'),
            'created_by'          => Auth::id(),
        ];
        $id = $this->model->create($data);
        (new ActivityLogModel())->log(Auth::id(), 'CREATE', 'leads', $id);
        flash('success', 'Lead added successfully.');
        redirect('leads');
    }

    public function update(int $id): void {
        $this->requireAuth();
        $this->verifyCsrf();
        $old = $this->model->find($id);
        $data = [
            'name'                => $this->post('name'),
            'phone'               => $this->post('phone'),
            'email'               => $this->post('email'),
            'source_id'           => $this->post('source_id') ?: null,
            'interested_model_id' => $this->post('interested_model_id') ?: null,
            'budget'              => $this->post('budget') ?: null,
            'status'              => $this->post('status'),
            'priority'            => $this->post('priority'),
            'assigned_to'         => $this->post('assigned_to') ?: null,
            'next_followup_date'  => $this->post('next_followup_date') ?: null,
            'notes'               => $this->post('notes'),
        ];
        $this->model->update($id, $data);
        (new ActivityLogModel())->log(Auth::id(), 'UPDATE', 'leads', $id, $old, $data);
        flash('success', 'Lead updated.');
        redirect('leads/' . $id);
    }

    public function addFollowup(int $leadId): void {
        $this->requireAuth();
        $this->verifyCsrf();
        $data = [
            'lead_id'       => $leadId,
            'followup_date' => $this->post('followup_date'),
            'followup_time' => $this->post('followup_time') ?: null,
            'type'          => $this->post('type') ?: 'call',
            'status'        => 'pending',
            'notes'         => $this->post('notes'),
            'conducted_by'  => Auth::id(),
        ];
        (new FollowupModel())->create($data);
        // Update lead next followup date
        $this->model->update($leadId, ['next_followup_date' => $data['followup_date']]);
        (new ActivityLogModel())->log(Auth::id(), 'ADD_FOLLOWUP', 'leads', $leadId);
        flash('success', 'Follow-up scheduled.');
        redirect('leads/' . $leadId);
    }

    public function completeFollowup(int $id): void {
        $this->requireAuth();
        $this->verifyCsrf();
        $data = [
            'status'            => 'completed',
            'result'            => $this->post('result'),
            'next_followup_date'=> $this->post('next_followup_date') ?: null,
        ];
        $fModel = new FollowupModel();
        $followup = $fModel->find($id);
        $fModel->update($id, $data);
        if (!empty($data['next_followup_date'])) {
            $this->model->update($followup['lead_id'], ['next_followup_date' => $data['next_followup_date']]);
        }
        if (isAjax()) $this->json(['success' => true]);
        redirect('leads/' . $followup['lead_id']);
    }

    public function convertToCustomer(int $id): void {
        $this->requireAuth();
        $this->verifyCsrf();
        $lead = $this->model->find($id);
        if (!$lead) redirect('leads');
        $customerModel = new CustomerModel();
        $custId = $customerModel->create([
            'lead_id'    => $id,
            'name'       => $lead['name'],
            'phone'      => $lead['phone'],
            'email'      => $lead['email'],
            'created_by' => Auth::id(),
        ]);
        $this->model->update($id, ['status' => 'converted']);
        (new ActivityLogModel())->log(Auth::id(), 'CONVERT_TO_CUSTOMER', 'leads', $id, null, ['customer_id' => $custId]);
        flash('success', 'Lead converted to customer.');
        redirect('customers/' . $custId);
    }
}

// ============================================================
// CUSTOMER CONTROLLER
// ============================================================
class CustomerController extends BaseController {
    private CustomerModel $model;

    public function __construct() {
        $this->model = new CustomerModel();
    }

    public function index(): void {
        $this->requireAuth();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $filters = ['search' => $this->get('search'), 'kyc_verified' => $this->get('kyc_verified')];
        $result = $this->model->getAllWithDetails($page, $filters);
        $this->view('customers/index', compact('result', 'filters'));
    }

    public function show(int $id): void {
        $this->requireAuth();
        $customer = $this->model->find($id);
        if (!$customer) redirect('customers');
        $db = Database::getInstance();
        $sales = $db->fetchAll(
            "SELECT s.*, i.model_name, i.color FROM sales s JOIN inventory i ON s.inventory_id=i.id WHERE s.customer_id=? ORDER BY s.created_at DESC",
            [$id]
        );
        $this->view('customers/show', compact('customer', 'sales'));
    }

    public function update(int $id): void {
        $this->requireAuth();
        $this->verifyCsrf();
        $data = [
            'name'         => $this->post('name'),
            'phone'        => $this->post('phone'),
            'email'        => $this->post('email'),
            'address'      => $this->post('address'),
            'city'         => $this->post('city'),
            'state'        => $this->post('state'),
            'pincode'      => $this->post('pincode'),
            'aadhar_number'=> $this->post('aadhar_number'),
            'pan_number'   => $this->post('pan_number'),
            'date_of_birth'=> $this->post('date_of_birth') ?: null,
            'kyc_verified' => isset($_POST['kyc_verified']) ? 1 : 0,
            'notes'        => $this->post('notes'),
        ];
        // Handle file uploads
        foreach (['document_aadhar', 'document_pan', 'document_photo'] as $field) {
            if (!empty($_FILES[$field]['name'])) {
                $path = uploadFile($_FILES[$field], 'kyc');
                if ($path) $data[$field] = $path;
            }
        }
        $this->model->update($id, $data);
        (new ActivityLogModel())->log(Auth::id(), 'UPDATE', 'customers', $id);
        flash('success', 'Customer updated.');
        redirect('customers/' . $id);
    }
}

// ============================================================
// INVENTORY CONTROLLER
// ============================================================
class InventoryController extends BaseController {
    private InventoryModel $model;

    public function __construct() {
        $this->model = new InventoryModel();
    }

    public function index(): void {
        $this->requireAuth();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $filters = ['search' => $this->get('search'), 'is_active' => $this->get('is_active')];
        $result   = $this->model->getAllWithPagination($page, $filters);
        $lowStock = $this->model->getLowStock();
        $this->view('inventory/index', compact('result', 'filters', 'lowStock'));
    }

    public function store(): void {
        $this->requireAuth('admin');
        $this->verifyCsrf();
        $data = [
            'model_name'      => $this->post('model_name'),
            'brand'           => $this->post('brand'),
            'color'           => $this->post('color'),
            'sku'             => $this->post('sku'),
            'purchase_price'  => (float)$this->post('purchase_price'),
            'selling_price'   => (float)$this->post('selling_price'),
            'stock_quantity'  => (int)$this->post('stock_quantity'),
            'low_stock_alert' => (int)($this->post('low_stock_alert') ?: 2),
            'dealer_name'     => $this->post('dealer_name'),
            'dealer_contact'  => $this->post('dealer_contact'),
            'battery_capacity'=> $this->post('battery_capacity'),
            'range_km'        => (int)$this->post('range_km') ?: null,
            'top_speed'       => (int)$this->post('top_speed') ?: null,
            'warranty_months' => (int)$this->post('warranty_months') ?: 12,
            'description'     => $this->post('description'),
            'is_active'       => 1,
        ];
        if (!empty($_FILES['image']['name'])) {
            $img = uploadFile($_FILES['image'], 'scooters');
            if ($img) $data['image'] = $img;
        }
        $id = $this->model->create($data);
        (new ActivityLogModel())->log(Auth::id(), 'CREATE', 'inventory', $id);
        flash('success', 'Scooter added to inventory.');
        redirect('inventory');
    }

    public function update(int $id): void {
        $this->requireAuth('admin');
        $this->verifyCsrf();
        $data = [
            'model_name'      => $this->post('model_name'),
            'brand'           => $this->post('brand'),
            'color'           => $this->post('color'),
            'sku'             => $this->post('sku'),
            'purchase_price'  => (float)$this->post('purchase_price'),
            'selling_price'   => (float)$this->post('selling_price'),
            'stock_quantity'  => (int)$this->post('stock_quantity'),
            'low_stock_alert' => (int)($this->post('low_stock_alert') ?: 2),
            'dealer_name'     => $this->post('dealer_name'),
            'dealer_contact'  => $this->post('dealer_contact'),
            'battery_capacity'=> $this->post('battery_capacity'),
            'range_km'        => (int)$this->post('range_km') ?: null,
            'top_speed'       => (int)$this->post('top_speed') ?: null,
            'warranty_months' => (int)$this->post('warranty_months') ?: 12,
            'description'     => $this->post('description'),
            'is_active'       => isset($_POST['is_active']) ? 1 : 0,
        ];
        if (!empty($_FILES['image']['name'])) {
            $img = uploadFile($_FILES['image'], 'scooters');
            if ($img) $data['image'] = $img;
        }
        $this->model->update($id, $data);
        (new ActivityLogModel())->log(Auth::id(), 'UPDATE', 'inventory', $id);
        flash('success', 'Inventory updated.');
        redirect('inventory');
    }

    public function delete(int $id): void {
        $this->requireAuth('admin');
        $this->verifyCsrf();
        $this->model->update($id, ['is_active' => 0]);
        flash('success', 'Item deactivated.');
        redirect('inventory');
    }
}

// ============================================================
// SALES CONTROLLER
// ============================================================
class SalesController extends BaseController {
    private SaleModel $model;

    public function __construct() {
        $this->model = new SaleModel();
    }

    public function index(): void {
        $this->requireAuth();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $filters = [
            'search'         => $this->get('search'),
            'payment_status' => $this->get('payment_status'),
            'date_from'      => $this->get('date_from'),
            'date_to'        => $this->get('date_to'),
        ];
        $result = $this->model->getAllWithDetails($page, $filters);
        $customers  = Database::getInstance()->fetchAll("SELECT id, name, phone FROM customers ORDER BY name ASC");
        $inventory  = (new InventoryModel())->getAllActive();
        $this->view('sales/index', compact('result', 'filters', 'customers', 'inventory'));
    }

    public function create(): void {
        $this->requireAuth();
        $customers = Database::getInstance()->fetchAll("SELECT id, name, phone FROM customers ORDER BY name ASC");
        $inventory = (new InventoryModel())->getAllActive();
        $this->view('sales/create', compact('customers', 'inventory'));
    }

    public function store(): void {
        $this->requireAuth();
        $this->verifyCsrf();
        $customerId  = (int)$this->post('customer_id');
        $inventoryId = (int)$this->post('inventory_id');
        $quantity    = max(1, (int)$this->post('quantity'));
        $unitPrice   = (float)$this->post('unit_price');
        $discount    = (float)($this->post('discount') ?: 0);
        $taxPercent  = (float)($this->post('tax_percent') ?: 0);
        $isEmi       = isset($_POST['is_emi']) ? 1 : 0;
        $emiMonths   = (int)($this->post('emi_months') ?: 0);
        $subtotal    = ($unitPrice * $quantity) - $discount;
        $taxAmount   = $subtotal * ($taxPercent / 100);
        $total       = $subtotal + $taxAmount;
        $amountPaid  = (float)$this->post('amount_paid');
        $balance     = max(0, $total - $amountPaid);

        $inv = (new InventoryModel())->find($inventoryId);
        if (!$inv || $inv['stock_quantity'] < $quantity) {
            flash('error', 'Insufficient stock.');
            redirect('sales/create');
        }

        $invoiceNo = $this->model->getNextInvoiceNumber();
        $warrantyExpiry = date('Y-m-d', strtotime('+' . ($inv['warranty_months'] ?? 12) . ' months'));

        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $saleId = $this->model->create([
                'invoice_number' => $invoiceNo,
                'customer_id'    => $customerId,
                'inventory_id'   => $inventoryId,
                'sale_date'      => $this->post('sale_date') ?: date('Y-m-d'),
                'quantity'       => $quantity,
                'unit_price'     => $unitPrice,
                'discount'       => $discount,
                'tax_percent'    => $taxPercent,
                'tax_amount'     => $taxAmount,
                'total_amount'   => $total,
                'payment_mode'   => $this->post('payment_mode'),
                'payment_status' => $balance <= 0 ? 'paid' : ($amountPaid > 0 ? 'partial' : 'pending'),
                'amount_paid'    => $amountPaid,
                'balance_due'    => $balance,
                'is_emi'         => $isEmi,
                'emi_months'     => $isEmi ? $emiMonths : null,
                'emi_amount'     => $isEmi && $emiMonths ? round($total / $emiMonths, 2) : null,
                'warranty_expiry'=> $warrantyExpiry,
                'operator_id'    => Auth::id(),
                'notes'          => $this->post('notes'),
            ]);

            if ($amountPaid > 0) {
                (new PaymentModel())->create([
                    'sale_id'       => $saleId,
                    'customer_id'   => $customerId,
                    'amount'        => $amountPaid,
                    'payment_date'  => $this->post('sale_date') ?: date('Y-m-d'),
                    'payment_mode'  => $this->post('payment_mode'),
                    'transaction_id'=> $this->post('transaction_id'),
                    'received_by'   => Auth::id(),
                ]);
            }

            (new InventoryModel())->decreaseStock($inventoryId, $quantity);
            $this->model->incrementInvoiceCounter();
            $db->commit();
            (new ActivityLogModel())->log(Auth::id(), 'CREATE_SALE', 'sales', $saleId, null, ['invoice' => $invoiceNo]);
            flash('success', 'Sale recorded. Invoice: ' . $invoiceNo);
            redirect('sales/invoice/' . $saleId);
        } catch (Exception $e) {
            $db->rollback();
            flash('error', 'Failed to create sale: ' . $e->getMessage());
            redirect('sales/create');
        }
    }

    public function invoice(int $id): void {
        $this->requireAuth();
        $sale = $this->model->getDetailedWithPayments($id);
        if (!$sale) redirect('sales');
        $settings = (new SettingsModel())->getGroup('general');
        $this->view('sales/invoice', compact('sale', 'settings'));
    }

    public function addPayment(int $saleId): void {
        $this->requireAuth();
        $this->verifyCsrf();
        $sale   = $this->model->find($saleId);
        $amount = (float)$this->post('amount');
        (new PaymentModel())->create([
            'sale_id'        => $saleId,
            'customer_id'    => $sale['customer_id'],
            'amount'         => $amount,
            'payment_date'   => $this->post('payment_date') ?: date('Y-m-d'),
            'payment_mode'   => $this->post('payment_mode'),
            'transaction_id' => $this->post('transaction_id'),
            'is_emi'         => isset($_POST['is_emi']) ? 1 : 0,
            'emi_installment_number' => $this->post('emi_installment_number') ?: null,
            'received_by'    => Auth::id(),
        ]);
        $newPaid    = $sale['amount_paid'] + $amount;
        $newBalance = max(0, $sale['total_amount'] - $newPaid);
        $newStatus  = $newBalance <= 0 ? 'paid' : 'partial';
        $this->model->update($saleId, ['amount_paid' => $newPaid, 'balance_due' => $newBalance, 'payment_status' => $newStatus]);
        flash('success', 'Payment recorded.');
        redirect('sales/invoice/' . $saleId);
    }
}

// ============================================================
// REPORTS CONTROLLER
// ============================================================
class ReportsController extends BaseController {
    public function index(): void {
        $this->requireAuth('admin');
        $this->view('reports/index', []);
    }

    public function daily(): void {
        $this->requireAuth('admin');
        $date = $this->get('date') ?: date('Y-m-d');
        $saleModel = new SaleModel();
        $payModel  = new PaymentModel();
        $sales     = $saleModel->getDailySales($date);
        $payments  = $payModel->getDailyCollection($date);
        $totalRevenue   = array_sum(array_column($sales, 'total_amount'));
        $totalCollected = array_sum(array_column($payments, 'amount'));
        $this->view('reports/daily', compact('date', 'sales', 'payments', 'totalRevenue', 'totalCollected'));
    }

    public function monthly(): void {
        $this->requireAuth('admin');
        $year = (int)($this->get('year') ?: date('Y'));
        $saleModel = new SaleModel();
        $monthlyData = $saleModel->getMonthlySummary($year);
        $topModels   = $saleModel->getTopModels(10);
        $db = Database::getInstance();
        $operatorStats = $db->fetchAll(
            "SELECT u.name, COUNT(s.id) as total_sales, SUM(s.total_amount) as revenue
             FROM users u LEFT JOIN sales s ON u.id = s.operator_id
             WHERE YEAR(s.sale_date) = ? GROUP BY u.id ORDER BY total_sales DESC",
            [$year]
        );
        $this->view('reports/monthly', compact('year', 'monthlyData', 'topModels', 'operatorStats'));
    }
}

// ============================================================
// SETTINGS CONTROLLER
// ============================================================
class SettingsController extends BaseController {
    private SettingsModel $model;

    public function __construct() {
        $this->model = new SettingsModel();
    }

    public function index(): void {
        $this->requireAuth('super_admin');
        $general  = $this->model->getGroup('general');
        $email    = $this->model->getGroup('email');
        $marketing= $this->model->getGroup('marketing');
        $notif    = $this->model->getGroup('notifications');
        $users    = (new UserModel())->getAll();
        $this->view('settings/index', compact('general', 'email', 'marketing', 'notif', 'users'));
    }

    public function updateGeneral(): void {
        $this->requireAuth('super_admin');
        $this->verifyCsrf();
        $keys = ['company_name','company_phone','company_email','company_address','company_gstin','invoice_prefix','currency_symbol'];
        foreach ($keys as $key) {
            $this->model->set($key, $this->post($key));
        }
        flash('success', 'Settings saved.');
        redirect('settings');
    }

    public function updateEmail(): void {
        $this->requireAuth('super_admin');
        $this->verifyCsrf();
        $keys = ['smtp_host','smtp_port','smtp_user','smtp_pass','smtp_from_name'];
        foreach ($keys as $key) {
            $this->model->set($key, $this->post($key));
        }
        flash('success', 'Email settings saved.');
        redirect('settings');
    }

    public function updateMarketing(): void {
        $this->requireAuth('super_admin');
        $this->verifyCsrf();
        $keys = ['facebook_pixel_id','facebook_access_token','whatsapp_api_key','whatsapp_phone_number'];
        foreach ($keys as $key) {
            $this->model->set($key, $this->post($key));
        }
        flash('success', 'Marketing settings saved.');
        redirect('settings');
    }

    public function createUser(): void {
        $this->requireAuth('super_admin');
        $this->verifyCsrf();
        $model = new UserModel();
        $data  = [
            'name'     => $this->post('name'),
            'email'    => $this->post('email'),
            'phone'    => $this->post('phone'),
            'password' => $model->hashPassword($this->post('password')),
            'role'     => $this->post('role'),
            'is_active'=> 1,
        ];
        $model->create($data);
        flash('success', 'User created.');
        redirect('settings');
    }

    public function toggleUser(int $id): void {
        $this->requireAuth('super_admin');
        $user = (new UserModel())->find($id);
        if ($user && $user['id'] !== Auth::id()) {
            (new UserModel())->update($id, ['is_active' => $user['is_active'] ? 0 : 1]);
        }
        redirect('settings');
    }
}
