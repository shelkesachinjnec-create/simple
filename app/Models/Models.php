<?php
// VisitorModel
class VisitorModel extends BaseModel {
    protected string $table = 'visitors';

    public function getAllWithDetails(int $page = 1, array $filters = []): array {
        $sql = "SELECT v.*, ms.name as source_name, i.model_name as interested_model, 
                u.name as operator_name
                FROM visitors v
                LEFT JOIN marketing_sources ms ON v.source_id = ms.id
                LEFT JOIN inventory i ON v.interested_model_id = i.id
                LEFT JOIN users u ON v.assigned_operator_id = u.id
                WHERE 1=1";
        $params = [];
        if (!empty($filters['status'])) {
            $sql .= " AND v.status = ?"; $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (v.name LIKE ? OR v.phone LIKE ? OR v.email LIKE ?)";
            $params[] = "%{$filters['search']}%";
            $params[] = "%{$filters['search']}%";
            $params[] = "%{$filters['search']}%";
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND v.visit_date >= ?"; $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND v.visit_date <= ?"; $params[] = $filters['date_to'];
        }
        $sql .= " ORDER BY v.created_at DESC";
        return $this->paginate($sql, $params, $page);
    }
}

// LeadModel
class LeadModel extends BaseModel {
    protected string $table = 'leads';

    public function getAllWithDetails(int $page = 1, array $filters = []): array {
        $sql = "SELECT l.*, ms.name as source_name, i.model_name as interested_model,
                u.name as assigned_to_name, v.name as visitor_name
                FROM leads l
                LEFT JOIN marketing_sources ms ON l.source_id = ms.id
                LEFT JOIN inventory i ON l.interested_model_id = i.id
                LEFT JOIN users u ON l.assigned_to = u.id
                LEFT JOIN visitors v ON l.visitor_id = v.id
                WHERE 1=1";
        $params = [];
        if (!empty($filters['status'])) {
            $sql .= " AND l.status = ?"; $params[] = $filters['status'];
        }
        if (!empty($filters['priority'])) {
            $sql .= " AND l.priority = ?"; $params[] = $filters['priority'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (l.name LIKE ? OR l.phone LIKE ?)";
            $params[] = "%{$filters['search']}%";
            $params[] = "%{$filters['search']}%";
        }
        if (!empty($filters['assigned_to'])) {
            $sql .= " AND l.assigned_to = ?"; $params[] = $filters['assigned_to'];
        }
        $sql .= " ORDER BY l.created_at DESC";
        return $this->paginate($sql, $params, $page);
    }

    public function getPendingFollowups(): array {
        return $this->db->fetchAll(
            "SELECT l.*, f.followup_date, f.type as followup_type, u.name as assigned_to_name
             FROM leads l
             JOIN followups f ON l.id = f.lead_id
             LEFT JOIN users u ON l.assigned_to = u.id
             WHERE f.status = 'pending' AND f.followup_date <= CURDATE()
             ORDER BY f.followup_date ASC"
        );
    }

    public function getTodayFollowups(): array {
        return $this->db->fetchAll(
            "SELECT l.*, f.id as followup_id, f.followup_date, f.type as followup_type, f.notes as followup_notes,
             u.name as assigned_to_name
             FROM leads l
             JOIN followups f ON l.id = f.lead_id
             LEFT JOIN users u ON l.assigned_to = u.id
             WHERE f.status = 'pending' AND f.followup_date = CURDATE()
             ORDER BY f.followup_time ASC"
        );
    }
}

// FollowupModel
class FollowupModel extends BaseModel {
    protected string $table = 'followups';

    public function getByLead(int $leadId): array {
        return $this->db->fetchAll(
            "SELECT f.*, u.name as conducted_by_name
             FROM followups f
             LEFT JOIN users u ON f.conducted_by = u.id
             WHERE f.lead_id = ?
             ORDER BY f.followup_date DESC",
            [$leadId]
        );
    }
}

// CustomerModel
class CustomerModel extends BaseModel {
    protected string $table = 'customers';

