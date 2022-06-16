<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Test\Unit\Model\Template\Product;

use Amasty\AltTagGenerator\Model\Source\ReplacementLogic;
use Amasty\AltTagGenerator\Model\Template;
use Amasty\AltTagGenerator\Model\Template\Product\GetAltTag;
use Amasty\AltTagGenerator\Model\Template\Product\GetAppliedTemplate;
use Amasty\AltTagGenerator\Model\Template\Product\ModifyAltTag;
use Amasty\AltTagGenerator\Model\Template\Query\GetByIdInterface;
use Amasty\AltTagGenerator\Test\Unit\Traits\ReflectionTrait;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class ModifyAltTagTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @var ModifyAltTag
     */
    private $model;

    protected function setup(): void
    {
        $getAppliedTemplate = $this->createMock(GetAppliedTemplate::class);
        $getById = $this->createMock(GetByIdInterface::class);
        $getAltTag = $this->createMock(GetAltTag::class);
        $this->model = new ModifyAltTag($getAppliedTemplate, $getById, $getAltTag);
    }

    /**
     * @covers ModifyAltTag::execute
     *
     * @dataProvider executeDataProvider
     *
     * @param int|null $appliedTemplateId
     * @param int $replacementLogic
     * @param string $altTag
     * @param int $productId
     * @param string $oldTag
     * @param string $expectedResult
     * @return void
     *
     * @throws ReflectionException
     */
    public function testExecute(
        ?int $appliedTemplateId,
        int $replacementLogic,
        string $altTag,
        int $productId,
        string $oldTag,
        string $expectedResult
    ): void {
        $getAppliedTemplate = $this->getProperty($this->model, 'getAppliedTemplate');
        $getAppliedTemplate->expects($this->any())->method('execute')->willReturn($appliedTemplateId);

        $template = $this->createMock(Template::class);
        $template->expects($this->any())->method('getReplacementLogic')->willReturn($replacementLogic);
        $getById = $this->getProperty($this->model, 'getById');
        $getById->expects($this->any())->method('execute')->willReturn($template);

        $getAltTag = $this->getProperty($this->model, 'getAltTag');
        $getAltTag->expects($this->any())->method('execute')->willReturn($altTag);

        $product = $this->createMock(Product::class);
        $product->expects($this->any())->method('getId')->willReturn($productId);

        $this->model->execute($product, $oldTag, true); // double call for check cache
        $actualResult = $this->model->execute($product, $oldTag, true);
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function executeDataProvider(): array
    {
        return [
            [
                null,
                ReplacementLogic::REPLACE,
                '',
                1,
                'old tag',
                'old tag'
            ],
            [
                2,
                ReplacementLogic::REPLACE,
                '',
                1,
                'old tag',
                ''
            ],
            [
                2,
                ReplacementLogic::REPLACE,
                'new tag',
                1,
                'old tag',
                'new tag'
            ],
            [
                2,
                ReplacementLogic::REPLACE_EMPTY,
                'new tag',
                1,
                'old tag',
                'old tag'
            ],
            [
                2,
                ReplacementLogic::APPEND,
                'new tag',
                1,
                'old tag',
                'old tag new tag'
            ]
        ];
    }
}
