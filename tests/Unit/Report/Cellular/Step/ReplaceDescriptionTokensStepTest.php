<?php

namespace PhpBench\Tests\Unit\Report\Cellular\Step;

use PhpBench\Report\Cellular\Step\ReplaceDescriptionTokensStep;
use DTL\Cellular\Workspace;

class ReplaceDescriptionTokensStepTest extends \PHPUnit_Framework_TestCase
{
    /**
     * It should replace the description tokens with the subject parameters
     */
    public function testReplaceTokens()
    {
        $workspace = Workspace::create();
        $table = $workspace->createAndAddTable();
        $table->setAttribute('description', 'Hello {name}, look! there is a {animal}');
        $table->setAttribute('parameters', array('name' => 'Daniel', 'animal' => 'Elephant'));

        $table = $workspace->createAndAddTable();
        $table->setAttribute('description', 'Hello {name}, look! there is a {animal}');
        $table->setAttribute('parameters', array('name' => 'Amy', 'animal' => 'Rabbit'));

        $step = new ReplaceDescriptionTokensStep();
        $step->step($workspace);

        $this->assertEquals(
            'Hello "Daniel", look! there is a "Elephant"',
            $workspace->getTable(0)->getAttribute('description')
        );

        $this->assertEquals(
            'Hello "Amy", look! there is a "Rabbit"',
            $workspace->getTable(1)->getAttribute('description')
        );
    }

    /**
     * It should leave any non-matching tokens in-place
     */
    public function testReplaceTokensNonMatching()
    {
        $workspace = Workspace::create();
        $table = $workspace->createAndAddTable();
        $table->setAttribute('description', 'Hello {name}, look! there is a {animal}');
        $table->setAttribute('parameters', array('name' => 'Daniel'));

        $step = new ReplaceDescriptionTokensStep();
        $step->step($workspace);

        $this->assertEquals(
            'Hello "Daniel", look! there is a {animal}',
            $workspace->getTable(0)->getAttribute('description')
        );
    }
}
