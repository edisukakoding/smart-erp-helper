<?php

namespace Esikat\Helper;

use PDO;
use InvalidArgumentException;

class QueryBuilder
{
    private $pdo;
    private $table;
    private $columns = '*';
    private $conditions = [];
    private $bindings = [];
    private $limit;
    private $offset;
    private $orderBy;
    private $joins = [];

    /**
     * Konstruktor untuk inisialisasi koneksi PDO.
     *
     * @param PDO $pdo Koneksi PDO untuk mengakses database.
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Reset semua properti query builder untuk query baru.
     */
    private function reset(): void
    {
        $this->table = null;
        $this->columns = '*';
        $this->conditions = [];
        $this->bindings = [];
        $this->limit = null;
        $this->offset = null;
        $this->orderBy = null;
        $this->joins = [];
    }

    /**
     * Menentukan tabel yang akan digunakan dalam query.
     *
     * @param string $table Nama tabel.
     * @param string|null $alias Alias tabel (opsional).
     * 
     * @return self Instance dari QueryBuilder.
     */
    public function table(string $table, ?string $alias = null): self
    {
        $this->reset();
        $this->table = $alias ? "$table AS $alias" : $table;
        return $this;
    }

    /**
     * Menentukan kolom yang akan diambil dalam query.
     *
     * @param mixed $columns Kolom yang akan dipilih (default: semua kolom).
     * 
     * @return self Instance dari QueryBuilder.
     */
    public function select($columns = '*'): self
    {
        if (is_array($columns)) {
            $this->columns = implode(', ', $columns);
        } else {
            $this->columns = $columns;
        }
        return $this;
    }

    /**
     * Menambahkan kondisi WHERE pada query.
     *
     * @param string|array $column Nama kolom atau array kondisi.
     * @param string|null $operator Operator perbandingan (e.g., '=', '<', '>').
     * @param mixed $value Nilai yang akan dibandingkan (jika $column adalah string).
     * 
     * @return self Instance dari QueryBuilder.
     */
    public function where(string|array $column, ?string $operator = null, mixed $value = null): self
    {
        if (is_array($column)) {
            // Jika parameter pertama adalah array, buat kondisi dari key-value
            foreach ($column as $col => $val) {
                $this->conditions[] = "$col = ?";
                $this->bindings[] = $val;
            }
        } else {
            // Validasi parameter
            if ($operator === null || $value === null) {
                throw new InvalidArgumentException("Jika parameter pertama adalah string, operator dan value harus diisi.");
            }

            // Jika parameter biasa (string, operator, value)
            $this->conditions[] = "$column $operator ?";
            $this->bindings[] = $value;
        }
        return $this;
    }


    /**
     * Menambahkan kondisi OR WHERE pada query.
     *
     * @param string $column Nama kolom.
     * @param string $operator Operator perbandingan (e.g., '=', '<', '>').
     * @param mixed $value Nilai yang akan dibandingkan.
     * 
     * @return self Instance dari QueryBuilder.
     */
    public function orWhere(string $column, string $operator, $value): self
    {
        if ($value instanceof self) {
            $subquery = $value->toSql();
            $this->conditions[] = "OR $column $operator ($subquery)";
            $this->bindings = array_merge($this->bindings, $value->getBindings());
        } else {
            $this->conditions[] = "OR $column $operator ?";
            $this->bindings[] = $value;
        }
        return $this;
    }

    /**
     * Menambahkan join (INNER, LEFT, RIGHT) pada query.
     *
     * @param string $table Nama tabel yang akan di-join.
     * @param array $conditions Kondisi join yang akan digunakan.
     * @param string $type Jenis join (default: 'INNER').
     * @param string|null $alias Alias untuk tabel yang di-join (opsional).
     * 
     * @return self Instance dari QueryBuilder.
     */
    public function join(string $table, array $conditions, string $type = 'INNER', ?string $alias = null): self
    {
        $aliasClause = $alias ? " AS $alias" : '';
        $joinConditions = [];
        foreach ($conditions as $condition) {
            [$first, $operator, $second] = $condition;
            $joinConditions[] = "$first $operator $second";
        }
        $this->joins[] = "$type JOIN $table$aliasClause ON " . implode(' AND ', $joinConditions);
        return $this;
    }

    /**
     * Menambahkan LEFT JOIN pada query.
     *
     * @param string $table Nama tabel yang akan di-join.
     * @param array $conditions Kondisi join yang akan digunakan.
     * @param string|null $alias Alias untuk tabel yang di-join (opsional).
     * 
     * @return self Instance dari QueryBuilder.
     */
    public function leftJoin(string $table, array $conditions, ?string $alias = null): self
    {
        if ($alias) {
            $table = "$table $alias";
        }
        return $this->join($table, $conditions, 'LEFT');
    }

