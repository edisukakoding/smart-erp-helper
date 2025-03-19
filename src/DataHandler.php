<?php

namespace Esikat\Helper;

use PDO;
use PDOException;

class DataHandler {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Menangani request DataTable dan mengembalikan data dalam format JSON.
     *
     * @param string $table Nama tabel database.
     * @param array $columns Daftar kolom yang akan ditampilkan.
     * @param string $primaryKey Kunci utama tabel (default: 'id').
     * @param string $join Query join tambahan (default: '').
     * @param array $joinColumns Kolom tambahan dari tabel yang di-join.
     *
     * @return string JSON data sesuai dengan format DataTable.
     */
    public function datatable(string $table, array $columns, string $primaryKey = 'id', string $join = '', array $joinColumns = []) {
        $draw = $_GET['draw'] ?? 1;
        $start = $_GET['start'] ?? 0;
        $length = $_GET['length'] ?? 10;
        $searchValue = $_GET['search']['value'] ?? '';
        
        $columnIndex = $_GET['order'][0]['column'] ?? 0;
        $columnName = $columns[$columnIndex] ?? $primaryKey;
        $columnOrder = $_GET['order'][0]['dir'] ?? 'asc';
        
        $allColumns = array_merge($columns, $joinColumns);
        
        $whereConditions = [];
        $params = [];
        
        if (!empty($_GET['where']) && is_array($_GET['where'])) {
            foreach ($_GET['where'] as $key => $value) {
                $paramKey = str_replace('.', '_', $key);
                $whereConditions[] = "$key = :$paramKey";
                $params[":$paramKey"] = $value;
            }
        }
        
        if (!empty($searchValue)) {
            $searchConditions = [];
            foreach ($allColumns as $col) {
                $searchConditions[] = "$col LIKE :search";
            }
            $whereConditions[] = '(' . implode(' OR ', $searchConditions) . ')';
            $params[':search'] = "%$searchValue%";
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        $sql = "SELECT COUNT($primaryKey) FROM $table $join";
        $totalRecords = $this->pdo->query($sql)->fetchColumn();
        
        $sql = "SELECT COUNT($primaryKey) FROM $table $join $whereClause";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        $filteredRecords = $stmt->fetchColumn();
        
        $sql = "SELECT " . implode(", ", $allColumns) . " FROM $table $join $whereClause ORDER BY $columnName $columnOrder LIMIT :start, :length";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
        $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return json_encode([
            'draw' => (int)$draw,
            'recordsTotal' => (int)$totalRecords,
            'recordsFiltered' => (int)$filteredRecords,
            'data' => $data
        ]);
    }

    /**
     * Mengambil data untuk Select2 dalam format JSON.
     *
     * @param string $table Nama tabel database.
     * @param string $idColumn Nama kolom yang digunakan sebagai ID.
     * @param string $textColumn Nama kolom yang digunakan sebagai teks.
     * @param string $extraCondition Kondisi tambahan untuk filter data.
     * @param array $joins Daftar tabel join dengan format [['type' => 'INNER', 'table' => 'other_table', 'on' => 'table.id = other_table.table_id']].
     *
     * @return array Data dalam format JSON untuk Select2.
     */
    public function select2(string $table, string $idColumn, string $textColumn, string $extraCondition = '', array $joins = []) {
        try {
            $search = $_GET['q'] ?? '';
            $limit  = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

            $joinSQL = "";
            if (!empty($joins)) {
                foreach ($joins as $join) {
                    $joinSQL .= " {$join['type']} JOIN {$join['table']} ON {$join['on']}";
                }
            }

            $whereSQL = "WHERE $textColumn LIKE :search";
            if (!empty($extraCondition)) {
                $whereSQL .= " AND $extraCondition";
            }

            $sql = "SELECT $idColumn AS id, $textColumn AS text FROM $table $joinSQL $whereSQL LIMIT :limit";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ["results" => $data];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}