    public function getAllWithDetails(int $page = 1, array $filters = []): array {
        $sql = "SELECT c.*, u.name as created_by_name,
                COUNT(DISTINCT s.id) as total_purchases
                FROM customers c
                LEFT JOIN users u ON c.created_by = u.id
                LEFT JOIN sales s ON c.id = s.customer_id
                WHERE 1=1";
        $params = [];
        if (!empty($filters['search'])) {
            $sql .= " AND (c.name LIKE ? OR c.phone LIKE ? OR c.email LIKE ?)";
            $params[] = "%{$filters['search']}%";
            $params[] = "%{$filters['search']}%";
            $params[] = "%{$filters['search']}%";
        }
        if (!empty($filters['kyc_verified'])) {
            $sql .= " AND c.kyc_verified = ?"; $params[] = $filters['kyc_verified'];
        }
        $sql .= " GROUP BY c.id ORDER BY c.created_at DESC";
        return $this->paginate($sql, $params, $page);
    }
}

// InventoryModel
class InventoryModel extends BaseModel {
    protected string $table = 'inventory';

    public function getLowStock(): array {
        return $this->db->fetchAll(
            "SELECT * FROM inventory WHERE stock_quantity <= low_stock_alert AND is_active = 1 ORDER BY stock_quantity ASC"
        );
    }

    public function getAllActive(): array {
        return $this->db->fetchAll(
            "SELECT * FROM inventory WHERE is_active = 1 ORDER BY model_name ASC"
        );
    }

    public function getAllWithPagination(int $page = 1, array $filters = []): array {
        $sql = "SELECT * FROM inventory WHERE 1=1";
        $params = [];
        if (!empty($filters['search'])) {
            $sql .= " AND (model_name LIKE ? OR brand LIKE ? OR sku LIKE ?)";
            $params[] = "%{$filters['search']}%";
            $params[] = "%{$filters['search']}%";
            $params[] = "%{$filters['search']}%";
        }
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $sql .= " AND is_active = ?"; $params[] = $filters['is_active'];
        }
        $sql .= " ORDER BY model_name ASC";
        return $this->paginate($sql, $params, $page);
    }

    public function decreaseStock(int $id, int $qty = 1): void {
        $this->db->execute("UPDATE inventory SET stock_quantity = stock_quantity - ? WHERE id = ?", [$qty, $id]);
    }

    public function increaseStock(int $id, int $qty = 1): void {
        $this->db->execute("UPDATE inventory SET stock_quantity = stock_quantity + ? WHERE id = ?", [$qty, $id]);
    }
}

// SaleModel
class SaleModel extends BaseModel {
    protected string $table = 'sales';

