# SQL Server (MSSQL) Simple ORM

## Usage

```
        $configs['serverName'] = "127.0.0.1";
        $configs['databaseName'] = "TestDB";
        $configs['username'] = "SA";
        $configs['password'] = 'yourpassword';
		
	$service = new MssqlService($configs);
		
	$where = ['id' => ['>', 6]];
        $updateWhere = ['id' => ['=', 6]];
        $attrs = ['quantity' => random_int(7777, 9999), 'name' => $this->generateRandomChineseString(5)];
        $service->from('Inventory')->update($updateWhere, $attrs);

        $service->from('Inventory')->delete($updateWhere);

        $list = $service->from('Inventory')->get($where, ['id', 'name', 'quantity'], 'id', 0, 20);
     
```
