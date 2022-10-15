<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Controller;

use Psr\Http\Message\ResponseInterface;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Http\Status;
use Yiisoft\RequestModel\Attribute\Query;
use Yiisoft\Yii\Gii\CodeFile;
use Yiisoft\Yii\Gii\Generator\AbstractGenerator;
use Yiisoft\Yii\Gii\Request\GeneratorRequest;

final class DefaultController
{
    public function __construct(private DataResponseFactoryInterface $responseFactory)
    {
    }

    public function get(GeneratorRequest $request): ResponseInterface
    {
        /** @var AbstractGenerator $generator */
        $generator = $request->getGenerator();
        $params = [
            'name' => $generator->getName(),
            'description' => $generator->getDescription(),
            'template' => $generator->getTemplate(),
//            'templatePath' => $generator->getTemplatePath(),
            'templates' => $generator->getTemplates(),
            'directory' => $generator->getDirectory(),
        ];

        return $this->responseFactory->createResponse($params);
    }

    public function generate(GeneratorRequest $request): ResponseInterface
    {
        /** @var AbstractGenerator $generator */
        $generator = $request->getGenerator();
        $answers = $request->getAnswers();
        $validationResult = $generator->validate();
        if ($validationResult->isValid()) {
            $generator->saveStickyAttributes();
            $files = $generator->generate();
            $params = [];
            $results = [];
            $params['hasError'] = !$generator->save($files, (array)$answers, $results);
            $params['results'] = $results;
            return $this->responseFactory->createResponse($params);
        }

        return $this->responseFactory->createResponse(
            ['errors' => $validationResult->getErrorMessagesIndexedByAttribute()],
            Status::UNPROCESSABLE_ENTITY
        );
    }

    public function preview(GeneratorRequest $request, #[Query('file')] ?string $file = null): ResponseInterface
    {
        /** @var AbstractGenerator $generator */
        $generator = $request->getGenerator();
        $validationResult = $generator->validate();
        if (!$validationResult->isValid()) {
            return $this->responseFactory->createResponse(
                ['errors' => $validationResult->getErrorMessagesIndexedByAttribute()],
                Status::UNPROCESSABLE_ENTITY
            );
        }

        $files = $generator->generate();
        if ($file !== null) {
            foreach ($files as $generatedFile) {
                if ($generatedFile->getId() === $file) {
                    $content = $generatedFile->preview();
                    return $this->responseFactory->createResponse(
                        ['content' => $content ?: 'Preview is not available for this file type.']
                    );
                }
            }
            return $this->responseFactory->createResponse(
                ['message' => "Code file not found: $file"],
                Status::UNPROCESSABLE_ENTITY
            );
        }
        return $this->responseFactory->createResponse(['files' => $files, 'operations' => CodeFile::OPERATIONS_MAP]);
    }

    public function diff(GeneratorRequest $request, #[Query('file')] string $file): ResponseInterface
    {
        /** @var AbstractGenerator $generator */
        $generator = $request->getGenerator();
        $validationResult = $generator->validate();
        if ($validationResult->isValid()) {
            foreach ($generator->generate() as $generatedFile) {
                if ($generatedFile->getId() === $file) {
                    return $this->responseFactory->createResponse(['diff' => $generatedFile->diff()]);
                }
            }
            return $this->responseFactory->createResponse(
                ['message' => "Code file not found: $file"],
                Status::UNPROCESSABLE_ENTITY
            );
        }

        return $this->responseFactory->createResponse(
            ['errors' => $validationResult->getErrorMessagesIndexedByAttribute()],
            Status::UNPROCESSABLE_ENTITY
        );
    }
}
