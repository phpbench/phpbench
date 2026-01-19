<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__.'/../config.php');

    $rectorConfig->ruleWithConfiguration(
        AnnotationToAttributeRector::class,
        array_reduce(
            glob(__DIR__.'/../../../../lib/Attributes/*.php'),
            static function (array $annotationToAttributes, string $file): array {
                $filename = pathinfo($file, PATHINFO_FILENAME);

                if ($filename === 'AbstractMethodsAttribute') {
                    return $annotationToAttributes;

                }

                $annotationToAttributes[] = new AnnotationToAttribute($filename, "PhpBench\\Attributes\\$filename");

                return $annotationToAttributes;
            },
            []
        )
    );
};
