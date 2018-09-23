<?php
$commandLine = $this->commandLine();
$commandLine->option('src', 'default', 'src');
$commandLine->option('spec', 'default', 'spec');
$commandLine->option('ff', 'default', 1);
$commandLine->option('reporter', 'default', 'verbose');
// $commandLine->option('coverage', 'default', 2);
// $commandLine->option('clover', 'default', 'test_output/clover_kahlan.xml');
