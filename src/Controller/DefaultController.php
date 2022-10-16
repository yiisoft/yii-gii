<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Controller;

use Psr\Http\Message\ResponseInterface;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Http\Status;
use Yiisoft\RequestModel\Attribute\Query;
use Yiisoft\Yii\Gii\CodeFile;
use Yiisoft\Yii\Gii\CodeFileWriter;
use Yiisoft\Yii\Gii\Exception\InvalidGeneratorCommandException;
use Yiisoft\Yii\Gii\Generator\CommandHydrator;
use Yiisoft\Yii\Gii\GeneratorCommandInterface;
use Yiisoft\Yii\Gii\Request\GeneratorRequest;

final class DefaultController
{
    public function __construct(private DataResponseFactoryInterface $responseFactory)
    {
    }

    public function get(GeneratorRequest $request): ResponseInterface
    {
        $generator = $request->getGenerator();
        /**
         * @psalm-var class-string<GeneratorCommandInterface> $commandClass
         */
        $commandClass = $generator::getCommandClass();
        $params = [
            'id' => $generator::getId(),
            'name' => $generator::getName(),
            'description' => $generator::getDescription(),
            'commandClass' => $commandClass,
            'hints' => $commandClass::getHints(),
            'attributeLabels' => $commandClass::getAttributeLabels(),
            //            'templatePath' => $generator->getTemplatePath(),
            //            'templates' => $generator->getTemplates(),
            'directory' => $generator->getDirectory(),
        ];

        return $this->responseFactory->createResponse($params);
    }

    public function generate(
        GeneratorRequest $request,
        CodeFileWriter $codeFileWriter,
        CommandHydrator $commandHydrator
    ): ResponseInterface {
        $generator = $request->getGenerator();
        $command = $commandHydrator->hydrate($generator::getCommandClass(), $request->getBody());
        $answers = $request->getAnswers();
        try {
            $files = $generator->generate($command);
        } catch (InvalidGeneratorCommandException $e) {
            return $this->createErrorResponse($e);
        }
        $params = [];
        $results = [];
        // TODO: get answers from the request
        $answers = [];
        foreach ($files as $file) {
            $answers[$file->getId()] = true;
        }
        $params['hasError'] = !$codeFileWriter->write($command, $files, $answers, $results);
        $params['results'] = $results;
        return $this->responseFactory->createResponse($params);
    }

    public function preview(
        GeneratorRequest $request,
        CommandHydrator $commandHydrator,
        #[Query('file')] ?string $file = null
    ): ResponseInterface {
        $generator = $request->getGenerator();
        $command = $commandHydrator->hydrate($generator::getCommandClass(), $request->getBody());

        try {
            $files = $generator->generate($command);
        } catch (InvalidGeneratorCommandException $e) {
            return $this->createErrorResponse($e);
        }
        if ($file === null) {
            return $this->responseFactory->createResponse([
                'files' => array_map($this->serializeCodeFile(...), $files),
                // todo: fix showing operations' keys. they are skipped because of serialization numerical arrays
                'operations' => CodeFile::OPERATIONS_MAP,
            ]);
        }

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

    public function diff(
        GeneratorRequest $request,
        CommandHydrator $commandHydrator,
        #[Query('file')] string $file
    ): ResponseInterface {
        $generator = $request->getGenerator();
        $command = $commandHydrator->hydrate($generator::getCommandClass(), $request->getBody());

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

    private function serializeCodeFile(CodeFile $file): array
    {
        return [
            'id' => $file->getId(),
            'content' => $file->getContent(),
            'operation' => $file->getOperation(),
            'path' => $file->getPath(),
            'relativePath' => $file->getRelativePath(),
            'type' => $file->getType(),
            'preview' => $file->preview(),
        ];
    }
}
