<?php

/***********************************************************************************************************************
 *
 * Copyright 2022 Evgeniy Averyanov (root@evgeniy-webmaster.pro)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit
 * persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO
 * THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS
 * OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 **********************************************************************************************************************/

namespace EvgeniyWebmaster\SimpleDOM;

class HtmlDocument
{
    private string $html;

    private ?string $doctype;

    public array $content = [];

    public function __construct()
    {
    }

    public function parseUrl(string $url): void
    {
        $this->parseHtml(file_get_contents($url));
    }

    public function parseHtml(string $html): void
    {
        $this->html = $html;
        $this->parseDoctype();
        $this->parse();
    }

    private function parseDoctype(): void
    {
        $docTypeRE = '/\<\!doctype(.*?)\>/i';
        if (preg_match($docTypeRE, $this->html, $ms)) {
            $this->doctype = trim($ms[1]);
            $this->html = preg_replace($docTypeRE, '', $this->html);
        }
    }

    private int $cursor = 0;
    private array $stack = [];

    private const TAG_OPEN = 'open';
    private const TAG_CLOSE = 'close';

    private function parse(): void
    {
        while (true) {

            $startOpenTag = strpos($this->html, '<', $this->cursor);

            if ($startOpenTag === false) {
                break;
            }

            $text = trim(substr($this->html, $this->cursor, $startOpenTag - $this->cursor));
            if ($text !== '') {
                $this->stack[] = new Text($text);
            }

            if (substr($this->html, $startOpenTag + 1, 3) === '!--') {
                $closeComment = strpos($this->html, '-->', $startOpenTag);
                $commentText = trim(substr($this->html, $startOpenTag + 4, $closeComment - ($startOpenTag + 4)));
                $this->stack[] = new Comment($commentText);
                $this->cursor = $closeComment + 3;
            } else {
                $startCloseTag = strpos($this->html, '>', $startOpenTag);
                $openTagStr = substr($this->html, $startOpenTag + 1, $startCloseTag - $startOpenTag - 1);
                $tagState = $this->parseTag($openTagStr);

                if ($tagState->state === self::TAG_OPEN) {
                    $tag = new Tag($tagState->name, $tagState->attributes);
                    $this->stack[] = $tag;
                }

                if ($tagState->state === self::TAG_CLOSE) {
                    $tagContent = [];

                    while (count($this->stack)) {
                        $last = $this->stack[count($this->stack) - 1];
                        if ($last instanceof Tag && $last->name === $tagState->name && $last->content === null) {
                            $last->content = $tagContent;
                            $tagContent = [];
                            break;
                        }
                        $last = array_pop($this->stack);
                        if ($last instanceof Tag && $last->content === null) {
                            $last->content = [];
                        }
                        array_unshift($tagContent, $last);
                    }

                }

                $this->cursor = $startCloseTag + 1;
            }

        }
        $this->content = $this->stack;
    }

    private function parseTag(string $str): object
    {
        $state = self::TAG_OPEN;
        if ($str[0] === '/') {
            $state = self::TAG_CLOSE;
        }

        preg_match_all('/( \w+ | (\s\S+) )/x', $str, $ms);

        $tagName = $ms[0][0];

        $attrs = [];

        for ($i = 1; $i < count($ms[0]); $i++) {
            $attr = trim($ms[0][$i]);
            if (preg_match('/([\w\-\_]+)="(.*?)"/', $attr, $ms1)) {
                $attrs[$ms1[1]] = $ms1[2];
            } else {
                $attrs[$attr] = $attr;
            }
        }

        return (object)[
            'state' => $state,
            'name' => $tagName,
            'attributes' => $attrs,
        ];
    }

    public function __toString(): string
    {
        $content = [];
        foreach ($this->content as $item) {
            $content[] .= (string)$item;
        }
        $content = implode("\n", $content);

        return "<!doctype $this->doctype>\n$content";
    }
}