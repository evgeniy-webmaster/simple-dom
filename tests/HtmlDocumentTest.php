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

use PHPUnit\Framework\TestCase;

use EvgeniyWebmaster\SimpleDOM\HtmlDocument;

class HtmlDocumentTest extends TestCase
{
    private string $html = <<<END
<!doctype html>
adlghsdfhgkl
<html class="no-js" lang="" disabled>

<head>
  <meta charset="utf-8">
  <title>Hello HTML</title>
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <meta property="og:title" content="">
  <meta property="og:type" content="">
  <meta property="og:url" content="">
  <meta property="og:image" content="">

  <link rel="icon" href="/favicon.ico" sizes="any">
  <link rel="icon" href="/icon.svg" type="image/svg+xml">
  <link rel="apple-touch-icon" href="icon.png">

  <link rel="stylesheet" href="css/normalize.css">
  <link rel="stylesheet" href="css/style.css">

  <link rel="manifest" href="site.webmanifest">
  <meta name="theme-color" content="#fafafa">
</head>

<body>

  <!-- Add your site or application content here -->
  <p>Hello world! This is HTML5 Boilerplate.</p>
  <script src="js/vendor/modernizr-{{MODERNIZR_VERSION}}.min.js"></script>
  <script src="js/app.js"></script>

</body>

</html>
END;


    public function testParse() {
        $htmlDoc = new HtmlDocument();
        $htmlDoc->parseHtml($this->html);
        $title = $htmlDoc->content[1]->content[0]->content[1]->content[0]->text;
        $this->assertEquals('Hello HTML', $title);
    }

    public function testToString() {
        $htmlDoc = new HtmlDocument();
        $htmlDoc->parseHtml($this->html);
        $str = (string)$htmlDoc;
        $this->assertStringContainsString('<head>', $str);
    }

    public function testParseUrl() {
        $htmlDoc = new HtmlDocument();
        $htmlDoc->parseUrl('https://www.php.net/');
        $title = $htmlDoc->content[0]->content[0]->content[2]->content[0]->text;
        $this->assertIsString($title, 'PHP: Hypertext Preprocessor');
    }
}