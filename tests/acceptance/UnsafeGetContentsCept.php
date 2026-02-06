<?php

$I = new \Weirdan\PsalmPluginSkeleton\Tests\AcceptanceTester($scenario);
$I->wantTo('Check that UnsafeGetContents plugin triggers on getContents()');

$I->haveTheFollowingConfig(<<<XML
<psalm>
  <projectFiles>
    <directory name="." />
  </projectFiles>

  <stubs>
    <file name="../_data/stubs/psr-http-message.php" />
  </stubs>

  <plugins>
    <pluginClass class="Moxio\\PsalmPlugin\\Plugin" />
  </plugins>

  <issueHandlers>
    <MethodSignatureMismatch errorLevel="suppress"/>
    <UnimplementedInterfaceMethod errorLevel="suppress"/>
    <MissingOverrideAttribute errorLevel="suppress"/>
  </issueHandlers>
</psalm>
XML);

$I->haveTheFollowingCode(
    file_get_contents('tests/_data/UnsafeGetContents.php')
);

$I->runPsalm();

$I->seeThisError(
    'UnsafeGetContents',
    'Calling getContents() on a PSR-7 StreamInterface is unsafe.'
);
