<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Tests;

use Yiisoft\Aliases\Aliases;
use Yiisoft\Yii\Gii\CodeFile;

class CodeFileTest extends GiiTestCase
{
    public function dataProviderDiff() {
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
                    <th data-line-number="1"></th>
                    <th data-line-number="1"></th>
                    <td class="Left">&lt;?php</td>
                </tr>    </tbody>    <tbody class="ChangeReplace">        <tr>
                    <th data-line-number="2"></th>
                    <th data-line-number="&nbsp;"></th>
                    <td class="Left"><span></span></td>
                </tr>        <tr>
                    <th data-line-number="2"></th>
                    <th data-line-number="&nbsp;"></th>
                    <td class="Right"><span>&nbsp; </span></td>
                </tr>    </tbody>    <tbody class="ChangeEqual">        <tr>
                    <th data-line-number="3"></th>
                    <th data-line-number="3"></th>
                    <td class="Left">declare(strict_types=1);</td>
                </tr>    </tbody>    <tbody class="ChangeReplace">        <tr>
                    <th data-line-number="4"></th>
                    <th data-line-number="&nbsp;"></th>
                    <td class="Left"><span></span></td>
                </tr>        <tr>
                    <th data-line-number="4"></th>
                    <th data-line-number="&nbsp;"></th>
                    <td class="Right"><span>&nbsp; </span></td>
                </tr>    </tbody>    <tbody class="ChangeEqual">        <tr>
                    <th data-line-number="5"></th>
                    <th data-line-number="5"></th>
                    <td class="Left">namespace Yiisoft\Yii\Gii\Tests\Controllers;</td>
                </tr>    </tbody>    <tbody class="ChangeReplace">        <tr>
                    <th data-line-number="6"></th>
                    <th data-line-number="&nbsp;"></th>
                    <td class="Left"><span></span></td>
                </tr>        <tr>
                    <th data-line-number="6"></th>
                    <th data-line-number="&nbsp;"></th>
                    <td class="Right"><span>&nbsp; </span></td>
                </tr>    </tbody>    <tbody class="ChangeEqual">        <tr>
                    <th data-line-number="7"></th>
                    <th data-line-number="7"></th>
                    <td class="Left">class EmptyController</td>
                </tr>        <tr>
                    <th data-line-number="8"></th>
                    <th data-line-number="8"></th>
                    <td class="Left">{</td>
                </tr>    </tbody>    <tbody class="ChangeReplace">        <tr>
                    <th data-line-number="9"></th>
                    <th data-line-number="&nbsp;"></th>
                    <td class="Left"><span></span></td>
                </tr>        <tr>
                    <th data-line-number="9"></th>
                    <th data-line-number="&nbsp;"></th>
                    <td class="Right"><span>&nbsp; public function customMethod() {</span></td>
                </tr>        <tr>
                    <th data-line-number="10"></th>
                    <th data-line-number="&nbsp;"></th>
                    <td class="Right"><span>&nbsp; </span></td>
                </tr>        <tr>
                    <th data-line-number="11"></th>
                    <th data-line-number="&nbsp;"></th>
                    <td class="Right"><span>&nbsp; }</span></td>
                </tr>    </tbody>    <tbody class="ChangeEqual">        <tr>
                    <th data-line-number="10"></th>
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
                ''
            ],
            [
                '',
                '',
                ''
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
                ''
            ],
            [
                '@app/Controllers/NonExistController.php',
                '',
                ''
            ],
            [
                '@app/Controllers/image.png',
                '',
                false
            ]
        ];
    }

    public function dataProviderPreview() {
        return [
            [
                '@app/Controllers/EmptyController.php',
                '',
                <<<HTML
                <code><span style="color: #000000">
                </span>
                </code>
                HTML
            ],
            [
                '@app/Controllers/image.png',
                '',
                false
            ],
            [
                '@app/Controllers/file',
                '',
                ''
            ]
        ];
    }

    public function dataProviderConstruct() {
        return [
            [
                '@app/Controllers/EmptyController.php',
                CodeFile::OP_OVERWRITE
            ],
            [
                '@app/Controllers/NonExistController.php',
                CodeFile::OP_CREATE
            ],
            [
                '@app/runtime',
                CodeFile::OP_CREATE
            ],
        ];
    }

    /** @dataProvider dataProviderConstruct */
    public function testConstruct(string $path, int $expectedOperation) {
        $codeFile = new CodeFile($this->container->get(Aliases::class)->get($path), '');
        $this->assertEquals($codeFile->getOperation(), $expectedOperation);
    }

    public function testConstructWithSameContent() {
        $path = $this->container->get(Aliases::class)->get('@app/Controllers/EmptyController.php');
        $codeFile = new CodeFile(
            $path,
            file_get_contents($path)
        );
        $this->assertEquals($codeFile->getOperation(), $codeFile::OP_SKIP);
    }

    /** @dataProvider dataProviderDiff */
    public function testDiff(string $path, string $content, $result) {
        $codeFile = new CodeFile($this->container->get(Aliases::class)->get($path), $content);
        $this->assertEquals($codeFile->diff(), $result);
    }

    public function testDiffSameContent() {
        $path = $this->container->get(Aliases::class)->get('@app/Controllers/EmptyController.php');
        $codeFile = new CodeFile(
            $path,
            file_get_contents($path)
        );
        $this->assertEquals($codeFile->diff(), '');
    }

    /** @dataProvider dataProviderPreview */
    public function testPreview(string $path, string $content, $result) {
        $codeFile = new CodeFile($this->container->get(Aliases::class)->get($path), $content);
        $this->assertEquals($codeFile->preview(), $result);
    }

    public function testSave() {
        $dest = $this->container->get(Aliases::class)->get('@app/runtime/EmptyController.php');
        copy(
            $this->container->get(Aliases::class)->get('@app/Controllers/EmptyController.php'),
            $dest,
        );
        $codeFile = new CodeFile($dest, '');

        $this->assertEquals($codeFile->save(), true);
        $this->assertTrue(file_exists($dest));
    }

    public function testSaveWithNonExistentFile() {
        $file = $this->container->get(Aliases::class)->get('@app/runtime/nonExistentFile.php');
        $codeFile = new CodeFile($file, '');

        $this->assertEquals($codeFile->save(), true);
        $this->assertTrue(file_exists($file));
    }

    public function testSaveWithNonExistentDirectory() {
        $codeFile = new CodeFile($this->container->get(Aliases::class)->get('@app/runtime/unknown/nonExistentFile.php'), '');
        $this->assertEquals($codeFile->save(), true);
    }

    public function testPath() {
        $file = $this->container->get(Aliases::class)->get('@app/runtime');
        $codeFile = new CodeFile($file, '');

        $this->assertEquals($codeFile->getPath(), $file);
    }

    public function testRelativePath() {
        $app = $this->container->get(Aliases::class)->get('@app');
        $codeFile = (new CodeFile($app . DIRECTORY_SEPARATOR . 'runtime', ''))->withBasePath($app);

        $this->assertEquals($codeFile->getRelativePath(), 'runtime');
    }

    public function testRelativePathWithEmptyBasePath() {
        $file = $this->container->get(Aliases::class)->get('@app/runtime');
        $codeFile = new CodeFile($file, '');

        $this->assertEquals($codeFile->getRelativePath(), $file);
    }
}
