<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Controller;

use Psr\Http\Message\ResponseInterface;
use ReflectionClass;
use ReflectionParameter;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Http\Status;
use Yiisoft\RequestModel\Attribute\Query;
use Yiisoft\Validator\RulesDumper;
use Yiisoft\Validator\RulesProvider\AttributesRulesProvider;
use Yiisoft\Yii\Gii\Component\CodeFile\CodeFile;
use Yiisoft\Yii\Gii\Component\CodeFile\CodeFileWriteOperationEnum;
use Yiisoft\Yii\Gii\Component\CodeFile\CodeFileWriter;
use Yiisoft\Yii\Gii\Exception\InvalidGeneratorCommandException;
use Yiisoft\Yii\Gii\Generator\CommandHydrator;
use Yiisoft\Yii\Gii\GeneratorCommandInterface;
use Yiisoft\Yii\Gii\GeneratorInterface;
use Yiisoft\Yii\Gii\GiiInterface;
use Yiisoft\Yii\Gii\Request\GeneratorRequest;

final class DefaultController
{
    public function __construct(private DataResponseFactoryInterface $responseFactory)
    {
    }

    public function list(GiiInterface $gii): ResponseInterface
    {
        $generators = $gii->getGenerators();

        return $this->responseFactory->createResponse([
            'generators' => array_map($this->serializeGenerator(...), array_values($generators)),
        ]);
    }

    private function serializeGenerator(GeneratorInterface $generator): array
    {
        /**
         * @psalm-var $commandClass class-string<GeneratorCommandInterface>
         */
        $commandClass = $generator::getCommandClass();

        $dataset = new AttributesRulesProvider($commandClass);
        $rules = $dataset->getRules();
        $dumpedRules = (new RulesDumper())->asArray($rules);
        $attributes = $commandClass::getAttributes();
        $hints = $commandClass::getHints();
        $labels = $commandClass::getAttributeLabels();

        $reflection = new ReflectionClass($commandClass);
        $constructorParameters = $reflection->getConstructor()->getParameters();

        $attributesResult = [];
        foreach ($attributes as $attributeName) {
            $reflectionProperty = $reflection->getProperty($attributeName);
            $defaultValue = $reflectionProperty->hasDefaultValue()
                ? $reflectionProperty->getDefaultValue()
                : $this->findReflectionParameter($attributeName, $constructorParameters)?->getDefaultValue();
            $attributesResult[$attributeName] = [
                'defaultValue' => $defaultValue,
                'hint' => $hints[$attributeName] ?? null,
                'label' => $labels[$attributeName] ?? null,
                'rules' => $dumpedRules[$attributeName] ?? [],
            ];
        }

        return [
            'id' => $generator::getId(),
            'name' => $generator::getName(),
            'description' => $generator::getDescription(),
            'commandClass' => $commandClass,
            'attributes' => $attributesResult,
            //            'templatePath' => $generator->getTemplatePath(),
            //            'templates' => $generator->getTemplates(),
            'directory' => $generator->getDirectory(),
        ];
    }

    public function get(GeneratorRequest $request): ResponseInterface
    {
        $generator = $request->getGenerator();

        return $this->responseFactory->createResponse(
            $this->serializeGenerator($generator)
        );
    }

    /**
     * @param ReflectionParameter[] $constructorParameters
     */
    private function findReflectionParameter(string $name, array $constructorParameters): ReflectionParameter|null
    {
        foreach ($constructorParameters as $parameter) {
            if ($parameter->getName() === $name) {
                return $parameter;
            }
        }
        return null;
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
        $result = $codeFileWriter->write($files, $answers);

        return $this->responseFactory->createResponse(array_values($result->getResults()));
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
                'files' => array_map($this->serializeCodeFile(...), array_values($files)),
                'operations' => CodeFileWriteOperationEnum::getLabels(),
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
            'operation' => $file->getOperation()->value,
            'state' => $file->getState()->value,
            'path' => $file->getPath(),
            'relativePath' => $file->getRelativePath(),
            'type' => $file->getType(),
            'preview' => $file->preview(),
        ];
    }
}
