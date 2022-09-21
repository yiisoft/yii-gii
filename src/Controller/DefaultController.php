<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Http\Status;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Yii\Gii\Generator\AbstractGenerator;
use Yiisoft\Yii\Gii\GeneratorInterface;
use Yiisoft\Yii\Gii\GiiInterface;

class DefaultController
{
    public function __construct(
        private DataResponseFactoryInterface $responseFactory,
        private GiiInterface $gii,
    ) {
    }

    public function view(CurrentRoute $currentRoute, ServerRequestInterface $request)
    {
        $id = $currentRoute->getArgument('id');
        /** @var AbstractGenerator $generator */
        $generator = $this->loadGenerator($id, $request);
        $params = [
            'id' => $id,
            'generator' => [
                'name' => $generator->getName(),
                'description' => $generator->getDescription(),
                'template' => $generator->getTemplate(),
                'templatePath' => $generator->getTemplatePath(),
                'templates' => $generator->getTemplates(),
                'viewPath' => $generator->getViewPath(),
                'directory' => $generator->getDirectory(),
            ],
        ];

        $preview = $currentRoute->getArgument('preview');
        $generate = $currentRoute->getArgument('generate');
        $answers = $currentRoute->getArgument('answers');

        if ($preview !== null || $generate !== null) {
            if ($generator->validate()->isValid()) {
                $generator->saveStickyAttributes();
                $files = $generator->generate();
                if ($generate !== null && !empty($answers)) {
                    $results = [];
                    $params['hasError'] = !$generator->save($files, (array) $answers, $results);
                    $params['results'] = $results;
                } else {
                    $params['files'] = $files;
                    $params['answers'] = $answers;
                }
            }
        }

        return $this->responseFactory->createResponse([
            'params' => $params,
        ]);
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function preview(CurrentRoute $currentRoute, ServerRequestInterface $request): ResponseInterface
    {
        $id = $currentRoute->getArgument('id');
        $file = $currentRoute->getArgument('file');
        $generator = $this->loadGenerator($id, $request);

        if ($generator->validate()->isValid()) {
            foreach ($generator->generate() as $f) {
                if ($f->getId() === $file) {
                    return $this->responseFactory->createResponse([
                        'preview' => $f->preview(),
                    ]);
                }
            }
        }

        return $this->responseFactory->createResponse(['error' => "Code file not found: $file"],
            Status::UNPROCESSABLE_ENTITY);
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function diff(CurrentRoute $currentRoute, ServerRequestInterface $request): ResponseInterface
    {
        $id = $currentRoute->getArgument('id');
        $file = $currentRoute->getArgument('file');
        $generator = $this->loadGenerator($id, $request);
        if ($generator->validate()->isValid()) {
            foreach ($generator->generate() as $f) {
                if ($f->getId() === $file) {
                    return $this->responseFactory->createResponse([
                        'diff' => $f->diff(),
                    ]);
                }
            }
        }
        return $this->responseFactory->createResponse(['error' => "Code file not found: $file"],
            Status::UNPROCESSABLE_ENTITY);
    }

    /**
     * Runs an action defined in the generator.
     * Given an action named "xyz", the method "actionXyz()" in the generator will be called.
     * If the method does not exist, a 400 HTTP exception will be thrown.
     *
     * @param ServerRequestInterface $request
     *
     * @return mixed the result of the action.
     */
    public function action(CurrentRoute $currentRoute, ServerRequestInterface $request): ResponseInterface
    {
        $id = $currentRoute->getArgument('id');
        $action = $currentRoute->getArgument('action');
        /** @var AbstractGenerator $generator */
        $generator = $this->loadGenerator($id, $request);
        if (method_exists($generator, $action)) {
            return $generator->$action();
        }

        return $this->responseFactory->createResponse(['error' => "Unknown generator action: $action"],
            Status::UNPROCESSABLE_ENTITY);
    }

    /**
     * @param string $id
     * @param ServerRequestInterface $request
     *
     * @return AbstractGenerator|GeneratorInterface
     */
    protected function loadGenerator(string $id, ServerRequestInterface $request): GeneratorInterface
    {
        /** @var AbstractGenerator $generator */
        $generator = $this->gii->getGenerator($id);
        $generator->loadStickyAttributes();
        $generator->load((array) $request->getParsedBody());

        return $generator;
    }
}
