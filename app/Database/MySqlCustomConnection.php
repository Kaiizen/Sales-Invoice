<?php
namespace App\Database;

use Illuminate\Database\MySqlConnection;
use Doctrine\DBAL\Driver\PDOMySql\Driver as DoctrineDriver;

class MySqlCustomConnection extends MySqlConnection
{
    protected function getDoctrineDriver()
    {
        return new DoctrineDriver();
    }
}