    public function getAllWithDetails(int $page = 1, array $filters = []): array {
        $sql = "SELECT s.*, c.name as customer_name, c.phone as customer_phone,
                i.model_name, i.color, u.name as operator_name
                FROM sales s
                JOIN customers c ON s.customer_id = c.id
                JOIN inventory i ON s.inventory_id = i.id
                LEFT JOIN users u ON s.operator_id = u.id
                WHERE 1=1";
        $params = [];
        if (!empty($filters['search'])) {
            $sql .= " AND (s.invoice_number LIKE ? OR c.name LIKE ? OR c.phone LIKE ?)";
            $params[] = "%{$filters['search']}%";
            $params[] = "%{$filters['search']}%";
            $params[] = "%{$filters['search']}%";
        }
        if (!empty($filters['payment_status'])) {
            $sql .= " AND s.payment_status = ?"; $params[] = $filters['payment_status'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND s.sale_date >= ?"; $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND s.sale_date <= ?"; $params[] = $filters['date_to'];
        }
        $sql .= " ORDER BY s.created_at DESC";
        return $this->paginate($sql, $params, $page);
    }

    public function getNextInvoiceNumber(): string {
        $prefixRow = $this->db->fetch("SELECT value FROM settings WHERE `key` = 'invoice_prefix'");
        $prefix = $prefixRow['value'] ?? 'SMP';
        $counterRow = $this->db->fetch("SELECT value FROM settings WHERE `key` = 'invoice_counter'");
        $counter = $counterRow['value'] ?? 1000;
        return $prefix . '-' . str_pad($counter, 5, '0', STR_PAD_LEFT);
    }

    public function incrementInvoiceCounter(): void {
        $this->db->execute("UPDATE settings SET value = value + 1 WHERE `key` = 'invoice_counter'");
    }

    public function getDailySales(string $date = ''): array {
        $date = $date ?: date('Y-m-d');
        return $this->db->fetchAll(
            "SELECT s.*, c.name as customer_name, i.model_name
             FROM sales s
             JOIN customers c ON s.customer_id = c.id
             JOIN inventory i ON s.inventory_id = i.id
             WHERE s.sale_date = ? ORDER BY s.created_at DESC",
            [$date]
        );
    }

    public function getMonthlySummary(int $year): array {
        return $this->db->fetchAll(
            "SELECT MONTH(sale_date) as month, COUNT(*) as total_sales,
             SUM(total_amount) as revenue, SUM(amount_paid) as collected,
             SUM(balance_due) as outstanding
             FROM sales WHERE YEAR(sale_date) = ?
             GROUP BY MONTH(sale_date) ORDER BY month ASC",
            [$year]
        );
    }

    public function getTopModels(int $limit = 5): array {
        return $this->db->fetchAll(
            "SELECT i.model_name, i.color, COUNT(s.id) as total_sales, SUM(s.total_amount) as revenue
             FROM sales s JOIN inventory i ON s.inventory_id = i.id
             GROUP BY s.inventory_id ORDER BY total_sales DESC LIMIT ?",
            [$limit]
        );
    }

    public function getDetailedWithPayments(int $id): ?array {
        $sale = $this->db->fetch(
            "SELECT s.*, c.name as customer_name, c.phone as customer_phone, c.email as customer_email,
             c.address as customer_address, c.aadhar_number, c.pan_number,
             i.model_name, i.color, i.warranty_months, u.name as operator_name
             FROM sales s
             JOIN customers c ON s.customer_id = c.id
             JOIN inventory i ON s.inventory_id = i.id
             LEFT JOIN users u ON s.operator_id = u.id
             WHERE s.id = ?",
            [$id]
        );
        if ($sale) {
            $sale['payments'] = $this->db->fetchAll(
                "SELECT p.*, u.name as received_by_name FROM payments p
                 LEFT JOIN users u ON p.received_by = u.id
                 WHERE p.sale_id = ? ORDER BY p.payment_date ASC",
                [$id]
            );
        }
        return $sale;
    }
}

// PaymentModel
class PaymentModel extends BaseModel {
    protected string $table = 'payments';

    public function getDailyCollection(string $date = ''): array {
        $date = $date ?: date('Y-m-d');
        return $this->db->fetchAll(
            "SELECT p.*, c.name as customer_name, s.invoice_number, u.name as received_by_name
             FROM payments p
             JOIN customers c ON p.customer_id = c.id
             JOIN sales s ON p.sale_id = s.id
             LEFT JOIN users u ON p.received_by = u.id
             WHERE p.payment_date = ? ORDER BY p.created_at DESC",
            [$date]
        );
    }
}

// ActivityLogModel
class ActivityLogModel extends BaseModel {
    protected string $table = 'activity_logs';

    public function log(int $userId, string $action, string $module, ?int $recordId = null, $oldValues = null, $newValues = null): void {
        $this->create([
            'user_id'    => $userId,
            'action'     => $action,
            'module'     => $module,
            'record_id'  => $recordId,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ]);
    }

    public function getRecent(int $limit = 50): array {
        return $this->db->fetchAll(
            "SELECT al.*, u.name as user_name FROM activity_logs al
             LEFT JOIN users u ON al.user_id = u.id
             ORDER BY al.created_at DESC LIMIT ?",
            [$limit]
        );
    }
}

// SettingsModel
class SettingsModel extends BaseModel {
    protected string $table = 'settings';

    public function get(string $key, $default = ''): string {
        $row = $this->db->fetch("SELECT value FROM settings WHERE `key` = ?", [$key]);
        return $row ? ($row['value'] ?? $default) : $default;
    }

    public function set(string $key, string $value): void {
        $this->db->execute(
            "INSERT INTO settings (`key`, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)",
            [$key, $value]
        );
    }

    public function getGroup(string $group): array {
        $rows = $this->db->fetchAll("SELECT `key`, value FROM settings WHERE `group` = ?", [$group]);
        $result = [];
        foreach ($rows as $row) {
            $result[$row['key']] = $row['value'];
        }
        return $result;
    }

    public function setMultiple(array $data): void {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
    }
}
