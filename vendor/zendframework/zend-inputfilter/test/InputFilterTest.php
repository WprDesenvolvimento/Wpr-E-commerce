<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\InputFilter;

use ArrayIterator;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Zend\InputFilter\Factory;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

/**
 * @covers Zend\InputFilter\InputFilter
 */
class InputFilterTest extends BaseInputFilterTest
{
    /**
     * @var InputFilter
     */
    protected $inputFilter;

    public function setUp()
    {
        $this->inputFilter = new InputFilter();
    }

    public function testLazilyComposesAFactoryByDefault()
    {
        $factory = $this->inputFilter->getFactory();
        $this->assertInstanceOf(Factory::class, $factory);
    }

    public function testCanComposeAFactory()
    {
        $factory = $this->createFactoryMock();
        $this->inputFilter->setFactory($factory);
        $this->assertSame($factory, $this->inputFilter->getFactory());
    }

    /**
     * @covers \Zend\InputFilter\BaseInputFilter::getValue
     *
     * @group 6028
     */
    public function testGetValueReturnsArrayIfNestedInputFilters()
    {
        $inputFilter = new InputFilter();
        $inputFilter->add(new Input(), 'name');

        $this->inputFilter->add($inputFilter, 'people');

        $data = [
            'people' => [
                 'name' => 'Wanderson'
            ]
        ];

        $this->inputFilter->setData($data);
        $this->assertTrue(
            $this->inputFilter->isValid(),
            'isValid() value not match. Detail . ' . json_encode($this->inputFilter->getMessages())
        );

        $this->assertInternalType('array', $this->inputFilter->getValue('people'));
    }

    public function inputProvider()
    {
        $dataSets = parent::inputProvider();

        $inputSpecificationAsArray = [
            'name' => 'inputFoo',
        ];
        $inputSpecificationAsTraversable = new ArrayIterator($inputSpecificationAsArray);

        $inputSpecificationResult = new Input('inputFoo');
        $inputSpecificationResult->getFilterChain(); // Fill input with a default chain just for make the test pass
        $inputSpecificationResult->getValidatorChain(); // Fill input with a default chain just for make the test pass

        // @codingStandardsIgnoreStart
        $inputFilterDataSets = [
            // Description => [input, expected name, $expectedReturnInput]
            'array' =>       [$inputSpecificationAsArray      , 'inputFoo', $inputSpecificationResult],
            'Traversable' => [$inputSpecificationAsTraversable, 'inputFoo', $inputSpecificationResult],
        ];
        // @codingStandardsIgnoreEnd
        $dataSets = array_merge($dataSets, $inputFilterDataSets);

        return $dataSets;
    }

    /**
     * @return Factory|MockObject
     */
    protected function createFactoryMock()
    {
        /** @var Factory|MockObject $factory */
        $factory = $this->getMock(Factory::class);

        return $factory;
    }
}
