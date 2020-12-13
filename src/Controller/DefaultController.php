<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Controller;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Http\Status;
use Yiisoft\View\ViewContextInterface;
use Yiisoft\View\WebView;
use Yiisoft\Yii\Gii\Generator\AbstractGenerator;
use Yiisoft\Yii\Gii\GeneratorInterface;
use Yiisoft\Yii\Gii\GiiInterface;

class DefaultController implements ViewContextInterface
{
    private string $layout;
    private ResponseFactoryInterface $responseFactory;
    private GiiInterface $gii;
    private WebView $view;
    private Aliases $aliases;

    public function __construct(
        GiiInterface $gii,
        WebView $view,
        Aliases $aliases,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->gii = $gii;
        $this->responseFactory = $responseFactory;
        $this->view = $view;
        $this->aliases = $aliases;
        $this->layout = $aliases->get('@yiisoft/yii-gii/views') . '/layout/generator';
    }

    public function index(): string
    {
        $this->layout = 'main';

        return $this->render('index');
    }

    public function view(ServerRequestInterface $request): string
    {
        $id = $request->getAttribute('id');
        /** @var AbstractGenerator $generator */
        $generator = $this->loadGenerator($id, $request);
        $params = ['generator' => $generator, 'id' => $id];

        $preview = $request->getAttribute('preview');
        $generate = $request->getAttribute('generate');
        $answers = $request->getAttribute('answers');

        if ($preview !== null || $generate !== null) {
            if ($generator->validate()) {
                $generator->saveStickyAttributes();
                $files = $generator->generate();
                if ($generate !== null && !empty($answers)) {
                    $results = [];
                    $params['hasError'] = !$generator->save($files, (array)$answers, $results);
                    $params['results'] = $results;
                } else {
                    $params['files'] = $files;
                    $params['answers'] = $answers;
                }
            }
        }

        return $this->render('view', $params);
    }

    /**
     * @param ServerRequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface|string
     */
    public function preview(ServerRequestInterface $request)
    {
        $id = $request->getAttribute('id');
        $file = $request->getAttribute('file');
        $generator = $this->loadGenerator($id, $request);
        if ($generator->validate()) {
            foreach ($generator->generate() as $f) {
                if ($f->getId() === $file) {
                    $content = $f->preview();
                    if ($content !== false) {
                        return '<div class="content">' . $content . '</div>';
                    }

                    return '<div class="error">Preview is not available for this file type.</div>';
                }
            }
        }

        $response = $this->responseFactory->createResponse(Status::UNPROCESSABLE_ENTITY);
        $response->getBody()->write("Code file not found: $file");
        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface|string
     */
    public function diff(ServerRequestInterface $request)
    {
        $id = $request->getAttribute('id');
        $file = $request->getAttribute('file');
        $generator = $this->loadGenerator($id, $request);
        if ($generator->validate()) {
            foreach ($generator->generate() as $f) {
                if ($f->getId() === $file) {
                    return $this->render(
                        'diff',
                        [
                            'diff' => $f->diff(),
                        ]
                    );
                }
            }
        }

        $response = $this->responseFactory->createResponse(Status::UNPROCESSABLE_ENTITY);
        $response->getBody()->write("Code file not found: $file");
        return $response;
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
    public function action(ServerRequestInterface $request)
    {
        $id = $request->getAttribute('id');
        $action = $request->getAttribute('action');
        /** @var AbstractGenerator $generator */
        $generator = $this->loadGenerator($id, $request);
        if (method_exists($generator, $action)) {
            return $generator->$action();
        }

        $response = $this->responseFactory->createResponse(Status::UNPROCESSABLE_ENTITY);
        $response->getBody()->write("Unknown generator action: {$action}");
        return $response;
    }

    /**
     * @param string $id
     * @param ServerRequestInterface $request
     * @return GeneratorInterface|AbstractGenerator
     */
    protected function loadGenerator(string $id, ServerRequestInterface $request): GeneratorInterface
    {
        /** @var AbstractGenerator $generator */
        $generator = $this->gii->getGenerator($id);
        $generator->loadStickyAttributes();
        $generator->load((array)$request->getParsedBody());

        return $generator;
    }

    private function render(string $view, array $parameters = []): string
    {
        $content = $this->view->render($view, $parameters, $this);
        return $this->renderContent($content);
    }

    private function renderContent(string $content): string
    {
        $layout = $this->findLayoutFile($this->layout);
        if ($layout !== null) {
            return $this->view->renderFile(
                $layout,
                [
                    'content' => $content,
                ],
                $this
            );
        }

        return $content;
    }

    public function getViewPath(): string
    {
        return $this->aliases->get('@yiisoft/yii-gii/views') . '/default';
    }

    private function findLayoutFile(?string $file): ?string
    {
        if ($file === null) {
            return null;
        }

        if (pathinfo($file, PATHINFO_EXTENSION) !== '') {
            return $file;
        }

        return $file . '.' . $this->view->getDefaultExtension();
    }
}
