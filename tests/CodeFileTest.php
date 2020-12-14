<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Tests;

use Yiisoft\Aliases\Aliases;
use Yiisoft\Yii\Gii\CodeFile;

class CodeFileTest extends TestCase
{
    private ?Aliases $aliases = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->aliases = $this->getContainer()->get(Aliases::class);
    }

    public function dataProviderDiff()
    {
        return [
            [
                '@app/Controllers/EmptyController.php',
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
                '@app/Controllers/NonExistController.php',
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
                '@app/Controllers/NonExistController.php',
                '',
                '',
            ],
            [
                '@app/Controllers/image.png',
                '',
                false,
            ],
        ];
    }

    public function dataProviderPreview()
    {
        return [
            [
                '@app/Controllers/EmptyController.php',
                '',
                highlight_string('', true),
            ],
            [
                '@app/Controllers/image.png',
                '',
                false,
            ],
            [
                '@app/Controllers/file',
                '',
                '',
            ],
        ];
    }

    public function dataProviderConstruct()
    {
        return [
            [
                '@app/Controllers/EmptyController.php',
                CodeFile::OP_OVERWRITE,
            ],
            [
                '@app/Controllers/NonExistController.php',
                CodeFile::OP_CREATE,
            ],
            [
                '@app/runtime',
                CodeFile::OP_CREATE,
            ],
        ];
    }

    /** @dataProvider dataProviderConstruct */
    public function testConstruct(string $path, int $expectedOperation)
    {
        $codeFile = new CodeFile($this->aliases->get($path), '');
        $this->assertEquals($codeFile->getOperation(), $expectedOperation);
    }

    public function testConstructWithSameContent()
    {
        $path = $this->aliases->get('@app/Controllers/EmptyController.php');
        $codeFile = new CodeFile(
            $path,
            file_get_contents($path)
        );
        $this->assertEquals($codeFile->getOperation(), $codeFile::OP_SKIP);
    }

    /** @dataProvider dataProviderDiff */
    public function testDiff(string $path, string $content, $result)
    {
        $codeFile = new CodeFile($this->aliases->get($path), $content);
        $this->assertEquals($codeFile->diff(), $result);
    }

    public function testDiffSameContent()
    {
        $path = $this->aliases->get('@app/Controllers/EmptyController.php');
        $codeFile = new CodeFile(
            $path,
            file_get_contents($path)
        );
        $this->assertEquals($codeFile->diff(), '');
    }

    /** @dataProvider dataProviderPreview */
    public function testPreview(string $path, string $content, $result)
    {
        $codeFile = new CodeFile($this->aliases->get($path), $content);
        $this->assertEquals($codeFile->preview(), $result);
    }

    public function testSave()
    {
        $dest = $this->aliases->get('@app/runtime/EmptyController.php');
        copy(
            $this->aliases->get('@app/Controllers/EmptyController.php'),
            $dest,
        );
        $codeFile = new CodeFile($dest, '');

        $this->assertEquals($codeFile->save(), true);
        $this->assertFileExists($dest);
    }

    public function testSaveWithNonExistentFile()
    {
        $file = $this->aliases->get('@app/runtime/nonExistentFile.php');
        $codeFile = new CodeFile($file, '');

        $this->assertEquals($codeFile->save(), true);
        $this->assertFileExists($file);
    }

    public function testSaveWithNonExistentDirectory()
    {
        $codeFile = new CodeFile($this->aliases->get('@app/runtime/unknown/nonExistentFile.php'), '');
        $this->assertEquals($codeFile->save(), true);
    }

    public function testPath()
    {
        $file = $this->aliases->get('@app/runtime');
        $codeFile = new CodeFile($file, '');

        $this->assertEquals($codeFile->getPath(), realpath($file));
    }

    public function testRelativePath()
    {
        $app = $this->aliases->get('@app');
        $codeFile = (new CodeFile($app . DIRECTORY_SEPARATOR . 'runtime', ''))->withBasePath($app);

        $this->assertEquals($codeFile->getRelativePath(), 'runtime');
    }

    public function testRelativePathWithEmptyBasePath()
    {
        $file = $this->aliases->get('@app/runtime');
        $codeFile = new CodeFile($file, '');

        $this->assertEquals($codeFile->getRelativePath(), realpath($file));
    }
}
