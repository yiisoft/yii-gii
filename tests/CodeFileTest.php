<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Tests;

use Yiisoft\Aliases\Aliases;
use Yiisoft\Yii\Gii\Component\CodeFile\CodeFile;
use Yiisoft\Yii\Gii\Component\CodeFile\CodeFileWriteOperationEnum;
use Yiisoft\Yii\Gii\Component\CodeFile\CodeFileWriteStatusEnum;

class CodeFileTest extends TestCase
{
    private ?Aliases $aliases = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->aliases = $this->getContainer()->get(Aliases::class);
    }

    public function dataProviderDiff(): array
    {
        return [
            [
                '@src/Controllers/EmptyController.php',
                <<<PHP
                 <?php

                 declare(strict_types=1);

                 namespace Yiisoft\Yii\Gii\Tests\Controllers;

                 class EmptyController
                 {
                    public function customMethod() {

                    }
                 }
                 PHP,
                <<<HTML
                <table class="Differences DifferencesInline">
                    <thead>
                        <tr>
                            <th>Old</th>
                            <th>New</th>
                            <th>Differences</th>
                        </tr>
                    </thead>    <tbody class="ChangeEqual">        <tr>
                            <th data-line-number="6"></th>
                            <th data-line-number="6"></th>
                            <td class="Left"></td>
                        </tr>        <tr>
                            <th data-line-number="7"></th>
                            <th data-line-number="7"></th>
                            <td class="Left">class EmptyController</td>
                        </tr>        <tr>
                            <th data-line-number="8"></th>
                            <th data-line-number="8"></th>
                            <td class="Left">{</td>
                        </tr>    </tbody>    <tbody class="ChangeInsert">        <tr>
                            <th data-line-number="&nbsp;"></th>
                            <th data-line-number="9"></th>
                            <td class="Right"><ins>&nbsp; public function customMethod() {</ins>&nbsp;</td>
                        </tr>        <tr>
                            <th data-line-number="&nbsp;"></th>
                            <th data-line-number="10"></th>
                            <td class="Right"><ins></ins>&nbsp;</td>
                        </tr>        <tr>
                            <th data-line-number="&nbsp;"></th>
                            <th data-line-number="11"></th>
                            <td class="Right"><ins>&nbsp; }</ins>&nbsp;</td>
                        </tr>    </tbody>    <tbody class="ChangeEqual">        <tr>
                            <th data-line-number="9"></th>
                            <th data-line-number="12"></th>
                            <td class="Left">}</td>
                        </tr>    </tbody></table>
                HTML,
            ],
            [
                '',
                <<<PHP
                 <?php

                 declare(strict_types=1);

                 namespace Yiisoft\Yii\Gii\Tests\Controllers;

                 class EmptyController
                 {
                    public function customMethod() {

                    }
                 }
                 PHP,
                '',
            ],
            [
                '',
                '',
                '',
            ],
            [
                '@src/Controllers/NonExistController.php',
                <<<PHP
                 <?php

                 declare(strict_types=1);

                 namespace Yiisoft\Yii\Gii\Tests\Controllers;

                 class EmptyController
                 {
                    public function customMethod() {

                    }
                 }
                 PHP,
                '',
            ],
            [
                '@src/Controllers/NonExistController.php',
                '',
                '',
            ],
            [
                '@src/Controllers/image.png',
                '',
                false,
            ],
        ];
    }

    public function dataProviderPreview(): array
    {
        return [
            [
                '@src/Controllers/EmptyController.php',
                '',
                highlight_string('', true),
            ],
            [
                '@src/Controllers/image.png',
                '',
                false,
            ],
            [
                '@src/Controllers/file',
                '',
                '',
            ],
        ];
    }

    public function dataProviderConstruct(): array
    {
        return [
            [
                '@src/Controllers/EmptyController.php',
                CodeFileWriteOperationEnum::SAVE,
            ],
            [
                '@src/Controllers/NonExistController.php',
                CodeFileWriteOperationEnum::SAVE,
            ],
            [
                '@src/runtime',
                CodeFileWriteOperationEnum::SAVE,
            ],
        ];
    }

    /** @dataProvider dataProviderConstruct */
    public function testConstruct(string $path, CodeFileWriteOperationEnum $expectedOperation): void
    {
        $codeFile = new CodeFile($this->aliases->get($path), '');
        $this->assertEquals($codeFile->getOperation(), $expectedOperation);
    }

    public function testConstructWithSameContent(): void
    {
        $path = $this->aliases->get('@src/Controllers/EmptyController.php');
        $codeFile = new CodeFile(
            $path,
            file_get_contents($path)
        );
        $this->assertEquals($codeFile->getOperation(), CodeFileWriteOperationEnum::SKIP);
    }

    // TODO: test \Yiisoft\Yii\Gii\Component\DiffRendererHtmlInline instead
//    /** @dataProvider dataProviderDiff */
//    public function testDiff(string $path, string $content, $result): void
//    {
//        $codeFile = new CodeFile($this->aliases->get($path), $content);
//        $this->assertEquals($codeFile->diff(), $result);
//    }

    public function testDiffSameContent(): void
    {
        $path = $this->aliases->get('@src/Controllers/EmptyController.php');
        $codeFile = new CodeFile(
            $path,
            file_get_contents($path)
        );
        $this->assertEquals($codeFile->diff(), '');
    }

    /** @dataProvider dataProviderPreview */
    public function testPreview(string $path, string $content, $result): void
    {
        $codeFile = new CodeFile($this->aliases->get($path), $content);
        $this->assertEquals($codeFile->preview(), $result);
    }

    public function testSave(): void
    {
        $dest = $this->aliases->get('@src/runtime/EmptyController.php');
        copy(
            $this->aliases->get('@src/Controllers/EmptyController.php'),
            $dest,
        );
        $codeFile = new CodeFile($dest, '');

        $this->assertEquals($codeFile->save(), CodeFileWriteStatusEnum::OVERWROTE);
        $this->assertFileExists($dest);
    }

    public function testSaveWithNonExistentFile(): void
    {
        $file = $this->aliases->get('@src/runtime/nonExistentFile.php');
        $codeFile = new CodeFile($file, '');

        $this->assertEquals($codeFile->save(), CodeFileWriteStatusEnum::CREATED);
        $this->assertFileExists($file);
    }

    public function testSaveWithNonExistentDirectory(): void
    {
        $codeFile = new CodeFile($this->aliases->get('@src/runtime/unknown/nonExistentFile.php'), '');
        $this->assertEquals($codeFile->save(), CodeFileWriteStatusEnum::CREATED);
    }

    public function testPath(): void
    {
        $file = $this->aliases->get('@src/runtime');
        $codeFile = new CodeFile($file, '');

        $this->assertEquals($codeFile->getPath(), realpath($file));
    }

    public function testRelativePath(): void
    {
        $app = $this->aliases->get('@src');
        $codeFile = (new CodeFile($app . DIRECTORY_SEPARATOR . 'runtime', ''))->withBasePath($app);

        $this->assertEquals($codeFile->getRelativePath(), 'runtime');
    }

    public function testRelativePathWithEmptyBasePath(): void
    {
        $file = $this->aliases->get('@src/runtime');
        $codeFile = new CodeFile($file, '');

        $this->assertEquals($codeFile->getRelativePath(), realpath($file));
    }
}
