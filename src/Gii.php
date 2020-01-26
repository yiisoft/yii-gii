<?php

namespace Yiisoft\Yii\Gii;

use Yiisoft\Yii\Gii\Generators\GeneratorInterface;

final class Gii implements GiiInterface
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = \Yiisoft\Yii\Gii\Controllers\DefaultController::class;
    /**
     * @var array the list of IPs that are allowed to access this module.
     * Each array element represents a single IP filter which can be either an IP address
     * or an address with wildcard (e.g. 192.168.0.*) to represent a network segment.
     * The default value is `['127.0.0.1', '::1']`, which means the module can only be accessed
     * by localhost.
     */
    private array $allowedIPs = ['127.0.0.1', '::1'];
    /**
     * @var array|GeneratorInterface[] a list of generator configurations or instances. The array keys
     * are the generator IDs (e.g. "crud"), and the array elements are the corresponding generator
     * configurations or the instances.
     *
     * After the module is initialized, this property will become an array of generator instances
     * which are created based on the configurations previously taken by this property.
     *
     * Newly assigned generators will be merged with the [[coreGenerators()|core ones]], and the former
     * takes precedence in case when they have the same generator ID.
     */
    private array $generators = [];
    /**
     * @var int the permission to be set for newly generated code files.
     * This value will be used by PHP chmod function.
     * Defaults to 0666, meaning the file is read-writable by all users.
     */
    private int $newFileMode = 0666;
    /**
     * @var int the permission to be set for newly generated directories.
     * This value will be used by PHP chmod function.
     * Defaults to 0777, meaning the directory can be read, written and executed by all users.
     */
    private int $newDirMode = 0777;


    /**
     * {@inheritdoc}
     */
    public function bootstrap($app)
    {
        if ($app instanceof \yii\web\Application) {
            $app->getUrlManager()->addRules(
                [
                    [
                        '__class' => \yii\web\UrlRule::class,
                        'pattern' => $this->id,
                        'route' => $this->id . '/default/index'
                    ],
                    [
                        '__class' => \yii\web\UrlRule::class,
                        'pattern' => $this->id . '/<id:\w+>',
                        'route' => $this->id . '/default/view'
                    ],
                    [
                        '__class' => \yii\web\UrlRule::class,
                        'pattern' => $this->id . '/<controller:[\w\-]+>/<action:[\w\-]+>',
                        'route' => $this->id . '/<controller>/<action>'
                    ],
                ],
                false
            );
        } elseif ($app instanceof \Yiisoft\Yii\Console\Application) {
            $app->controllerMap[$this->id] = [
                '__class' => \Yiisoft\Yii\Gii\Console\GenerateController::class,
                'generators' => array_merge($this->coreGenerators(), $this->generators),
                'module' => $this,
            ];
        }
    }

    public function addGenerator(string $name, $generator)
    {
    }

    public function getGenerator(string $name)
    {
    }

    /**
     * @return int whether the module can be accessed by the current user
     */
    protected function checkAccess()
    {
        $ip = $this->app->getRequest()->getUserIP();
        foreach ($this->allowedIPs as $filter) {
            if ($filter === '*' || $filter === $ip || (($pos = strpos($filter, '*')) !== false && !strncmp(
                        $ip,
                        $filter,
                        $pos
                    ))) {
                return true;
            }
        }
        Yii::warning('Access to Gii is denied due to IP address restriction. The requested IP is ' . $ip, __METHOD__);

        return false;
    }

    /**
     * Returns the list of the core code generator configurations.
     * @return array the list of the core code generator configurations.
     */
    protected function coreGenerators()
    {
        return [
            'model' => ['__class' => \Yiisoft\Yii\Gii\Generators\Model\Generator::class],
            'crud' => ['__class' => \Yiisoft\Yii\Gii\Generators\Crud\Generator::class],
            'controller' => ['__class' => \Yiisoft\Yii\Gii\Generators\Controller\Generator::class],
            'form' => ['__class' => \Yiisoft\Yii\Gii\Generators\Form\Generator::class],
            'module' => ['__class' => \Yiisoft\Yii\Gii\Generators\Module\Generator::class],
            'extension' => ['__class' => \Yiisoft\Yii\Gii\Generators\Extension\Generator::class],
        ];
    }

    /**
     * {@inheritdoc}
     * @since 2.0.6
     */
    protected function defaultVersion()
    {
        $packageInfo = Json::decode(file_get_contents(\dirname(__DIR__) . DIRECTORY_SEPARATOR . 'composer.json'));
        $extensionName = $packageInfo['name'];
        if (isset($this->app->extensions[$extensionName])) {
            return $this->app->extensions[$extensionName]['version'];
        }
        return parent::defaultVersion();
    }
}
