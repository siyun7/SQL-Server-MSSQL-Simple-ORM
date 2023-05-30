<?php

/**
 * SQL Server (MSSQL) Simple ORM
 * User: L
 * Date: 2023/5/30
 * Time: 11:28
 */


class mssql
{
    /**
     * @var PDO|null
     */
    private ?\PDO $conn = null;

    /**
     * @var string
     */
    private string $table = "";

    /**
     * @throws Exception
     */
    public function __construct($configs = [])
    {
        if (!$configs) {
            throw new Exception("configs is empty");
        }

        $this->conn = new \PDO("sqlsrv:Server={$configs['serverName']};Database={$configs['databaseName']};TrustServerCertificate=1", $configs['username'], $configs['password']);
        $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->conn->setAttribute(\PDO::ATTR_STRINGIFY_FETCHES, false);
        $this->conn->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
    }

    /**
     * @param $table
     * @return $this
     * @throws Exception
     */
    public function from($table): static
    {
        if (!$table) {
            throw new Exception("table name is empty");
        }

        $this->table = $table;
        return $this;
    }

    /**
     * @param $data
     * @return void
     */
    public function insert($data): void
    {
        $dataNum = count($data);
        $query = "INSERT INTO {$this->table} VALUES ";
        $params = [];

        $query .= "(";
        for ($i = 0; $i < $dataNum; $i++) {
            $query .= "?,";
            $params[] = $data[$i];
        }
        $query = substr($query, 0, -1);
        $query .= ");";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
    }

    /**
     * @param $where
     * @param $data
     * @return void
     */
    public function update($where, $data): void
    {
        $params = [];
        $query = "UPDATE {$this->table} SET ";
        foreach ($data as $key => $value) {
            $query .= "{$key} = ?,";
            $params[] = $value;
        }
        $query = substr($query, 0, -1);
        $query .= " WHERE ";

        list($whereQuery, $whereParams) = $this->iGetWhereAndParams($where);
        $query .= $whereQuery;

        $query .= ";";

        $params = array_merge($params, $whereParams);
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
    }

    /**
     * @param $where
     * @param int $offset
     * @param string $order
     * @param string $expression
     * @param int $limit
     * @param array $fields
     * @return bool|array
     */
    public function get($where, array $fields = [], string $order = 'id', int $offset = 0, int $limit = 10, string $expression = 'DESC'): bool|array
    {
        if (!$fields) {
            $fields = "*";
        } else {
            $fields = implode(",", $fields);
        }

        $query = "SELECT {$fields} FROM {$this->table} WHERE ";

        list($whereQuery, $params) = $this->iGetWhereAndParams($where);
        $query .= $whereQuery;

        $query .= " ORDER BY {$order} {$expression} OFFSET {$offset} ROWS FETCH NEXT {$limit} ROWS ONLY;";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param $where
     * @return void
     */
    public function delete($where): void
    {
        $query = "DELETE FROM {$this->table} WHERE ";

        list($whereQuery, $params) = $this->iGetWhereAndParams($where);
        $query .= $whereQuery;

        $query .= ";";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
    }

    /**
     * @param $where
     * @return array
     */
    private function iGetWhereAndParams($where): array
    {
        $query = "";
        $params = [];
        foreach ($where as $key => $item) {
            $query .= "{$key} $item[0] ? AND ";
            if ($item[0] == "LIKE") {
                $item[1] = "%{$item[1]}%";
            }

            if ($item[0] == "IN" && is_array($item[1])) {
                $item[1] = implode(",", $item[1]);
                $item[1] = "({$item[1]})";
            }

            if ($item[0] == "BETWEEN" && is_array($item[1])) {
                $item[1] = implode(" AND ", $item[1]);
            }

            $params[] = $item[1];
        }

        $query = substr($query, 0, -4);

        return [$query, $params];
    }
}