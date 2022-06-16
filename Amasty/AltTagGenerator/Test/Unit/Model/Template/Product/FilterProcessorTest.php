<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Test\Unit\Model\Template\Product;

use Amasty\AltTagGenerator\Model\Template\Product\Filter\AttributeResolver;
use Amasty\AltTagGenerator\Model\Template\Product\FilterProcessor;
use Amasty\AltTagGenerator\Test\Unit\Traits\ReflectionTrait;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class FilterProcessorTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @var FilterProcessor
     */
    private $model;

    protected function setup(): void
    {
        $defaultAttributeResolver = $this->createMock(AttributeResolver::class);
        $defaultAttributeResolver->expects($this->any())->method('execute')->willReturnCallback(
            function (Product $product, string $attributeCode) {
                return $product->getData($attributeCode);
            }
        );
        $this->model = new FilterProcessor($defaultAttributeResolver);
    }

    /**
     * @covers FilterProcessor::handleVariables
     *
     * @dataProvider handleVariablesDataProvider
     *
     * @param string $template
     * @param array $attributeValuesMap
     * @param string $expectedResult
     * @return void
     *
     * @throws ReflectionException
     */
    public function testHandleVariables(string $template, array $attributeValuesMap, string $expectedResult): void
    {
        $product = $this->createMock(Product::class);
        $product->expects($this->any())->method('getData')->willReturnMap($attributeValuesMap);
        $actualResult = $this->invokeMethod($this->model, 'handleVariables', [$template, $product]);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @covers FilterProcessor::handleOptionalParts
     *
     * @dataProvider handleOptionalPartsDataProvider
     *
     * @param string $template
     * @param string $expectedResult
     * @return void
     *
     * @throws ReflectionException
     */
    public function testHandleOptionalParts(string $template, string $expectedResult): void
    {
        $actualResult = $this->invokeMethod($this->model, 'handleOptionalParts', [$template]);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @covers FilterProcessor::handleNonProcessedVariables
     *
     * @dataProvider handleNonProcessedVariablesDataProvider
     *
     * @param string $template
     * @param string $expectedResult
     * @return void
     *
     * @throws ReflectionException
     */
    public function testHandleNonProcessedVariables(string $template, string $expectedResult): void
    {
        $actualResult = $this->invokeMethod($this->model, 'handleNonProcessedVariables', [$template]);
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function handleVariablesDataProvider(): array
    {
        return [
            [
                'no variables',
                [],
                'no variables'
            ],
            [
                'variable {attribute} not exist',
                [],
                'variable {attribute} not exist'
            ],
            [
                'variable value is {attribute}',
                [
                    ['attribute', null, '111']
                ],
                'variable value is 111'
            ],
            [
                'variable value is {attribute_a|attribute_b}',
                [
                    ['attribute_b', null, '111']
                ],
                'variable value is 111'
            ],
            [
                'variable value is {attribute_a|aTTriBute_b|attribute_c}',
                [
                    ['attribute_b', null, '111']
                ],
                'variable value is 111'
            ],
            [
                'variable value is {attribute_b} and {attribute_b} and {attribute_c|attribute_d}',
                [
                    ['attribute_b', null, '111']
                ],
                'variable value is 111 and 111 and {attribute_c|attribute_d}'
            ],
            [
                'variable value is {attribute_a|attribute_b} and {attribute_c|attribute_d|attribute_b}',
                [
                    ['attribute_b', null, '111'],
                    ['attribute_d', null, '222']
                ],
                'variable value is 111 and 222'
            ]
        ];
    }

    public function handleOptionalPartsDataProvider(): array
    {
        return [
            [
                '[no variables]',
                'no variables'
            ],
            [
                'unresolved variables [by {attribute}]',
                'unresolved variables'
            ],
            [
                'inner unresolved variables [by ABC [by {attribute}]]',
                'inner unresolved variables by ABC'
            ],
            [
                'combine {attribute_a} [by ABC [by {attribute_b} [by ABC]]]',
                'combine {attribute_a} by ABC'
            ],
            [
                'combine {attribute_a} [by ABC [[by {attribute_b}] [by ABC]]]',
                'combine {attribute_a} by ABC by ABC'
            ]
        ];
    }

    public function handleNonProcessedVariablesDataProvider(): array
    {
        return [
            [
                '{attribute_a} unresolved variables {attribute_b}',
                'unresolved variables'
            ],
            [
                '{attribute_a|attribute_b} unresolved {attribute_b} variables {attribute_c}',
                'unresolved variables'
            ]
        ];
    }
}
