<?php

use Behat\MinkExtension\Context\RawMinkContext;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends RawMinkContext
{
    /**
     * @Given AAA
     */
    public function aaa()
    {
        $this->getSession()->setCookie('abc', 'def');

        $this->getSession()->visit('/item');

        echo $this->getSession()->getPage()->getContent();
    }

    /**
     * @When BBB
     */
    public function bbb()
    {
    }

    /**
     * @Then CCC
     */
    public function ccc()
    {
    }
}
