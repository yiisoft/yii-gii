<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Controller;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Http\Status;
use Yiisoft\RequestModel\Attribute\Query;
use Yiisoft\Yii\Gii\CodeFile;
use Yiisoft\Yii\Gii\CodeFileSaver;
use Yiisoft\Yii\Gii\Exception\InvalidGeneratorCommandException;
use Yiisoft\Yii\Gii\Generator\AbstractGenerator;
use Yiisoft\Yii\Gii\GeneratorInterface;
use Yiisoft\Yii\Gii\Request\GeneratorRequest;

final class DefaultController
{
    public function __construct(private DataResponseFactoryInterface $responseFactory)
    {
    }

    public function get(GeneratorRequest $request): ResponseInterface
    {
        $generator = $request->getGenerator();
        $params = [
            'name' => $generator::getName(),
            'description' => $generator::getDescription(),
            'commandClass' => $generator::getCommandClass(),
            //            'templatePath' => $generator->getTemplatePath(),
            //            'templates' => $generator->getTemplates(),
            'directory' => $generator->getDirectory(),
        ];

        return $this->responseFactory->createResponse($params);
    }

    public function generate(GeneratorRequest $request, CodeFileSaver $codeFileSaver): ResponseInterface
    {
        /** @var GeneratorInterface $generator */
        $generator = $request->getGenerator();
        $command = new ($generator::getCommandClass())();
        $answers = $request->getAnswers();
        try {
            $files = $generator->generate($command);
        } catch (InvalidGeneratorCommandException $e) {
            return $this->createErrorResponse($e);
        }
        $params = [];
        $results = [];
        $params['hasError'] = !$codeFileSaver->save($command, $files, (array) $answers, $results);
        $params['results'] = $results;
        return $this->responseFactory->createResponse($params);
    }

    public function preview(GeneratorRequest $request, #[Query('file')] ?string $file = null): ResponseInterface
    {
        /** @var GeneratorInterface $generator */
        $generator = $request->getGenerator();
        $command = new ($generator::getCommandClass())();
        try {
            $files = $generator->generate($command);
        } catch (InvalidGeneratorCommandException $e) {
            return $this->createErrorResponse($e);
        }
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
        /** @var GeneratorInterface $generator */
        $generator = $request->getGenerator();
        $command = new ($generator::getCommandClass())();
        try {
            $files = $generator->generate($command);
        } catch (InvalidGeneratorCommandException $e) {
            return $this->createErrorResponse($e);
        }

        foreach ($files as $generatedFile) {
            if ($generatedFile->getId() === $file) {
                return $this->responseFactory->createResponse(['diff' => $generatedFile->diff()]);
            }
        }
        return $this->responseFactory->createResponse(
            ['message' => "Code file not found: $file"],
            Status::UNPROCESSABLE_ENTITY
        );
    }

    private function createErrorResponse(InvalidGeneratorCommandException $e): DataResponse
    {
        return $this->responseFactory->createResponse(
        // TODO: fix
            ['errors' => $e->getResult()->getErrorMessagesIndexedByAttribute()],
            Status::UNPROCESSABLE_ENTITY
        );
    }
}
