<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Component;

use Diff_Renderer_Html_Array;

/**
 * Renders diff to HTML. Output adjusted to be copy-paste friendly.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class DiffRendererHtmlInline extends Diff_Renderer_Html_Array
{
    /**
     * Render a and return diff with changes between the two sequences
     * displayed inline (under each other)
     *
     * @psalm-suppress ImplementedReturnTypeMismatch
     *
     * @return string The generated inline diff.
     */
    #[\Override]
    public function render(): string
    {
        $changes = parent::render();
        $html = '';
        if (empty($changes)) {
            return $html;
        }

        $html .= <<<HTML
<table class="Differences DifferencesInline">
    <thead>
        <tr>
            <th>Old</th>
            <th>New</th>
            <th>Differences</th>
        </tr>
    </thead>
HTML;
        /**
         * @var array $blocks
         */
        foreach ($changes as $i => $blocks) {
            // If this is a separate block, we're condensing code so output ...,
            // indicating a significant portion of the code has been collapsed as
            // it is the same
            if ($i > 0) {
                $html .= <<<HTML
    <tbody class="Skipped">
        <th data-line-number="&hellip;"></th>
        <th data-line-number="&hellip;"></th>
        <td>&nbsp;</td>
    </tbody>
HTML;
            }
            
            /**
             * @var array $change
             */
            foreach ($blocks as $change) {
                $changeTag = (string)$change['tag'] ?: '';

                $changeBase = (array)$change['base'] ?: [];
                $changeBaseOffset = !empty($changeBase['offset']) ? (int)$changeBase['offset'] : 0;
                $changeBaseLines = (array)($changeBase['lines']) ?: [];

                $changeChanged = (array)$change['changed'] ?: '';
                $changeChangedOffset = !empty($changeChanged['offset']) ? (int)$changeChanged['offset'] : 0;
                $changeChangedLines = !empty($changeChanged['lines']) ? (array)$changeChanged['lines'] : [];

                $tag = ucfirst($changeTag);
                $html .= <<<HTML
    <tbody class="Change{$tag}">
HTML;
                // Equal changes should be shown on both sides of the diff
                if ($changeTag === 'equal') {
                    /**
                     * @var int $no
                     * @var string $line
                     */
                    foreach ($changeBaseLines as $no => $line) {
                        $fromLine = $changeBaseOffset + $no + 1;
                        $toLine = $changeChangedOffset + $no + 1;
                        $html .= <<<HTML
        <tr>
            <th data-line-number="{$fromLine}"></th>
            <th data-line-number="{$toLine}"></th>
            <td class="Left">{$line}</td>
        </tr>
HTML;
                    }
                } // Added lines only on the right side
                elseif ($changeTag === 'insert') {
                    /**
                     * @var int $no
                     * @var string $line
                     */
                    foreach ($changeChangedLines as $no => $line) {
                        $toLine = $changeChangedOffset + $no + 1;
                        $html .= <<<HTML
        <tr>
            <th data-line-number="&nbsp;"></th>
            <th data-line-number="{$toLine}"></th>
            <td class="Right"><ins>{$line}</ins>&nbsp;</td>
        </tr>
HTML;
                    }
                } // Show deleted lines only on the left side
                elseif ($changeTag === 'delete') {
                    /**
                     * @var int $no
                     * @var string $line
                     */
                    foreach ($changeBaseLines as $no => $line) {
                        $fromLine = $changeBaseOffset + $no + 1;
                        $html .= <<<HTML
        <tr>
            <th data-line-number="{$fromLine}"></th>
            <th data-line-number="&nbsp;"></th>
            <td class="Left"><del>{$line}</del>&nbsp;</td>
        </tr>
HTML;
                    }
                } // Show modified lines on both sides
                elseif ($changeTag === 'replace') {
                    /**
                     * @var int $no
                     * @var string $line
                     */
                    foreach ($changeBaseLines as $no => $line) {
                        $fromLine = $changeBaseOffset + $no + 1;
                        $html .= <<<HTML
        <tr>
            <th data-line-number="{$fromLine}"></th>
            <th data-line-number="&nbsp;"></th>
            <td class="Left"><span>{$line}</span></td>
        </tr>
HTML;
                    }
                    /**
                     * @var int $no
                     * @var string $line
                     */
                    foreach ($changeChangedLines as $no => $line) {
                        $toLine = $changeChangedOffset + $no + 1;
                        $html .= <<<HTML
        <tr>
            <th data-line-number="{$toLine}"></th>
            <th data-line-number="&nbsp;"></th>
            <td class="Right"><span>{$line}</span></td>
        </tr>
HTML;
                    }
                }
                $html .= <<<HTML
    </tbody>
HTML;
            }
        }
        $html .= <<<HTML
</table>
HTML;

        return $html;
    }
}
