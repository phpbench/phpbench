<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Tests\Unit\Serializer;

use PhpBench\Dom\Document;
use PhpBench\Environment\Information;
use PhpBench\Model\ParameterSet;
use PhpBench\Model\Suite;
use PhpBench\Model\SuiteCollection;
use PhpBench\Serializer\XmlDecoder;
use PhpBench\Serializer\XmlEncoder;
use PhpBench\Tests\Util\Approval;
use PhpBench\Tests\Util\SuiteBuilder;

class XmlDecoderTest extends XmlTestCase
{
    /**
     * It should encode the suite to an XML document.
     *
     * @dataProvider provideEncode
     */
    public function testDecoder(string $path): void
    {
        $approval = Approval::create($path, 2);
        $params = $approval->getConfig(0);

        $collection = $this->getSuiteCollection($params);
        $dom = $this->encode($collection);

        $decoder = new XmlDecoder();
        $collection = $decoder->decode($dom);

        $decodedDom = $this->encode($collection);

        $this->assertEquals(
            $dom->dump(),
            $decodedDom->dump()
        );
    }

    /**
     * It should throw an exception when encountering a non-existing result class.
     *
     */
    public function testDecodeUnknownResultClass(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('XML file defines a non-existing result class "FooVendor\FooResult" - maybe you are missing an extension?');
        $dom = new Document(1.0);
        $dom->loadXML(
            <<<EOT
<phpbench>
  <suite>
    <result key="foo" class="FooVendor\FooResult"/>
  </suite>
</phpbench>
EOT
        );
        $decoder = new XmlDecoder();
        $collection = $decoder->decode($dom);
    }

    /**
     * It should throw an exception for a non-existing result key.
     *
     */
    public function testDecodeUnknownResultKey(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No result class was provided with key "foobar" for attribute "foobar-foo"');
        $dom = new Document(1.0);
        $dom->loadXML(
            <<<EOT
<phpbench>
  <suite>
      <benchmark class="\PhpBench\Micro\Math\KdeBench">
      <subject name="benchKde">
        <variant>
          <iteration foobar-foo="12" />
        </variant>
      </subject>
      </benchmark>
  </suite>
</phpbench>
EOT
        );
        $decoder = new XmlDecoder();
        $collection = $decoder->decode($dom);
    }

    /**
     * It should throw an exception if an attribute name has no - prefix.
     *
     */
    public function testInvalidAttribute(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Expected attribute name to have a result key prefix, got "foo"');
        $dom = new Document(1.0);
        $dom->loadXML(
            <<<EOT
<phpbench>
  <suite>
      <benchmark class="\PhpBench\Micro\Math\KdeBench">
      <subject name="benchKde">
        <variant>
          <iteration foo="12" />
        </variant>
      </subject>
      </benchmark>
  </suite>
</phpbench>
EOT
        );
        $decoder = new XmlDecoder();
        $collection = $decoder->decode($dom);
    }

    public function testDecodeBoolValue(): void
    {
        $dom = new Document(1.0);
        $dom->loadXML(
            <<<EOT
<phpbench>
  <suite>
    <env>
      <info1>
        <value name="foo" type="boolean">1</value>
      </info1>
    </env>
  </suite>
</phpbench>
EOT
        );
        $decoder = new XmlDecoder();
        $collection = $decoder->decode($dom);
        $suite = $collection->getSuites()[0];
        assert($suite instanceof Suite);
        $envs = $suite->getEnvInformations();
        self::assertCount(1, $envs);
        $env = reset($envs);
        self::assertInstanceOf(Information::class, $env);
        assert($env instanceof Information);
        self::assertIsBool($env->offsetGet('foo'));
    }

    public function testDecodeLegacyEnv(): void
    {
        $dom = new Document(1.0);
        $dom->loadXML(
            <<<EOT
<phpbench>
  <suite>
    <env>
<uname os="Linux" host="x1-debian" release="5.4.0-4-amd64" version="#1 SMP Debian 5.4.19-1 (2020-02-13)" machine="x86_64"/><php xdebug="1" version="7.3.15-3" ini="/etc/php/7.3/cli/php.ini" extensions="Core, date, libxml, openssl, pcre, zlib, filter, hash, pcntl, Reflection, SPL, session, sodium, standard, mysqlnd, PDO, xml, apcu, bcmath, bz2, calendar, ctype, curl, dom, mbstring, fileinfo, ftp, gd, gettext, iconv, igbinary, imagick, intl, json, ldap, exif, mysqli, pdo_mysql, pdo_pgsql, pdo_sqlite, pgsql, apc, posix, readline, redis, shmop, SimpleXML, sockets, sqlite3, sysvmsg, sysvsem, sysvshm, tokenizer, wddx, xmlreader, xmlwriter, xsl, zip, Phar, blackfire, Zend OPcache, xdebug"/><opcache extension_loaded="1" enabled=""/>
    </env>
  </suite>
</phpbench>
EOT
        );
        $decoder = new XmlDecoder();
        $collection = $decoder->decode($dom);
        $suite = $collection->getSuites()[0];
        assert($suite instanceof Suite);
        $envs = $suite->getEnvInformations();
        self::assertCount(3, $envs);
        $env = reset($envs);
        self::assertInstanceOf(Information::class, $env);
        assert($env instanceof Information);
        self::assertCount(5, $env);
    }

    public function doTestBinary(SuiteCollection $collection): void
    {
        $dom = $this->encode($collection);

        $decoder = new XmlDecoder();
        $collection = $decoder->decode($dom);

        $decodedDom = $this->encode($collection);

        $this->assertEquals(
            $dom->dump(),
            $decodedDom->dump()
        );
    }

    public function doTestDate(SuiteCollection $collection): void
    {
        $dom = $this->encode($collection);

        $decoder = new XmlDecoder();
        $collection = $decoder->decode($dom);

        $decodedDom = $this->encode($collection);

        $this->assertEquals(
            $dom->dump(),
            $decodedDom->dump()
        );
    }

    public function testParameters(): void
    {
        $collection = new SuiteCollection([
            SuiteBuilder::create('one')->withDateString('2021-01-01')->benchmark('bench')->subject('subject')->variant()->withParameterSet('one', [
                'int' => 1,
                'float' => 1.123,
                'string' => 'string',
            ])->end()->end()->end()->build()
        ]);
        $dom = $this->encode($collection);
        $decoder = new XmlDecoder();
        $suites = $decoder->decode($dom);

        self::assertEquals(
            ParameterSet::fromUnserializedValues('one', [
                'int' => 1,
                'float' => 1.123,
                'string' => 'string',
            ]),
            $suites->first()
                ->getBenchmark('bench')
                ->getSubject('subject')
                ->getVariantByParameterSetName('one')
                ->getParameterSet()
        );
    }

    private function encode(SuiteCollection $collection)
    {
        $xmlEncoder = new XmlEncoder();
        $dom = $xmlEncoder->encode($collection);

        return $dom;
    }
}
