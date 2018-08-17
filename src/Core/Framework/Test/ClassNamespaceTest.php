<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Test;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Product\Aggregate\ProductCategoryTree\ProductCategoryTreeDefinition;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class ClassNamespaceTest extends TestCase
{
    public function test_all_production_files_are_namespaced_correctly(): void
    {
        $basePath = __DIR__ . '/../../../';
        $basePathParts = explode('/', $basePath);

        $phpFiles = (new Finder())->files()->in($basePath)->name('*.php')->getIterator();

        $errors = [];
        foreach ($phpFiles as $file) {
            if ($this->hasNamespaceDefined($file) === false) {
                continue;
            }

            $parts = $this->extractProductionNamespaceParts($file, $basePathParts);

            $namespace = rtrim('namespace Shopware\\' . implode('\\', $parts), '\\');

            if (strpos($file->getContents(), $namespace) === false) {
                $relativePath = str_replace($basePath, '', $file->getPathname());
                $errors['src/' . $relativePath] = $namespace;
            }
        }

        $errorMessage = 'Expected the following files to have a correct namespace:' . PHP_EOL . PHP_EOL . print_r($errors, true);

        self::assertCount(0, $errors, $errorMessage);
    }

    public function testReflectionClassConstantComparison(): void
    {
        $reflection = new \ReflectionClass(\Shopware\Core\Content\Category\CategoryDefinition::class);
        $this->assertNotEquals(\Shopware\Core\Framework\ORM\MappingEntityDefinition::class, $reflection->getParentClass());

        $reflection = new \ReflectionClass(ProductCategoryTreeDefinition::class);
        $this->assertEquals(\Shopware\Core\Framework\ORM\MappingEntityDefinition::class, $reflection->getParentClass()->getName());
    }

    /**
     * @param SplFileInfo $file
     * @param string[]    $basePathParts
     *
     * @return string[]
     */
    private function extractProductionNamespaceParts(SplFileInfo $file, array $basePathParts): array
    {
        $parts = explode('/', (string) $file);
        $parts = \array_slice($parts, \count($basePathParts) - 1);
        $parts = array_filter($parts);

        array_pop($parts);

        return $parts;
    }

    private function hasNamespaceDefined(SplFileInfo $file): bool
    {
        $lines = explode("\n", $file->getContents());

        foreach ($lines as $line) {
            if (preg_match('#^namespace\sShopware\\\.*;$#m', $line)) {
                return true;
            }
        }

        return false;
    }
}
