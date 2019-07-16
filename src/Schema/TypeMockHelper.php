<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Test\Schema;

use Ixocreate\Schema\Type\Type;
use Ixocreate\Schema\Type\TypeInterface;
use Ixocreate\ServiceManager\Autowire\FactoryResolverInterface;
use Ixocreate\ServiceManager\Exception\ServiceNotFoundException;
use Ixocreate\ServiceManager\ServiceManagerConfigInterface;
use Ixocreate\ServiceManager\ServiceManagerSetupInterface;
use Ixocreate\ServiceManager\SubManager\SubManagerInterface;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class TypeMockHelper
{
    /**
     * @var TestCase
     */
    private $testCase;

    /**
     * @var array
     */
    private $typesToRegister;

    /**
     * @var bool
     */
    private $strictRegisterCheck;

    public function __construct(TestCase $testCase, array $typesToRegister = [], bool $strictRegisterCheck = false)
    {
        $this->testCase = $testCase;
        $this->typesToRegister = $typesToRegister;
        $this->strictRegisterCheck = $strictRegisterCheck;
    }

    public function create(): void
    {
        $reflection = new ReflectionClass(Type::class);
        $type = $reflection->newInstanceWithoutConstructor();

        $container = $this->createSubManager();

        $reflection = new \ReflectionProperty($type, 'subManager');
        $reflection->setAccessible(true);
        $reflection->setValue($type, $container);

        $reflection = new \ReflectionProperty(Type::class, 'type');
        $reflection->setAccessible(true);
        $reflection->setValue($type);
    }

    private function createSubManager(): SubManagerInterface
    {
        return new class ($this->testCase, $this->typesToRegister, $this->strictRegisterCheck) implements SubManagerInterface
        {
            /**
             * @var TestCase
             */
            private $testCase;

            /**
             * @var array
             */
            private $typesToRegister;

            /**
             * @var bool
             */
            private $strictRegisterCheck;

            public function __construct(TestCase $testCase, array $typesToRegister = [], bool $strictRegisterCheck = false)
            {
                $this->testCase = $testCase;
                $this->typesToRegister = $typesToRegister;
                $this->strictRegisterCheck = $strictRegisterCheck;
            }

            public function has($id)
            {
                if (\array_key_exists($id, $this->typesToRegister)) {
                    return true;
                }

                if ($this->strictRegisterCheck === true) {
                    return false;
                }

                return true;
            }

            public function get($id)
            {
                if (\array_key_exists($id, $this->typesToRegister)) {
                    return $this->typesToRegister[$id];
                }

                if ($this->strictRegisterCheck === true) {
                    throw new ServiceNotFoundException('Type not found');
                }

                return (new MockBuilder($this->testCase, TypeInterface::class))
                    ->disableOriginalConstructor()
                    ->disableOriginalClone()
                    ->disableArgumentCloning()
                    ->disallowMockingUnknownTypes()
                    ->getMock();
            }

            public function getValidation(): string
            {
                return TypeInterface::class;
            }

            /**
             * @param string $id
             * @param array|null $options
             * @return mixed
             */
            public function build(string $id, array $options = null)
            {
                return $this->get($id);
            }

            /**
             * @return ServiceManagerConfigInterface
             */
            public function getServiceManagerConfig(): ServiceManagerConfigInterface
            {
                return (new MockBuilder($this->testCase, ServiceManagerConfigInterface::class))
                    ->disableOriginalConstructor()
                    ->disableOriginalClone()
                    ->disableArgumentCloning()
                    ->disallowMockingUnknownTypes()
                    ->getMock();
            }

            /**
             * @return ServiceManagerSetupInterface
             */
            public function getServiceManagerSetup(): ServiceManagerSetupInterface
            {
                return (new MockBuilder($this->testCase, ServiceManagerSetupInterface::class))
                    ->disableOriginalConstructor()
                    ->disableOriginalClone()
                    ->disableArgumentCloning()
                    ->disallowMockingUnknownTypes()
                    ->getMock();
            }

            /**
             * @return FactoryResolverInterface
             */
            public function getFactoryResolver(): FactoryResolverInterface
            {
                return (new MockBuilder($this->testCase, FactoryResolverInterface::class))
                    ->disableOriginalConstructor()
                    ->disableOriginalClone()
                    ->disableArgumentCloning()
                    ->disallowMockingUnknownTypes()
                    ->getMock();
            }

            /**
             * @return array
             */
            public function getServices(): array
            {
                return [];
            }

            /**
             * @return array
             */
            public function initialServices(): array
            {
                return [];
            }
        };
    }
}
