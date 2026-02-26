<?php

abstract class BaseModel {
    protected Database $db;
    protected string $table = '';
    protected string $primaryKey = 'id';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function find(int $id): ?array {
        return $this->db->fetch(
            "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?",
            [$id]
        );
    }

    public function findAll(array $conditions = [], string $orderBy = '', int $limit = 0, int $offset = 0): array {
        $sql = "SELECT * FROM `{$this->table}`";
        $params = [];
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $col => $val) {
                $where[] = "`$col` = ?";
                $params[] = $val;
            }
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        if ($orderBy) $sql .= " ORDER BY $orderBy";
        if ($limit > 0) $sql .= " LIMIT $limit";
        if ($offset > 0) $sql .= " OFFSET $offset";
        return $this->db->fetchAll($sql, $params);
    }

    public function create(array $data): int {
        $cols = array_keys($data);
        $placeholders = array_fill(0, count($cols), '?');
        $sql = "INSERT INTO `{$this->table}` (`" . implode('`, `', $cols) . "`) VALUES (" . implode(', ', $placeholders) . ")";
        $this->db->execute($sql, array_values($data));
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): int {
        $sets = [];
        $params = [];
        foreach ($data as $col => $val) {
            $sets[] = "`$col` = ?";
            $params[] = $val;
        }
        $params[] = $id;
        $sql = "UPDATE `{$this->table}` SET " . implode(', ', $sets) . " WHERE `{$this->primaryKey}` = ?";
        return $this->db->execute($sql, $params);
    }

    public function delete(int $id): int {
        return $this->db->execute("DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?", [$id]);
    }

    public function count(array $conditions = []): int {
        $sql = "SELECT COUNT(*) FROM `{$this->table}`";
        $params = [];
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $col => $val) {
                $where[] = "`$col` = ?";
                $params[] = $val;
            }
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        return (int)$this->db->fetch($sql, $params)['COUNT(*)'];
    }

    public function paginate(string $sql, array $params, int $page, int $perPage = DEFAULT_PAGE_SIZE): array {
        $countSql = preg_replace('/SELECT.*?FROM/is', 'SELECT COUNT(*) as total FROM', $sql, 1);
        $countSql = preg_replace('/ORDER BY.*/i', '', $countSql);
        $total = (int)($this->db->fetch($countSql, $params)['total'] ?? 0);
        $offset = ($page - 1) * $perPage;
        $data = $this->db->fetchAll("$sql LIMIT $perPage OFFSET $offset", $params);
        return [
            'data'         => $data,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int)ceil($total / $perPage),
        ];
    }
}
