<?php

namespace PhpBench\Report\Cellular;

interface Step
{
    /**
     * Apply a transformation step to the workspace.
     *
     * @param Workspace $workspace
     */
    public function step(Workspace $workspace);
}
