<?php

namespace yii\gii\tests;

use yii\helpers\Yii;
use yii\helpers\FileHelper;
use yii\tests\TestCase;

/**
 * GiiTestCase is the base class for all gii related test cases
 * @group gii
 */
class GiiTestCase extends TestCase
{
    protected $driverName = 'sqlite';

    protected function setUp()
    {
        parent::setUp();

        FileHelper::createDirectory(__DIR__ . '/runtime');

        $allConfigs = require(__DIR__ . '/data/config.php');

        $config = $allConfigs['databases'][$this->driverName];
        $pdo_database = 'pdo_' . $this->driverName;

        if (!\extension_loaded('pdo') || !\extension_loaded($pdo_database)) {
            $this->markTestSkipped('pdo and ' . $pdo_database . ' extension are required.');
        }

        $this->mockWebApplication();

        $this->container->set('db', [
            '__class' => isset($config['__class']) ? $config['__class'] : \yii\db\Connection::class,
            'dsn' => $config['dsn'],
            'username' => isset($config['username']) ? $config['username'] : null,
            'password' => isset($config['password']) ? $config['password'] : null,
        ]);


        if (isset($config['fixture'])) {
            $this->app->db->open();
            $lines = explode(';', file_get_contents($config['fixture']));
            foreach ($lines as $line) {
                if (trim($line) !== '') {
                    $this->app->db->pdo->exec($line);
                }
            }
        }
    }
}
