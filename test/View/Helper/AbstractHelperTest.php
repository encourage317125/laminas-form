<?php

declare(strict_types=1);

namespace LaminasTest\Form\View\Helper;

use Laminas\Escaper\Escaper;
use Laminas\Form\Exception\InvalidArgumentException;
use Laminas\Form\View\Helper\AbstractHelper;
use Laminas\I18n\Translator\Translator;

/**
 * Tests for {@see \Laminas\Form\View\Helper\AbstractHelper}
 *
 * @covers \Laminas\Form\View\Helper\AbstractHelper
 */
final class AbstractHelperTest extends AbstractCommonTestCase
{
    protected function setUp(): void
    {
        $this->helper = $this->getMockForAbstractClass(AbstractHelper::class);
        parent::setUp();
    }

    /**
     * @group issue-5991
     */
    public function testWillEscapeValueAttributeValuesCorrectly(): void
    {
        self::assertSame(
            'data-value="breaking&#x20;your&#x20;HTML&#x20;like&#x20;a&#x20;boss&#x21;&#x20;&#x5C;"',
            $this->helper->createAttributesString(['data-value' => 'breaking your HTML like a boss! \\'])
        );
    }

    public function testWillEncodeValueAttributeValuesCorrectly(): void
    {
        $escaper = new Escaper('iso-8859-1');

        $this->helper->setEncoding('iso-8859-1');

        self::assertSame(
            'data-value="' . $escaper->escapeHtmlAttr('Título') . '"',
            $this->helper->createAttributesString(['data-value' => 'Título'])
        );
    }

    public function testWillNotEncodeValueAttributeValuesCorrectly(): void
    {
        $escaper = new Escaper('iso-8859-1');

        self::assertNotSame(
            'data-value="' . $escaper->escapeHtmlAttr('Título') . '"',
            $this->helper->createAttributesString(['data-value' => 'Título'])
        );
    }

    public static function addAttributesData(): array
    {
        return [
            'valid'                => ['valid', 'valid="value"'],
            'valid-prefix'         => ['px-custom', 'px-custom="value"'],
            'xml-ns'               => ['xlink:custom', 'xlink:custom="value"'],
            'invalid-slash'        => ['attr/', null, true],
            'invalid-double-quote' => ['attr"', null, true],
            'invalid-quote'        => ['attr\'', null, true],
            'invalid-gt'           => ['attr>', null, true],
            'invalid-equals'       => ['attr=value', null, true],
            'invalid-space'        => ['at tr', null, true],
            'invalid-newline'      => ["at\ntr", null, true],
            'invalid-tab'          => ["at\ttr", null, true],
            'invalid-formfeed'     => ["at\ftr", null, true],
        ];
    }

    /**
     * @dataProvider addAttributesData
     */
    public function testWillIncludeAdditionalAttributes(
        string $attribute,
        ?string $expected = null,
        ?bool $exception = null
    ): void {
        if ($exception) {
            $this->expectException(InvalidArgumentException::class);
        }

        $this->helper->addValidAttribute($attribute);

        self::assertSame(
            $expected,
            $this->helper->createAttributesString([$attribute => 'value'])
        );
    }

    public static function addAttributesPrefixData(): array
    {
        return [
            'valid'                => ['v-', 'v-attr="value"'],
            'valid-dash'           => ['custom-', 'custom-attr="value"'],
            'xml-ns'               => ['xlink:', 'xlink:attr="value"'],
            'valid-nodash'         => ['abc', 'abcattr="value"'],
            'invalid-slash'        => ['custom/', null, true],
            'invalid-double-quote' => ['custom"', null, true],
            'invalid-quote'        => ['custom\'', null, true],
            'invalid-gt'           => ['custom>', null, true],
            'invalid-equals'       => ['custom=', null, true],
            'invalid-space'        => ['custom ', null, true],
            'invalid-newline'      => ["cus\ntom", null, true],
            'invalid-tab'          => ["cus\ttom", null, true],
            'invalid-formfeed'     => ["cus\ftom", null, true],
        ];
    }

    /**
     * @dataProvider addAttributesPrefixData
     */
    public function testWillIncludeAdditionalAttributesByPrefix(
        string $prefix,
        ?string $expected = null,
        ?bool $exception = null
    ): void {
        if ($exception) {
            $this->expectException(InvalidArgumentException::class);
        }

        $this->helper->addValidAttributePrefix($prefix);

        self::assertSame(
            $expected,
            $this->helper->createAttributesString([$prefix . 'attr' => 'value'])
        );
    }

    public function testWillTranslateAttributeValuesCorrectly(): void
    {
        $translator = self::getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['translate'])
            ->getMock();

        $translator
            ->expects(self::exactly(2))
            ->method('translate')
            ->with(
                self::equalTo('Welcome'),
                self::equalTo('view-helper-text-domain')
            )
            ->willReturn('Willkommen');

        $this->helper
            ->addTranslatableAttribute('data-translate-me')
            ->addTranslatableAttributePrefix('data-translatable-')
            ->setTranslatorEnabled(true)
            ->setTranslator(
                $translator,
                'view-helper-text-domain'
            );

        self::assertSame(
            'data-translate-me="Willkommen"',
            $this->helper->createAttributesString(['data-translate-me' => 'Welcome'])
        );

        self::assertSame(
            'data-translatable-welcome="Willkommen"',
            $this->helper->createAttributesString(['data-translatable-welcome' => 'Welcome'])
        );

        self::assertSame(
            'class="Welcome"',
            $this->helper->createAttributesString(['class' => 'Welcome'])
        );
    }

    public function testWillTranslateDefaultAttributeValuesCorrectly(): void
    {
        $translator = self::getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['translate'])
            ->getMock();

        $translator
            ->expects(self::exactly(2))
            ->method('translate')
            ->with(
                self::equalTo('Welcome'),
                self::equalTo('view-helper-text-domain')
            )
            ->willReturn('Willkommen');

        AbstractHelper::addDefaultTranslatableAttribute('data-translate-me');
        AbstractHelper::addDefaultTranslatableAttributePrefix('data-translatable-');

        $this->helper
            ->setTranslatorEnabled(true)
            ->setTranslator(
                $translator,
                'view-helper-text-domain'
            );

        self::assertSame(
            'data-translate-me="Willkommen"',
            $this->helper->createAttributesString(['data-translate-me' => 'Welcome'])
        );

        self::assertSame(
            'data-translatable-welcome="Willkommen"',
            $this->helper->createAttributesString(['data-translatable-welcome' => 'Welcome'])
        );

        self::assertSame(
            'class="Welcome"',
            $this->helper->createAttributesString(['class' => 'Welcome'])
        );
    }

    public function testWillInsulateAgainstBadAttributes(): void
    {
        self::assertSame(
            'data-value=""',
            $this->helper->createAttributesString(['data-value' => "\xc3\x28"])
        );
    }

    public function testNullValueForBooleanAttributeDisablesIt(): void
    {
        $this->helper->addValidAttribute('disabled');

        self::assertSame(
            'disabled="disabled"',
            $this->helper->createAttributesString(['disabled' => 'disabled'])
        );
        self::assertSame(
            '',
            $this->helper->createAttributesString(['disabled' => null])
        );
    }
}