    /**
     * Menambahkan RIGHT JOIN pada query.
     *
     * @param string $table Nama tabel yang akan di-join.
     * @param array $conditions Kondisi join yang akan digunakan.
     * @param string|null $alias Alias untuk tabel yang di-join (opsional).
     * 
     * @return self Instance dari QueryBuilder.
     */
    public function rightJoin(string $table, array $conditions, ?string $alias = null): self
    {
        if ($alias) {
            $table = "$table $alias";
        }
        return $this->join($table, $conditions, 'RIGHT');
    }

    /**
     * Menambahkan limit pada query.
     *
     * @param int $limit Jumlah data yang akan dibatasi.
     * 
     * @return self Instance dari QueryBuilder.
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Menambahkan offset pada query.
     *
     * @param int $offset Posisi data mulai diambil.
     * 
     * @return self Instance dari QueryBuilder.
     */
    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Menambahkan pengurutan data pada query.
     *
     * @param string $column Kolom yang akan diurutkan.
     * @param string $direction Arah pengurutan ('ASC' atau 'DESC').
     * 
     * @return self Instance dari QueryBuilder.
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy = "$column $direction";
        return $this;
    }

    /**
     * Menjalankan query dan mengembalikan hasil dalam bentuk array.
     *
     * @return array Hasil query dalam bentuk array asosiatif.
     */
    public function get(): array
    {
        $sql = $this->toSql();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->reset();
        return $result;
    }

    /**
     * Menjalankan query dan mengembalikan hasil pertama.
     *
     * @return array|null Hasil query pertama atau null jika tidak ada.
     */
    public function first(): ?array
    {
        $sql = $this->toSql() . ' LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);
        $result = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        $this->reset();
        return $result;
    }

    /**
     * Menghasilkan query SQL dalam bentuk string.
     *
     * @return string Query SQL lengkap.
     */
    public function toSql(): string
    {
        $sql = "SELECT {$this->columns} FROM {$this->table}";

        if ($this->joins) {
            $sql .= ' ' . implode(' ', $this->joins);
        }

        if ($this->conditions) {
            // Gabungkan kondisi dengan AND agar format WHERE benar
            $whereClause = implode(' AND ', $this->conditions);
            // Hapus "OR " di awal jika ada (untuk menjaga kompatibilitas kode lama)
            $sql .= " WHERE " . preg_replace('/^OR /', '', $whereClause);
        }

        if ($this->orderBy) {
            $sql .= " ORDER BY {$this->orderBy}";
        }

        if ($this->limit) {
            $sql .= " LIMIT {$this->limit}";
        }

        if ($this->offset) {
            $sql .= " OFFSET {$this->offset}";
        }

        return $sql;
    }


    /**
     * Mendapatkan semua binding parameter untuk query.
     *
     * @return array Daftar parameter binding.
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * Menyisipkan data baru ke dalam tabel.
     *
     * @param array $data Data yang akan disisipkan (kolom => nilai).
     * 
     * @return bool Status eksekusi query.
     */
    public function insert(array $data): bool
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";

        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute(array_values($data));
        $this->reset();
        return $result;
    }

    /**
     * Memperbarui data di dalam tabel.
     *
     * @param array $data Data yang akan diperbarui (kolom => nilai).
     * 
     * @return bool Status eksekusi query.
     */
    public function update(array $data): bool
    {
        $setClauses = [];
        $updateBindings = []; // Simpan sementara data update

        foreach ($data as $column => $value) {
            $setClauses[] = "$column = ?";
            $updateBindings[] = $value; // Data update dulu
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses);

        if ($this->conditions) {
            $sql .= " WHERE " . implode(' AND ', $this->conditions);
        }

        $stmt = $this->pdo->prepare($sql);

        // Urutan bindings harus: DATA UPDATE dulu, baru kondisi WHERE
        $bindings = array_merge($updateBindings, $this->bindings);

        $result = $stmt->execute($bindings);
        $this->reset();
        return $result;
    }

    /**
     * Menghapus data dari tabel.
     *
     * @return bool Status eksekusi query.
     */
    public function delete(): bool
    {
        $sql = "DELETE FROM {$this->table}";

        if ($this->conditions) {
            // Gabungkan kondisi dengan AND agar format WHERE benar
            $conditions = implode(' AND ', $this->conditions);
            // Hapus "AND " atau "OR " di awal jika ada (untuk kompatibilitas kode lama)
            $sql .= " WHERE " . preg_replace('/^(AND |OR )/', '', $conditions);
        }

        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute($this->bindings);
        $this->reset();
        return $result;
    }
}
