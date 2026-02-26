<?php

class DashboardController extends BaseController {
    public function index(): void {
        $this->requireAuth();
        $db = Database::getInstance();

        // Summary counts
        $stats = [
            'visitors'     => (int)($db->fetch("SELECT COUNT(*) as c FROM visitors WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")['c'] ?? 0),
            'leads'        => (int)($db->fetch("SELECT COUNT(*) as c FROM leads WHERE status NOT IN('converted','lost')")['c'] ?? 0),
            'customers'    => (int)($db->fetch("SELECT COUNT(*) as c FROM customers")['c'] ?? 0),
            'today_sales'  => (int)($db->fetch("SELECT COUNT(*) as c FROM sales WHERE sale_date=CURDATE()")['c'] ?? 0),
            'pending_followups' => (int)($db->fetch("SELECT COUNT(*) as c FROM followups WHERE status='pending' AND followup_date<=CURDATE()")['c'] ?? 0),
            'monthly_revenue'   => (float)($db->fetch("SELECT COALESCE(SUM(total_amount),0) as t FROM sales WHERE MONTH(sale_date)=MONTH(NOW()) AND YEAR(sale_date)=YEAR(NOW())")['t'] ?? 0),
            'total_collected'   => (float)($db->fetch("SELECT COALESCE(SUM(amount_paid),0) as t FROM sales WHERE MONTH(sale_date)=MONTH(NOW()) AND YEAR(sale_date)=YEAR(NOW())")['t'] ?? 0),
            'outstanding'       => (float)($db->fetch("SELECT COALESCE(SUM(balance_due),0) as t FROM sales WHERE payment_status!='paid'")['t'] ?? 0),
        ];

        // Low stock
        $invModel = new InventoryModel();
        $lowStock = $invModel->getLowStock();

        // Today's followups
        $leadModel = new LeadModel();
        $todayFollowups = $leadModel->getTodayFollowups();

        // Monthly chart data
        $saleModel = new SaleModel();
        $monthlyData = $saleModel->getMonthlySummary((int)date('Y'));

        // Source breakdown
        $sourceData = $db->fetchAll(
            "SELECT ms.name, ms.type, COUNT(l.id) as total
             FROM marketing_sources ms
             LEFT JOIN leads l ON l.source_id = ms.id
             GROUP BY ms.id ORDER BY total DESC"
        );

        // Top models
        $topModels = $saleModel->getTopModels(5);

        // Recent activity
        $activityLog = new ActivityLogModel();
        $recentActivity = $activityLog->getRecent(10);

        // Lead conversion
        $totalLeads = (int)($db->fetch("SELECT COUNT(*) as c FROM leads")['c'] ?? 1);
        $convertedLeads = (int)($db->fetch("SELECT COUNT(*) as c FROM leads WHERE status='converted'")['c'] ?? 0);
        $stats['conversion_rate'] = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 1) : 0;

        $this->view('dashboard/index', compact('stats', 'lowStock', 'todayFollowups', 'monthlyData', 'sourceData', 'topModels', 'recentActivity'));
    }
}
