<?php

namespace Yiisoft\Yii\Gii\Tests;

use Yiisoft\Yii\Gii\CodeFile;
use Yiisoft\Yii\Gii\Generators\Controller\Generator as ControllerGenerator;
use Yiisoft\Yii\Gii\Generators\Crud\Generator as CRUDGenerator;
use Yiisoft\Yii\Gii\Generators\Extension\Generator as ExtensionGenerator;
use Yiisoft\Yii\Gii\Generators\Form\Generator as FormGenerator;
use Yiisoft\Yii\Gii\Generators\Model\Generator as ModelGenerator;
use Yiisoft\Yii\Gii\Generators\Module\Generator as ModuleGenerator;
use Yiisoft\Yii\Gii\Tests\Profile;

/**
 * GeneratorsTest checks that Gii generators aren't throwing any errors during generation
 * @group gii
 */
class GeneratorsTest extends GiiTestCase
{
    public function testControllerGenerator(): void
    {
        $generator = new ControllerGenerator($this->app);
        $generator->template = 'default';
        $generator->controllerClass = 'app\runtime\TestController';

        $valid = $generator->validate();
        $this->assertTrue($valid, 'Validation failed: ' . print_r($generator->getErrors(), true));

        $this->assertNotEmpty($generator->generate());
    }

    public function testExtensionGenerator()
    {
        $generator = new ExtensionGenerator($this->app);
        $generator->template = 'default';
        $generator->vendorName = 'samdark';
        $generator->namespace = 'samdark\\';
        $generator->license = 'BSD';
        $generator->title = 'Sample extension';
        $generator->description = 'This is sample description.';
        $generator->authorName = 'Alexander Makarov';
        $generator->authorEmail = 'sam@rmcreative.ru';

        $valid = $generator->validate();
        $this->assertTrue($valid, 'Validation failed: ' . print_r($generator->getErrors(), true));

        $this->assertNotEmpty($generator->generate());
    }

    public function testModelGenerator()
    {
        $generator = new ModelGenerator($this->app);
        $generator->template = 'default';
        $generator->tableName = 'profile';
        $generator->modelClass = 'Profile';

        $valid = $generator->validate();
        $this->assertTrue($valid, 'Validation failed: ' . print_r($generator->getErrors(), true));

        $files = $generator->generate();
        $modelCode = $files[0]->content;

        $this->assertTrue(strpos($modelCode, "'id' => 'ID'") !== false, "ID label should be there:\n" . $modelCode);
        $this->assertTrue(strpos($modelCode, "'description' => 'Description',") !== false, "Description label should be there:\n" . $modelCode);
    }

    public function testModuleGenerator()
    {
        $generator = new ModuleGenerator($this->app);
        $generator->template = 'default';
        $generator->moduleID = 'test';
        $generator->moduleClass = 'app\modules\test\Module';

        $valid = $generator->validate();
        $this->assertTrue($valid, 'Validation failed: ' . print_r($generator->getErrors(), true));

        $this->assertNotEmpty($generator->generate());
    }


    public function testFormGenerator()
    {
        $generator = new FormGenerator($this->app);
        $generator->template = 'default';
        $generator->modelClass = Profile::class;
        $generator->viewName = 'profile';
        $generator->viewPath = '@app/runtime';

        $valid = $generator->validate();
        $this->assertTrue($valid, 'Validation failed: ' . print_r($generator->getErrors(), true));

        $this->assertNotEmpty($generator->generate());
    }

    public function testCRUDGenerator()
    {
        $generator = new CRUDGenerator($this->app);
        $generator->template = 'default';
        $generator->modelClass = Profile::class;
        $generator->controllerClass = 'app\TestController';

        $valid = $generator->validate();
        $this->assertTrue($valid, 'Validation failed: ' . print_r($generator->getErrors(), true));

        $this->assertNotEmpty($generator->generate());
    }

    public function testTemplateValidation()
    {
        $generator = new ModelGenerator($this->app);

        // Validate default template
        $generator->template = 'default';
        $this->assertTrue($generator->validate(['template']));

        // Validate custom template
        $this->app->setAlias('@customTemplate', __DIR__ . '/Data/templates');
        $generator->templates = [
            'custom' => '@customTemplate/custom'
        ];
        $generator->template = 'custom';

        $this->assertTrue($generator->validate(['template']));
    }
}
