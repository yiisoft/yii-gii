<?php

namespace Yiisoft\Yii\Gii\Tests\Generators;

use Yiisoft\Db\ColumnSchema;
use Yiisoft\Yii\Gii\Generators\Crud\Generator;
use Yiisoft\Yii\Gii\Tests\GiiTestCase;

class CrudGeneratorTest extends GiiTestCase
{
    public function testGenerateColumnFormat()
    {
        $g = new Generator($this->app);

        $c = new ColumnSchema();
        $c->phpType = 'boolean';
        $c->type = 'boolean';
        $c->name = 'is_enabled';
        $this->assertEquals('boolean', $g->generateColumnFormat($c));

        $c = new ColumnSchema();
        $c->phpType = 'string';
        $c->type = 'text';
        $c->name = 'description';
        $this->assertEquals('ntext', $g->generateColumnFormat($c));

        $c = new ColumnSchema();
        $c->phpType = 'integer';
        $c->type = 'integer';
        $c->name = 'create_time';
        $this->assertEquals('datetime', $g->generateColumnFormat($c));

        $c = new ColumnSchema();
        $c->phpType = 'string';
        $c->type = 'string';
        $c->name = 'email_address';
        $this->assertEquals('email', $g->generateColumnFormat($c));

        // url type and false positive checks for URL
        $c = new ColumnSchema();
        $c->phpType = 'string';
        $c->type = 'string';
        $c->name = 'hourly';
        $this->assertEquals('text', $g->generateColumnFormat($c));

        $c = new ColumnSchema();
        $c->phpType = 'string';
        $c->type = 'string';
        $c->name = 'some_hourly_check';
        $this->assertEquals('text', $g->generateColumnFormat($c));

        $c = new ColumnSchema();
        $c->phpType = 'string';
        $c->type = 'string';
        $c->name = 'some_url';
        $this->assertEquals('url', $g->generateColumnFormat($c));

        $c = new ColumnSchema();
        $c->phpType = 'string';
        $c->type = 'string';
        $c->name = 'my_url_string';
        $this->assertEquals('url', $g->generateColumnFormat($c));

        $c = new ColumnSchema();
        $c->phpType = 'string';
        $c->type = 'string';
        $c->name = 'url_lalala';
        $this->assertEquals('url', $g->generateColumnFormat($c));
    }

    public function testGeneratedControllerId()
    {
        $g = new Generator($this->app);
        $g->controllerClass = '\app\controllers\TestController';
        $this->assertEquals('test', $g->getControllerID());

        $g = new Generator($this->app);
        $g->controllerClass = '\app\controllers\SomeTestController';
        $this->assertEquals('some-test', $g->getControllerID());

        $g = new Generator($this->app);
        $g->controllerClass = '\app\controllers\ATestController';
        $this->assertEquals('a-test', $g->getControllerID());

        $g = new Generator($this->app);
        $g->controllerClass = '\app\controllers\YoTestController';
        $this->assertEquals('yo-test', $g->getControllerID());

        $g = new Generator($this->app);
        $g->controllerClass = '\app\controllers\ABCTestController';
        $this->assertEquals('a-b-c-test', $g->getControllerID());

        $g = new Generator($this->app);
        $g->controllerClass = '\app\controllers\XYTestController';
        $this->assertEquals('x-y-test', $g->getControllerID());
    }
}
